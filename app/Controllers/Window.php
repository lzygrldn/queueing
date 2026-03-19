<?php

namespace App\Controllers;

use App\Models\WindowModel;
use App\Models\QueueModel;
use App\Models\ServiceRecordModel;
use App\Models\CustomerRecordsModel;

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
        $this->customerRecordsModel = new CustomerRecordsModel();
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
        $data['skipped_list'] = $this->queueModel->getSkippedByWindow($window['id']);
        $data['completed_list'] = $this->queueModel->getCompletedByWindow($window['id']);
        
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

        // Get specific queue ID from POST data if provided
        $queueId = $this->request->getPost('queue_id');
        $targetQueue = null;
        
        if ($queueId) {
            // Call specific queue item (from skipped or completed lists)
            $targetQueue = $this->queueModel->find($queueId);
            if (!$targetQueue || $targetQueue['window_id'] != $windowId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid queue item']);
            }
            
            // Move the target queue back to waiting status first
            $this->queueModel->markAsWaiting($queueId);
        } else {
            // Get next in queue (default behavior)
            $targetQueue = $this->queueModel->getNextInQueue($windowId);
            if (!$targetQueue) {
                return $this->response->setJSON(['success' => false, 'message' => 'No customers waiting']);
            }
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

        // Mark target as serving
        $this->queueModel->markAsServing($targetQueue['id']);
        $this->windowModel->updateCurrentNumber($windowId, $targetQueue['queue_number']);

        return $this->response->setJSON([
            'success' => true,
            'window_number' => $window['window_number'],
            'ticket_number' => $targetQueue['ticket_number']
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
        $skippedList = $this->queueModel->getSkippedByWindow($windowId);
        $completedList = $this->queueModel->getCompletedByWindow($windowId);

        // Debug: Log window info for verification
        error_log("getData for Window {$window['window_number']} ({$window['window_name']}) - ID: $windowId");
        error_log("Waiting count: " . count($waitingList));
        error_log("Skipped count: " . count($skippedList));
        error_log("Completed count: " . count($completedList));

        return $this->response->setJSON([
            'success' => true,
            'now_serving' => $nowServing ? $nowServing['ticket_number'] : 'None',
            'current_queue_id' => $nowServing ? $nowServing['id'] : null,
            'waiting_count' => count($waitingList),
            'waiting_list' => $waitingList,
            'skipped_list' => $skippedList,
            'completed_list' => $completedList,
            'current_number' => $window['current_number'],
            'window_info' => [
                'window_number' => $window['window_number'],
                'window_name' => $window['window_name']
            ]
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

    public function saveCustomer()
    {
        $transactionNumber = $this->request->getPost('transactionNumber');
        $customerName = $this->request->getPost('customerName');
        $documentName = $this->request->getPost('documentName');
        $service = $this->request->getPost('service');
        $remarks = $this->request->getPost('remarks');
        
        // Validate required fields
        if (!$transactionNumber || !$customerName || !$documentName || !$service) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fill in all required fields'
            ]);
        }
        
        // Extract window info from transaction number or get from session
        $windowId = $this->request->getPost('window_id');
        $windowName = $this->request->getPost('window_name');
        
        // Get current serving queue item to extract ticket number
        $currentServing = null;
        if ($windowId) {
            $currentServing = $this->queueModel->getServingByWindow($windowId);
        }
        
        $customerData = [
            'transaction_number' => $transactionNumber,
            'window_id' => $windowId,
            'window_name' => $windowName,
            'ticket_number' => $currentServing ? $currentServing['ticket_number'] : '',
            'customer_name' => $customerName,
            'document_name' => $documentName,
            'service' => $service,
            'remarks' => $remarks,
            'status' => 'serving',
            'queue_id' => $currentServing ? $currentServing['id'] : null
        ];
        
        try {
            $recordId = $this->customerRecordsModel->createCustomerRecord($customerData);
            
            if ($recordId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Customer information saved successfully',
                    'record_id' => $recordId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save customer information'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}
