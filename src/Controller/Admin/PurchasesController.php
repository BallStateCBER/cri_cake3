<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class PurchasesController extends AppController
{
    public $paginate = [
        'conditions' => ['Communities.dummy' => false],
        'contain' => [
            'Communities' => [
                'fields' => ['id', 'name']
            ],
            'Products' => [
                'fields' => ['id', 'description', 'price']
            ],
            'Refunders' => [
                'fields' => ['id', 'name']
            ],
            'Users' => [
                'fields' => ['id', 'name', 'email', 'phone', 'title', 'organization']
            ]
        ],
        'fields' => [
            'admin_added',
            'community_id',
            'created',
            'id',
            'notes',
            'product_id',
            'refunded',
            'refunder_id',
            'source',
            'user_id'
        ],
        'limit' => 50,
        'order' => [
            'Purchases.created' => 'DESC'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set([
            'titleForLayout' => 'Payment Records',
            'purchases' => $this->paginate()->toArray(),
            'sources' => $this->Purchases->getSourceOptions()
        ]);
    }

    /**
     * View method
     *
     * @param int $communityId Community ID
     * @return void
     */
    public function view($communityId)
    {
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);
        $this->paginate['conditions']['community_id'] = $communityId;
        $this->set([
            'communityId' => $communityId,
            'purchases' => $this
                ->paginate()
                ->toArray(),
            'sources' => $this->Purchases->getSourceOptions(),
            'titleForLayout' => $community->name . ' Payment Records'
        ]);
    }

    /**
     * Refund method
     *
     * @param int $purchaseId Purchase record ID
     * @return \Cake\Http\Response|null
     */
    public function refund($purchaseId)
    {
        try {
            $purchase = $this->Purchases->get($purchaseId);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error("Payment record #$purchaseId not found.");

            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            // Bounce user back if the purchase was already refunded
            if ($purchase->refunded) {
                $timestamp = strtotime($purchase->refunded);
                $date = date('F j, Y', $timestamp);
                $usersTable = TableRegistry::get('Users');
                try {
                    $user = $usersTable->get($purchase->refunder_id);
                    $admin = $user->name;
                } catch (RecordNotFoundException $e) {
                    $admin = "(unknown user #$purchase->refunder_id)";
                }
                $this->Flash->error("That purchase record was already marked refunded on $date by $admin.");
            } else {
                // Record refund
                $purchase->refunded = date('Y-m-d H:i:s');
                $purchase->refunder_id = $this->Auth->user('id');
                if ($this->Purchases->save($purchase)) {
                    $this->Flash->success('Refund recorded.');

                    // Dispatch event
                    $productId = $purchase->product_id;
                    $productsTable = TableRegistry::get('Products');
                    $product = $productsTable->get($productId);
                    $event = new Event('Model.Purchase.afterRefund', $this, ['meta' => [
                        'communityId' => $purchase->community_id,
                        'productName' => $product->description
                    ]]);
                    $this->eventManager()->dispatch($event);
                } else {
                    $this->Flash->error('There was an error saving that refund record.');
                }
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null
     */
    public function add()
    {
        $purchase = $this->Purchases->newEntity();
        $productsTable = TableRegistry::get('Products');
        if ($this->request->is('post')) {
            $this->request->data['admin_added'] = true;
            $this->request->data['user_id'] = $this->Auth->user('id');
            $this->request->data['postback'] = '';
            $purchase = $this->Purchases->patchEntity($purchase, $this->request->getData());
            $errors = $purchase->errors();
            if (empty($errors) && $this->Purchases->save($purchase)) {
                $this->Flash->success('Purchase record added');

                // Dispatch event
                $productId = $purchase->product_id;
                $product = $productsTable->get($productId);
                $event = new Event('Model.Purchase.afterAdminAdd', $this, ['meta' => [
                    'communityId' => $purchase->community_id,
                    'productName' => $product->description
                ]]);
                $this->eventManager()->dispatch($event);

                return $this->redirect([
                    'action' => 'index'
                ]);
            }
            $this->Flash->error('There was an error adding a new purchase record');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $results = $productsTable->find('all')
            ->select(['id', 'description', 'price'])
            ->order(['id' => 'ASC']);
        $products = [];
        foreach ($results as $product) {
            $products[$product->id] = $product->description . ' ($' . number_format($product->price) . ')';
        }
        $this->set([
            'communities' => $communitiesTable->find('list')->order(['name' => 'ASC']),
            'products' => $products,
            'purchase' => $purchase,
            'titleForLayout' => 'Add a New Payment Record',
            'sources' => $this->Purchases->getSourceOptions()
        ]);
    }

    /**
     * Page for OCRA-funded purchases
     *
     * @return void
     */
    public function ocra()
    {
        $query = $this->Purchases->find('ocra');
        $purchases = [
            'not yet billable' => $query->find('notBillable')->toArray(),
            'billable' => $query->find('billable')->toArray(),
            'billed' => [],
            'paid' => []
        ];
        $totals = [];
        foreach ($purchases as $label => $group) {
            $costs = Hash::extract($group, '{n}.product.price');
            $totals[$label] = array_sum($costs);
        }
        $this->set([
            'purchases' => $purchases,
            'titleForLayout' => 'OCRA Funding',
            'totals' => $totals
        ]);
    }
}
