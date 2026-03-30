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
        
        // Check if current serving was from completed list (for initial skip button state)
        $data['is_serving_from_completed'] = false;
        if ($data['now_serving']) {
            $completedRecord = $this->serviceRecordModel
                ->where('window_id', $window['id'])
                ->where('ticket_number', $data['now_serving']['ticket_number'])
                ->where('service_type', 'completed')
                ->first();
            $data['is_serving_from_completed'] = !empty($completedRecord);
        }
        
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
            
            // Store if from completed BEFORE changing status
            $isFromCompleted = ($targetQueue['status'] === 'completed');
            
            // Move the target queue back to waiting status first
            $this->queueModel->markAsWaiting($queueId);
        } else {
            // Get next in queue (default behavior)
            $targetQueue = $this->queueModel->getNextInQueue($windowId);
            if (!$targetQueue) {
                return $this->response->setJSON(['success' => false, 'message' => 'No customers waiting']);
            }
            $isFromCompleted = false;
        }

        // If there's currently someone serving, complete them first
        $currentServing = $this->queueModel->getServingByWindow($windowId);
        if ($currentServing) {
            log_message('info', '=== COMPLETING CURRENT SERVING ===');
            log_message('info', 'Current serving ticket: ' . $currentServing['ticket_number']);
            
            // Set END TIME for current customer (when callNext clicked)
            $endTime = date('H:i:s');
            log_message('info', 'Setting end time for current customer to: ' . $endTime);
            
            // Mark current as completed in queue
            $this->queueModel->markAsCompleted($currentServing['id']);
            log_message('info', 'Marked queue item as completed');
            
            // Complete current customer record
            $currentRecord = $this->customerRecordsModel
                ->where('window_id', $windowId)
                ->where('status', 'serving')
                ->orderBy('id', 'DESC')
                ->first();
                
            if ($currentRecord) {
                log_message('info', 'Found customer record: ' . $currentRecord['transaction_number']);
                log_message('info', 'Current status: ' . $currentRecord['status']);
                log_message('info', 'Start time: ' . ($currentRecord['start_time'] ?? 'NULL'));
                log_message('info', 'Queueing time: ' . ($currentRecord['queueing_time'] ?? 'NULL'));
                
                // Calculate serving time before completing
                $servingTime = '0 hours 0 minutes';
                if ($currentRecord['start_time']) {
                    $startDateTime = date('Y-m-d') . ' ' . $currentRecord['start_time'];
                    $endDateTime = date('Y-m-d') . ' ' . $endTime;
                    $servingTime = $this->calculateDuration($startDateTime, $endDateTime);
                    log_message('info', 'Calculated serving time: ' . $servingTime);
                }
                
                // Update record with end time and serving time
                $this->customerRecordsModel->update($currentRecord['id'], [
                    'status' => 'completed',
                    'end_time' => $endTime,
                    'serving_time' => $servingTime
                ]);
                
                log_message('info', '✓ SUCCESSFULLY completed customer record with end_time: ' . $endTime . ' and serving_time: ' . $servingTime);
            } else {
                log_message('error', '✗ NO CUSTOMER RECORD found for window: ' . $windowId . ' - but continuing to serve next');
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
        
        // Set START TIME for new customer (when callNext clicked)
        $startTime = date('H:i:s');
        log_message('info', 'Setting start time for new customer to: ' . $startTime);
        
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
            // Calculate waiting time based on queueing_time and current time
            $startTime = date('H:i:s');
            $queueingTime = $newRecord['queueing_time'];
            $waitingTime = '0 hours 0 minutes';
            
            if ($queueingTime) {
                $queueingDateTime = date('Y-m-d') . ' ' . $queueingTime;
                $startDateTime = date('Y-m-d') . ' ' . $startTime;
                $waitingTime = $this->calculateDuration($queueingDateTime, $startDateTime);
                log_message('info', 'Calculated waiting time: ' . $waitingTime);
            }
            
            // Update the record with start_time and waiting_time
            $this->customerRecordsModel->update($recordId, [
                'start_time' => $startTime,
                'waiting_time' => $waitingTime
            ]);
            
            log_message('info', 'Started service for: ' . $transactionNumber . ' at ' . $startTime . ' (waiting: ' . $waitingTime . ')');
        }
        
        $response = [
            'success' => true,
            'window_number' => $window['window_number'],
            'ticket_number' => $targetQueue['ticket_number'],
            'is_from_completed' => $isFromCompleted
        ];
        
        log_message('info', 'callNext response: ' . json_encode($response));
        
        return $this->response->setJSON($response);
    }

    public function getCustomerData($ticketNumber)
    {
        log_message('info', 'getCustomerData called for ticket: ' . $ticketNumber);
        
        // Find customer record by ticket number
        $customer = $this->customerRecordsModel
            ->where('ticket_number', $ticketNumber)
            ->orderBy('id', 'DESC')
            ->first();
            
        log_message('info', 'Customer query result: ' . ($customer ? 'Found' : 'Not found'));
        
        if ($customer) {
            log_message('info', 'Customer data: ' . json_encode($customer));
            return $this->response->setJSON([
                'success' => true,
                'customer' => $customer
            ]);
        } else {
            log_message('info', 'No customer data found for ticket: ' . $ticketNumber);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer data not found'
            ]);
        }
    }

    public function complete($queueId)
    {
        $queue = $this->queueModel->find($queueId);
        if (!$queue) {
            return $this->response->setJSON(['success' => false, 'message' => 'Queue not found']);
        }

        // Set END TIME for current customer (when complete clicked)
        $endTime = date('H:i:s');
        log_message('info', 'Setting end time for completed customer to: ' . $endTime);

        // Mark as completed in queue
        $this->queueModel->markAsCompleted($queueId);

        // Update customer record status to completed
        $record = $this->customerRecordsModel
            ->where('window_id', $queue['window_id'])
            ->where('status', 'serving')
            ->orderBy('id', 'DESC')
            ->first();
        if ($record) {
            $endTime = date('H:i:s');
            $servingTime = '0 hours 0 minutes';
            
            if ($record['start_time']) {
                $startDateTime = date('Y-m-d') . ' ' . $record['start_time'];
                $endDateTime = date('Y-m-d') . ' ' . $endTime;
                $servingTime = $this->calculateDuration($startDateTime, $endDateTime);
            }
            
            $this->customerRecordsModel->update($record['id'], [
                'status' => 'completed',
                'end_time' => $endTime,
                'serving_time' => $servingTime
            ]);
            log_message('info', 'Completed customer record for window: ' . $queue['window_id'] . ' with end_time: ' . $endTime . ' and serving_time: ' . $servingTime);
        }

        // Add to service records for statistics
        $this->serviceRecordModel->addRecord([
            'window_id' => $queue['window_id'],
            'ticket_number' => $queue['ticket_number'],
            'service_date' => date('Y-m-d'),
            'service_type' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'daily_reset_excluded' => 0,
            'monthly_reset_excluded' => 0
        ]);

        // Clear current serving from window (but don't call next)
        $this->windowModel->updateCurrentNumber($queue['window_id'], 0);

        return $this->response->setJSON(['success' => true]);
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
            $endTime = date('H:i:s');
            $servingTime = '0 hours 0 minutes';
            
            if ($record['start_time']) {
                $startDateTime = date('Y-m-d') . ' ' . $record['start_time'];
                $endDateTime = date('Y-m-d') . ' ' . $endTime;
                $servingTime = $this->calculateDuration($startDateTime, $endDateTime);
            }
            
            $this->customerRecordsModel->update($record['id'], [
                'status' => 'skipped',
                'end_time' => $endTime,
                'serving_time' => $servingTime
            ]);
            log_message('info', 'Skipped customer record for window: ' . $queue['window_id'] . ' with end_time: ' . $endTime . ' and serving_time: ' . $servingTime);
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

        // Check if current serving was from completed list
        $isServingFromCompleted = false;
        if ($nowServing) {
            // Check if this ticket has a service record as 'completed' before being called again
            $completedRecord = $this->serviceRecordModel
                ->where('window_id', $windowId)
                ->where('ticket_number', $nowServing['ticket_number'])
                ->where('service_type', 'completed')
                ->first();
            $isServingFromCompleted = !empty($completedRecord);
        }

        // Debug: Log window info for verification
        error_log("getData for Window {$window['window_number']} ({$window['window_name']}) - ID: $windowId");
        error_log("Waiting count: " . count($waitingList));
        error_log("Skipped count: " . count($skippedList));
        error_log("Completed count: " . count($completedList));
        error_log("Is serving from completed: " . ($isServingFromCompleted ? 'YES' : 'NO'));

        return $this->response->setJSON([
            'success' => true,
            'now_serving' => $nowServing ? $nowServing['ticket_number'] : 'None',
            'current_queue_id' => $nowServing ? $nowServing['id'] : null,
            'waiting_count' => count($waitingList),
            'waiting_list' => $waitingList,
            'skipped_list' => $skippedList,
            'completed_list' => $completedList,
            'current_number' => $window['current_number'],
            'is_serving_from_completed' => $isServingFromCompleted,
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
        $transactionNumber = $this->request->getPost('transactionNumber');
        
        // Get window info
        $windowId = $this->request->getPost('window_id');
        $windowName = $this->request->getPost('window_name');
        
        // Validate required fields
        $errors = [];
        if (empty($customerName)) {
            $errors[] = 'Name of Customer must not be empty';
        }
        if (empty($documentName)) {
            $errors[] = 'Name in Document must not be empty';
        }
        if (empty($service)) {
            $errors[] = 'Service must not be empty';
        }
        if (empty($transactionNumber)) {
            $errors[] = 'Transaction Number must not be empty';
        }
        
        if (!empty($errors)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
        }
        
        // Get current serving queue item
        $currentServing = $this->queueModel->getServingByWindow($windowId);
        if (!$currentServing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No customer currently being served'
            ]);
        }
        
        // Find existing customer record for current serving ticket
        log_message('info', 'Looking for customer record for window_id: ' . $windowId . ' with status: serving');
        
        $existingRecord = $this->customerRecordsModel
            ->where('window_id', $windowId)
            ->where('status', 'serving')
            ->orderBy('id', 'DESC')
            ->first();
        
        log_message('info', 'Existing record found: ' . ($existingRecord ? 'YES' : 'NO'));
        if ($existingRecord) {
            log_message('info', 'Record details: ' . json_encode([
                'id' => $existingRecord['id'],
                'transaction_number' => $existingRecord['transaction_number'],
                'customer_name' => $existingRecord['customer_name'],
                'status' => $existingRecord['status']
            ]));
        }
        
        if (!$existingRecord) {
            // Create customer record if it doesn't exist
            log_message('info', 'Creating customer record for ticket: ' . $currentServing['ticket_number']);
            
            $window = $this->windowModel->find($windowId);
            
            $customerRecordData = [
                'window_id' => $windowId,
                'window_name' => $window['window_name'],
                'ticket_number' => $currentServing['ticket_number'], // For service extraction
                'customer_name' => $customerName,
                'document_name' => $documentName,
                'service' => $service,
                'remarks' => $remarks,
                'created_at' => $currentServing['created_at'] ?? date('Y-m-d H:i:s')
            ];
            
            $recordId = $this->customerRecordsModel->createCustomerRecord($customerRecordData);
            
            if (!$recordId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create customer record'
                ]);
            }
            
            // Start service for the new record
            $newRecord = $this->customerRecordsModel->find($recordId);
            $this->customerRecordsModel->startService($newRecord['transaction_number']);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Customer information saved successfully',
                'record_id' => $recordId
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
    
    /**
     * Calculate duration between two datetime strings
     * Returns formatted string like "30 minutes" or "1 hour(s) 30 minute(s)"
     */
    private function calculateDuration($startDateTime, $endDateTime)
    {
        try {
            $start = new \DateTime($startDateTime);
            $end = new \DateTime($endDateTime);
            $diff = $start->diff($end);
            
            $hours = $diff->h;
            $minutes = $diff->i;
            
            if ($hours == 0 && $minutes == 0) {
                return 'Less than 1 minute';
            }
            
            if ($hours == 0) {
                return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
            
            if ($minutes == 0) {
                return $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        } catch (\Exception $e) {
            log_message('error', 'Error calculating duration: ' . $e->getMessage());
            return '0 hours 0 minutes';
        }
    }
}
