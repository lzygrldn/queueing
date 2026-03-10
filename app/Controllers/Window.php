<?php

namespace App\Controllers;

use App\Models\WindowModel;
use App\Models\QueueModel;
use App\Models\ServiceRecordModel;

class Window extends BaseController
{
    protected $windowModel;
    protected $queueModel;
    protected $serviceRecordModel;

    public function __construct()
    {
        $this->windowModel = new WindowModel();
        $this->queueModel = new QueueModel();
        $this->serviceRecordModel = new ServiceRecordModel();
    }

    public function index($windowNumber = null)
    {
        if ($windowNumber === null) {
            return view('window/select');
        }

        // Validate window number
        $window = $this->windowModel->getWindowByNumber($windowNumber);
        if (!$window) {
            return redirect()->to('index.php/window')->with('error', 'Invalid window number');
        }

        $data['window'] = $window;
        $data['now_serving'] = $this->queueModel->getServingByWindow($window['id']);
        $data['waiting_count'] = $this->queueModel->getWaitingCount($window['id']);
        $data['waiting_list'] = $this->queueModel->getWaitingByWindow($window['id']);
        
        // Check if coming from admin
        $data['from_admin'] = $this->request->getGet('from_admin') === 'true';

        return view('window/dashboard', $data);
    }

    public function callNext($windowId)
    {
        $window = $this->windowModel->find($windowId);
        if (!$window) {
            return $this->response->setJSON(['success' => false, 'message' => 'Window not found']);
        }

        // Get next in queue
        $next = $this->queueModel->getNextInQueue($windowId);
        if (!$next) {
            return $this->response->setJSON(['success' => false, 'message' => 'No customers waiting']);
        }

        // If there's currently someone serving, complete them first
        $currentServing = $this->queueModel->getServingByWindow($windowId);
        if ($currentServing) {
            // Mark current as completed
            $this->queueModel->markAsCompleted($currentServing['id']);
            
            // Add to service records
            $this->serviceRecordModel->addRecord([
                'window_id' => $currentServing['window_id'],
                'ticket_number' => $currentServing['ticket_number'],
                'service_date' => date('Y-m-d'),
                'service_type' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'daily_reset_excluded' => 0,
                'monthly_reset_excluded' => 0
            ]);
        }

        // Mark next as serving
        $this->queueModel->markAsServing($next['id']);
        $this->windowModel->updateCurrentNumber($windowId, $next['queue_number']);

        return $this->response->setJSON([
            'success' => true,
            'window_number' => $window['window_number'],
            'ticket_number' => $next['ticket_number']
        ]);
    }

    public function skip($queueId)
    {
        $queue = $this->queueModel->find($queueId);
        if (!$queue) {
            return $this->response->setJSON(['success' => false, 'message' => 'Queue not found']);
        }

        // Mark as skipped
        $this->queueModel->markAsSkipped($queueId);

        // Add to service records for statistics
        $this->serviceRecordModel->addRecord([
            'window_id' => $queue['window_id'],
            'ticket_number' => $queue['ticket_number'],
            'service_date' => date('Y-m-d'),
            'service_type' => 'skipped',
            'created_at' => date('Y-m-d H:i:s'),
            'daily_reset_excluded' => 0, // Include in daily stats
            'monthly_reset_excluded' => 0 // Include in monthly stats
        ]);

        // Serve next
        $this->serveNext($queue['window_id']);

        return $this->response->setJSON(['success' => true]);
    }

    public function getData($windowId)
    {
        $window = $this->windowModel->find($windowId);
        if (!$window) {
            return $this->response->setJSON(['success' => false]);
        }

        $nowServing = $this->queueModel->getServingByWindow($windowId);
        $waitingList = $this->queueModel->getWaitingByWindow($windowId);

        return $this->response->setJSON([
            'success' => true,
            'now_serving' => $nowServing ? $nowServing['ticket_number'] : 'None',
            'serving_queue_id' => $nowServing ? $nowServing['id'] : null,
            'waiting_count' => count($waitingList),
            'waiting_list' => $waitingList,
            'current_number' => $window['current_number']
        ]);
    }

    private function serveNext($windowId)
    {
        $next = $this->queueModel->getNextInQueue($windowId);
        if ($next) {
            $this->queueModel->markAsServing($next['id']);
            $this->windowModel->updateCurrentNumber($windowId, $next['queue_number']);
        } else {
            $this->windowModel->updateCurrentNumber($windowId, 0);
        }
    }
}
