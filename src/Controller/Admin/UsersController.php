<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;

/**
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $cookieParentKey = 'AdminUsersIndex';
        $cookieKey = "$cookieParentKey.filter";

        // Remember selected filters
        $filter = $this->request->getQuery('filter');
        if ($filter) {
            $this->Cookie->write($cookieKey, $filter);

        // Use remembered filter when no filter is manually specified
        } elseif ($this->Cookie->check($cookieKey)) {
            $filter = $this->Cookie->read($cookieKey);
        }

        // Apply filters
        switch ($filter) {
            case 'client':
            case 'consultant':
            case 'admin':
                $this->paginate['conditions']['Users.role'] = $filter;
                break;
            default:
                $filter = 'all';
                break;
        }

        $this->paginate['order']['Users.name'] = 'ASC';
        $this->set([
            'titleForLayout' => 'Users',
            'users' => $this->paginate(),
            'buttons' => [
                'all' => 'All Users',
                'client' => 'Clients',
                'consultant' => 'Consultants',
                'admin' => 'Admins',
            ],
            'currentFilter' => $filter,
        ]);
    }

    /**
     * Sets variables in the view for the user add/edit form
     *
     * @param \App\Model\Entity\User $user User
     * @return void
     */
    private function prepareForm($user)
    {
        $communities = $this->Users->ConsultantCommunities
            ->find('list')
            ->order('name')
            ->toArray();
        $selectedCommunities = [];
        if (! empty($user->consultant_communities)) {
            foreach ($user->consultant_communities as $community) {
                $selectedCommunities[] = [
                    'id' => $community->id,
                    'name' => $communities[$community->id],
                ];
            }
        }
        $noCommunity = [0 => '(No community)'];
        $communities = $noCommunity + $communities;
        $errors = $user->getErrors();
        $hasPasswordError = strpos(implode('', array_keys($errors)), 'password') !== false;

        $this->set([
            'communities' => $communities,
            'hasPasswordError' => $hasPasswordError,
            'roles' => [
                'admin' => 'Admin',
                'client' => 'Client',
                'consultant' => 'Consultant',
            ],
            'salutations' => $this->Users->getSalutations(),
            'selectedCommunities' => $selectedCommunities,
            'user' => $user,
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response
     */
    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->getData();
            $data['password'] = $data['new_password'];
            $clientCommunityId = $this->request->getData('client_communities.0.id');
            if (empty($clientCommunityId)) {
                $data['client_communities'] = [];
            }

            // Ignore ClientCommunity if user is not a client
            if ($this->request->getData('role') != 'client' && isset($data['client_communities'])) {
                unset($data['client_communities']);
            }

            $user = $this->Users->patchEntity($user, $data);

            $errors = $user->getErrors();
            if (empty($errors) && $this->Users->save($user)) {
                // Dispatch event
                $event = new Event('Model.User.afterAdd', $this, ['meta' => [
                    'newUserId' => $user->id,
                    'userName' => $user->name,
                    'userRole' => $user->role,
                    'communityId' => $clientCommunityId ? $clientCommunityId : null,
                ]]);
                $this->getEventManager()->dispatch($event);

                try {
                    /** @var \Queue\Model\Table\QueuedJobsTable $queuedJobs */
                    $queuedJobs = TableRegistry::getTableLocator()->get('Queue.QueuedJobs');
                    $queuedJobs->createJob(
                        'NewAccountEmail',
                        [
                            'user' => [
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $user->role,
                            ],
                            'unhashedPassword' => $this->request->getData('new_password'),
                        ],
                        ['reference' => $user->email]
                    );

                    $this->Flash->success('User account created and login credentials emailed');

                    return $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index',
                    ]);
                } catch (\Exception $e) {
                    $this->Users->delete($user);

                    $msg =
                        'There was an error emailing this user with their login info. No new account was created. ' .
                        'Please try again or contact an administrator for assistance.';
                    $this->Flash->error($msg);
                }
            } else {
                $msg = 'There was an error creating this user\'s account.';
                $msg .= ' Please try again or contact an administrator for assistance.';
                $this->Flash->error($msg);
            }
        } else {
            $user->all_communities = false;
        }

        $this->prepareForm($user);
        $this->set([
            'titleForLayout' => 'Add User',
        ]);
        $this->render('/Admin/Users/form');
    }

    /**
     * Edit method
     *
     * @param int|null $id User ID
     * @return \Cake\Http\Response|null
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, ['contain' => ['ClientCommunities', 'ConsultantCommunities']]);

        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->getData();
            if ($this->request->getData('new_password') != '') {
                $data['password'] = $this->request->getData('new_password');
            }

            if (empty($this->request->getData('client_communities.0.id'))) {
                $data['client_communities'] = [];
            }

            if (empty($this->request->getData('consultant_communities'))) {
                $data['consultant_communities'] = [];
            }

            $user = $this->Users->patchEntity($user, $data);
            $errors = $user->getErrors();
            if (empty($errors)) {
                $roleChanged = $user->isDirty('role');

                if ($this->Users->save($user)) {
                    $msg = 'User info updated';
                    if ($roleChanged) {
                        $msg .= '. The update to this user\'s <strong>role</strong> will take effect';
                        $msg .= ' the next time they manually log in or when their session automatically refreshes.';
                    }
                    $this->Flash->success($msg);

                    return $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index',
                    ]);
                }
            } else {
                $this->Flash->error('Please correct the indicated error(s)');
            }
        }

        $this->prepareForm($user);
        $this->set([
            'titleForLayout' => $user->name,
        ]);
        $this->render('/Admin/Users/form');
    }

    /**
     * Delete method
     *
     * @param int|null $id User ID
     * @return \Cake\Http\Response|null
     */
    public function delete($id = null)
    {
        if (! $this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $user = $this->Users->get($id);

        if ($this->Users->delete($user)) {
            $this->Flash->success('User deleted');

            // Dispatch event
            $event = new Event('Model.User.afterDelete', $this, ['meta' => [
                'userName' => $user->name,
                'userRole' => $user->role,
            ]]);
            $this->getEventManager()->dispatch($event);
        } else {
            $this->Flash->error('User was not deleted');
        }

        return $this->redirect([
            'prefix' => 'admin',
            'action' => 'index',
        ]);
    }

    /**
     * ChooseClient method
     *
     * @return \Cake\Http\Response|null
     */
    public function chooseClient()
    {
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        if ($this->request->is('post')) {
            $communityId = $this->request->getData('community_id');
            $this->Cookie->write('communityId', $communityId);
            $clientId = $communitiesTable->getCommunityClientId($communityId);
            $this->Cookie->write('clientId', $clientId);
            if ($this->request->getData('redirect')) {
                return $this->redirect($this->request->getData('redirect'));
            } elseif ($this->request->is('ajax')) {
                $this->render('/Pages/blank');
                $this->viewBuilder()->setLayout('ajax');
            } else {
                $this->Flash->success('Client selected');

                return $this->redirect([
                    'prefix' => 'client',
                    'controller' => 'Communities',
                    'action' => 'index',
                ]);
            }
        }
        $this->set([
            'communities' => $communitiesTable->getClientCommunityList(),
            'redirect' => urldecode($this->request->getQuery('redirect')),
            'titleForLayout' => 'Choose client',
        ]);
    }
}
