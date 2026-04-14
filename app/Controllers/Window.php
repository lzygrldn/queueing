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
            return redirect()->to(base_url('admin'));
        }

        // Validate window number
        $window = $this->windowModel->getWindowByNumber($windowNumber);
        if (!$window) {
            return redirect()->to(base_url('window'))->with('error', 'Invalid window number');
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
            
            // Store if from completed or skipped BEFORE changing status
            $isFromCompleted = ($targetQueue['status'] === 'completed' || $targetQueue['status'] === 'skipped');
            
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
                
                // Update record status to completed
                $this->customerRecordsModel->update($currentRecord['id'], [
                    'status' => 'completed'
                ]);
                
                log_message('info', '✓ SUCCESSFULLY completed customer record');
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
        
        // Do NOT create customer record here - only create when customer data is actually saved via saveCustomer()
        // This prevents skipped tickets from appearing in customer records
        
        $response = [
            'success' => true,
            'window_number' => $window['window_number'],
            'ticket_number' => $targetQueue['ticket_number'],
            'transaction_number' => null, // Will be generated when customer data is saved
            'service_type' => $targetQueue['service_type'] ?? '',
            'is_from_completed' => $isFromCompleted
        ];
        
        log_message('info', 'callNext response: ' . json_encode($response));
        
        return $this->response->setJSON($response);
    }

    public function getCustomerDataByTransaction($transactionNumber)
    {
        log_message('info', 'getCustomerDataByTransaction called for: ' . $transactionNumber);
        
        // URL decode the transaction number
        $transactionNumber = urldecode($transactionNumber);
        log_message('info', 'Decoded transaction number: ' . $transactionNumber);
        
        // Find customer record by exact transaction number match
        $customer = $this->customerRecordsModel
            ->getCustomerRecordByTransactionNumber($transactionNumber);
            
        log_message('info', 'Customer query result: ' . ($customer ? 'Found' : 'Not found'));
        
        if ($customer) {
            log_message('info', 'Customer data: ' . json_encode($customer));
            return $this->response->setJSON([
                'success' => true,
                'customer' => $customer
            ]);
        } else {
            log_message('info', 'No customer data found for transaction: ' . $transactionNumber);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer data not found'
            ]);
        }
    }

    public function getCustomerData($ticketNumber)
    {
        log_message('info', 'getCustomerData called for ticket: ' . $ticketNumber);
        
        // Extract the queue number from the ticket number (e.g., BREQS-001 -> 1)
        $parts = explode('-', $ticketNumber);
        $queueNumber = isset($parts[count($parts) - 1]) ? intval($parts[count($parts) - 1]) : 0;
        
        // Find customer record by matching transaction number pattern
        // Transaction numbers are in format: PREFIX20260331-001
        $transactionPattern = '%-' . str_pad($queueNumber, 3, '0', STR_PAD_LEFT);
        $customer = $this->customerRecordsModel
            ->like('transaction_number', $transactionPattern, 'before')
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

        $windowId = $queue['window_id'];

        // Mark as completed in queue
        $this->queueModel->markAsCompleted($queueId);

        // Add to service records for statistics
        $this->serviceRecordModel->addRecord([
            'window_id' => $windowId,
            'ticket_number' => $queue['ticket_number'],
            'service_date' => date('Y-m-d'),
            'service_type' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'daily_reset_excluded' => 0,
            'monthly_reset_excluded' => 0
        ]);

        // Clear current serving from window
        $this->windowModel->updateCurrentNumber($windowId, 0);

        // Return success - do NOT auto-serve next customer
        return $this->response->setJSON(['success' => true]);
    }
    
    // Serve a specific queue item
    private function serveSpecificQueue($windowId, $queueItem)
    {
        $window = $this->windowModel->find($windowId);
        if (!$window || !$queueItem) {
            return null;
        }

        // Mark target as serving
        $this->queueModel->markAsServing($queueItem['id']);
        $this->windowModel->updateCurrentNumber($windowId, $queueItem['queue_number']);
        
        // Do NOT create customer record here - only create when customer data is actually saved
        // This prevents skipped tickets from appearing in customer records
        
        return [
            'ticket_number' => $queueItem['ticket_number'],
            'transaction_number' => null // Will be generated when customer data is saved
        ];
    }

    public function skip($queueId)
    {
        $queue = $this->queueModel->find($queueId);
        if (!$queue) {
            return $this->response->setJSON(['success' => false, 'message' => 'Queue not found']);
        }

        // Mark as skipped in queue
        $this->queueModel->markAsSkipped($queueId);

        // Add to service records for statistics
        $this->serviceRecordModel->addRecord([
            'window_id' => $queue['window_id'],
            'ticket_number' => $queue['ticket_number'],
            'service_date' => date('Y-m-d'),
            'service_type' => 'skipped',
            'created_at' => date('Y-m-d H:i:s'),
            'daily_reset_excluded' => 0,
            'monthly_reset_excluded' => 0
        ]);

        // Clear current serving from window (do NOT auto-serve next)
        $this->windowModel->updateCurrentNumber($queue['window_id'], 0);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Auto-serve the first customer in queue on page load
     */
    public function autoServeFirst($windowId)
    {
        $window = $this->windowModel->find($windowId);
        if (!$window) {
            return $this->response->setJSON(['success' => false, 'message' => 'Window not found']);
        }

        // Check if someone is already being served
        $currentServing = $this->queueModel->getServingByWindow($windowId);
        if ($currentServing) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Already serving a customer',
                'ticket_number' => $currentServing['ticket_number']
            ]);
        }

        // Get the first customer in waiting queue
        $nextInQueue = $this->queueModel->getNextInQueue($windowId);
        if (!$nextInQueue) {
            return $this->response->setJSON(['success' => false, 'message' => 'No customers waiting']);
        }

        // Serve this customer
        $served = $this->serveSpecificQueue($windowId, $nextInQueue);
        
        if ($served) {
            return $this->response->setJSON([
                'success' => true,
                'ticket_number' => $served['ticket_number'],
                'transaction_number' => $served['transaction_number']
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to serve customer']);
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
            'now_serving_service_type' => $nowServing ? ($nowServing['service_type'] ?? '') : '',
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
        
        // Find existing customer record - first try by transaction number
        $existingRecord = $this->customerRecordsModel
            ->getCustomerRecordByTransactionNumber($transactionNumber);
        
        // If not found by transaction number, try to find by current serving ticket
        if (!$existingRecord) {
            $currentServing = $this->queueModel->getServingByWindow($windowId);
            if ($currentServing) {
                // Generate expected transaction number pattern from the serving ticket
                $expectedTxnPattern = $this->generateTransactionPattern($currentServing['ticket_number']);
                
                // Try to find by matching transaction number pattern
                $existingRecord = $this->customerRecordsModel
                    ->like('transaction_number', $expectedTxnPattern, 'after')
                    ->where('window_id', $windowId)
                    ->orderBy('id', 'DESC')
                    ->first();
                
                if ($existingRecord) {
                    log_message('info', 'Found existing record by transaction pattern for ticket: ' . $currentServing['ticket_number']);
                }
            }
        }
        
        log_message('info', 'Looking for customer record with transaction: ' . $transactionNumber);
        log_message('info', 'Existing record found: ' . ($existingRecord ? 'YES' : 'NO'));
        
        if (!$existingRecord) {
            // Get current serving queue item to create new record
            $currentServing = $this->queueModel->getServingByWindow($windowId);
            if (!$currentServing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No customer currently being served'
                ]);
            }
            
            // Create customer record if it doesn't exist
            log_message('info', 'Creating customer record for ticket: ' . $currentServing['ticket_number']);
            
            $window = $this->windowModel->find($windowId);
            
            $customerRecordData = [
                'window_id' => $windowId,
                'window_name' => $window['window_name'],
                'queue_number' => $currentServing['queue_number'],
                'customer_name' => $customerName,
                'document_name' => $documentName,
                'service' => $service,
                'remarks' => $remarks
            ];
            
            $recordId = $this->customerRecordsModel->createCustomerRecord($customerRecordData);
            
            if (!$recordId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create customer record'
                ]);
            }
            
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

    public function searchCustomers()
    {
        $query = $this->request->getGet('q');
        
        if (empty($query) || strlen($query) < 2) {
            return $this->response->setJSON([
                'success' => true,
                'customers' => []
            ]);
        }
        
        // Search for customers by document_name or transaction_number only
        $customers = $this->customerRecordsModel
            ->like('document_name', $query, 'both', true, true)
            ->orLike('transaction_number', $query, 'both', true, true)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();
        
        return $this->response->setJSON([
            'success' => true,
            'customers' => $customers
        ]);
    }
    
    // Generate transaction number pattern from ticket number for matching
    private function generateTransactionPattern($ticketNumber)
    {
        // Extract the number from ticket (e.g., BREQS-001 -> 001)
        $parts = explode('-', $ticketNumber);
        $ticketNum = end($parts);
        
        // Get today's date in YYYYMMDD format
        $date = date('Ymd');
        
        // Return pattern like: %20260331-001
        return $date . '-' . str_pad($ticketNum, 3, '0', STR_PAD_LEFT);
    }
}
