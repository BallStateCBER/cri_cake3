<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * ActivityRecords Controller
 *
 * @property \App\Model\Table\ActivityRecordsTable $ActivityRecords
 */
class ActivityRecordsController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->viewBuilder()->helpers(['ActivityRecords']);
        $this->paginate = [
            'contain' => ['Users', 'Communities', 'Surveys'],
            'order' => ['created' => 'DESC']
        ];
        $activityRecords = $this->paginate($this->ActivityRecords);
        $trackedEvents = [
            'Community added',
            'User account added'
        ];
        $this->set([
            'activityRecords' => $activityRecords,
            'titleForLayout' => 'Activity Log',
            'trackedEvents' => $trackedEvents
        ]);
        $this->set('_serialize', ['activityRecords']);
    }
}
