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
        log_message('info', 'callNext called for windowId: ' . $windowId);
        
        $window = $this->windowModel->find($windowId);
        if (!$window) {
            log_message('error', 'Window not found: ' . $windowId);
            return $this->response->setJSON(['success' => false, 'message' => 'Window not found']);
        }

        // Get specific queue ID from POST data if provided
        $queueId = $this->request->getPost('queue_id');
        log_message('info', 'Queue ID from POST: ' . $queueId);
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
            log_message('info', '=== COMPLETING CURRENT SERVING ===');
            log_message('info', 'Current serving ticket: ' . $currentServing['ticket_number']);
            
            // Mark current as completed in queue
            $this->queueModel->markAsCompleted($currentServing['id']);
            log_message('info', 'Marked queue item as completed');
            
            // Complete current customer record
            // Find by transaction_number since ticket_number was removed from table
            $currentRecord = $this->customerRecordsModel
                ->where('window_id', $windowId)
                ->where('status', 'serving')
                ->orderBy('id', 'DESC')
                ->first();
            if ($currentRecord) {
                log_message('info', 'Found customer record: ' . $currentRecord['transaction_number']);
                log_message('info', 'Current status: ' . $currentRecord['status']);
                log_message('info', 'Start time: ' . ($currentRecord['start_time'] ?? 'NULL'));
                
                $updated = $this->customerRecordsModel->updateCustomerStatus($currentRecord['transaction_number'], 'completed');
                
                if ($updated) {
                    log_message('info', '✓ SUCCESSFULLY completed customer record');
                } else {
                    log_message('error', '✗ FAILED to complete customer record');
                }
            } else {
                log_message('error', '✗ NO CUSTOMER RECORD found for window: ' . $windowId);
            }
            
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
            log_message('info', '=== END COMPLETION ===');
        } else {
            log_message('info', 'No current serving customer to complete');
        }

        // Mark target as serving
        $this->queueModel->markAsServing($targetQueue['id']);
        $this->windowModel->updateCurrentNumber($windowId, $targetQueue['queue_number']);
        
        // Check if customer record already exists for this window
        $existingRecord = $this->customerRecordsModel
            ->where('window_id', $windowId)
            ->where('status', 'serving')
            ->orderBy('id', 'DESC')
            ->first();
        $transactionNumber = '';
        
        if ($existingRecord) {
            // This shouldn't happen - record should be completed when previous is done
            log_message('error', 'Unexpected: Record already exists for window: ' . $windowId);
            $transactionNumber = $existingRecord['transaction_number'];
        } else {
            // Create new customer record FIRST
            log_message('info', 'Creating new customer record for ticket: ' . $targetQueue['ticket_number']);
            
            $customerRecordData = [
                'window_id' => $windowId,
                'window_name' => $window['window_name'],
                'ticket_number' => $targetQueue['ticket_number'], // Keep for service extraction
                'customer_name' => '',
                'document_name' => '',
                'service' => '',
                'remarks' => '',
                // ✅ Use queue's created_at for queueing time (when ticket was printed)
                'created_at' => $targetQueue['created_at'] ?? date('Y-m-d H:i:s')
            ];
            
            $recordId = $this->customerRecordsModel->createCustomerRecord($customerRecordData);
            
            if (!$recordId) {
                log_message('error', 'Failed to create customer record for ticket: ' . $targetQueue['ticket_number']);
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create customer record']);
            }
            
            $newRecord = $this->customerRecordsModel->find($recordId);
            if (!$newRecord) {
                log_message('error', 'Failed to find newly created record ID: ' . $recordId);
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to find customer record']);
            }
            
            $transactionNumber = $newRecord['transaction_number'];
            log_message('info', 'Created customer record with transaction: ' . $transactionNumber);
            log_message('info', 'Queueing time (ticket printed): ' . $newRecord['queueing_time']);
            
            // Start service for new record (sets start_time when callNext clicked)
            $started = $this->customerRecordsModel->startService($transactionNumber);
            if ($started) {
                log_message('info', 'Started service for: ' . $transactionNumber);
            } else {
                log_message('error', 'Failed to start service for: ' . $transactionNumber);
            }
        }
        
        $response = [
            'success' => true,
            'window_number' => $window['window_number'],
            'ticket_number' => $targetQueue['ticket_number']
        ];
        
        log_message('info', 'callNext response: ' . json_encode($response));
        
        return $this->response->setJSON($response);
    }

    public function skip($queueId)
    {
        $queue = $this->queueModel->find($queueId);
        if (!$queue) {
            return $this->response->setJSON(['success' => false, 'message' => 'Queue not found']);
        }

        // Mark as skipped in queue
        $this->queueModel->markAsSkipped($queueId);

        // Update customer record status to skipped
        $record = $this->customerRecordsModel
            ->where('window_id', $queue['window_id'])
            ->where('status', 'serving')
            ->orderBy('id', 'DESC')
            ->first();
        if ($record) {
            $this->customerRecordsModel->updateCustomerStatus($record['transaction_number'], 'skipped');
            log_message('info', 'Skipped customer record for window: ' . $queue['window_id']);
        }

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
        $customerName = $this->request->getPost('customerName');
        $documentName = $this->request->getPost('documentName');
        $service = $this->request->getPost('service');
        $remarks = $this->request->getPost('remarks');
        
        // Get window info
        $windowId = $this->request->getPost('window_id');
        $windowName = $this->request->getPost('window_name');
        
        // Get current serving queue item
        $currentServing = $this->queueModel->getServingByWindow($windowId);
        if (!$currentServing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No customer currently being served'
            ]);
        }
        
        // Find existing customer record for current serving ticket
        $existingRecord = $this->customerRecordsModel
            ->where('window_id', $windowId)
            ->where('status', 'serving')
            ->orderBy('id', 'DESC')
            ->first();
        if (!$existingRecord) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No customer record found for current serving ticket'
            ]);
        }
        
        // Update the existing record with customer information
        $updateData = [
            'customer_name' => $customerName,
            'document_name' => $documentName,
            'service' => $service,
            'remarks' => $remarks,
            'window_id' => $windowId,
            'window_name' => $windowName
        ];
        
        try {
            $updated = $this->customerRecordsModel->update($existingRecord['id'], $updateData);
            
            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Customer information updated successfully',
                    'record_id' => $existingRecord['id']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update customer information'
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
