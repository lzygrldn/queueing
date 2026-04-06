<?php
 
namespace App\Controllers;

use App\Models\CustomerRecordsModel;
use App\Models\WindowModel;
use App\Models\QueueModel;

class CustomerRecords extends BaseController
{
    protected $customerRecordsModel;
    protected $windowModel;
    protected $queueModel;

    public function __construct()
    {
        $this->customerRecordsModel = new CustomerRecordsModel();
        $this->windowModel = new WindowModel();
        $this->queueModel = new QueueModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Customer Records',
            'windows' => $this->windowModel->findAll()
        ];
        return view('admin/customer_records', $data);
    }

    public function getData()
    {
        $windowId = $this->request->getGet('window_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $search = $this->request->getGet('search');
        
        try {
            $records = $this->customerRecordsModel->getCustomerRecords($windowId, $startDate, $endDate, $search);
            
            return $this->response->setJSON([
                'data' => $records
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in CustomerRecords getData: ' . $e->getMessage());
            return $this->response->setJSON([
                'data' => []
            ]);
        }
    }

    /**
     * Create a new ticket with customer record
     * This creates both a queue ticket and an associated customer record
     */
    public function createTicket()
    {
        try {
            // Get POST data
            $windowId = $this->request->getPost('window_id');
            $customerName = $this->request->getPost('customer_name');
            $documentName = $this->request->getPost('document_name');
            $service = $this->request->getPost('service');
            $remarks = $this->request->getPost('remarks');

            // Validate required fields
            if (!$windowId || !$service) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Window ID and service are required'
                ]);
            }

            // Set timezone
            date_default_timezone_set('Asia/Manila');

            // Get window info
            $window = $this->windowModel->find($windowId);
            if (!$window) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Window not found'
                ]);
            }

            // Map window_id to ticket prefix
            $prefixMap = [
                1 => 'BREQS',
                2 => 'BIRTH',
                3 => 'DEATH',
                4 => 'MARRIAGE'
            ];
            
            // Generate ticket number
            $nextNumber = ($window['last_released'] ?? 0) + 1;
            $ticketPrefix = $prefixMap[$windowId] ?? 'BREQS';
            $ticketNumber = $ticketPrefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Add to queue
            $queueData = [
                'window_id' => $windowId,
                'ticket_number' => $ticketNumber,
                'queue_number' => $nextNumber,
                'status' => 'waiting',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->queueModel->addToQueue($queueData);
            $queueId = $this->queueModel->getInsertID();

            // Update last released
            $this->windowModel->updateLastReleased($windowId, $nextNumber);

            // Create customer record with detailed info
            $customerRecordData = [
                'window_id' => $windowId,
                'window_name' => $window['window_name'],
                'ticket_number' => $ticketNumber,
                'customer_name' => $customerName ?? '',
                'document_name' => $documentName ?? '',
                'service' => $service,
                'remarks' => $remarks ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $recordId = $this->customerRecordsModel->createCustomerRecord($customerRecordData);

            // If no one is being served, serve this one
            $serving = $this->queueModel->getServingByWindow($windowId);
            if (!$serving) {
                $this->queueModel->markAsServing($queueId);
                $this->windowModel->updateCurrentNumber($windowId, $nextNumber);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Ticket created successfully',
                'ticket' => [
                    'number' => $ticketNumber,
                    'queue_id' => $queueId,
                    'record_id' => $recordId,
                    'window_number' => $window['window_number'],
                    'window_name' => $window['window_name'],
                    'datetime' => date('M. d, Y h:i A'),
                    'customer_name' => $customerName,
                    'service' => $service
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating ticket: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creating ticket: ' . $e->getMessage()
            ]);
        }
    }

    public function export()
    {
        $windowId = $this->request->getGet('window_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $records = $this->customerRecordsModel->getCustomerRecords($windowId, $startDate, $endDate);
        
        // Create CSV data
        $csvData = [];
        $csvData[] = [
            'Transaction Number',
            'Customer Name',
            'Document Name',
            'Service',
            'Remarks',
            'Window',
            'Status',
        ];
        
        foreach ($records as $record) {
            $csvData[] = [
                $record['transaction_number'],
                $record['customer_name'],
                $record['document_name'],
                $record['service'],
                $record['remarks'] ?: '',
                $record['window_name'] . ' (Window ' . $record['window_number'] . ')',
                ucfirst($record['status']),
            ];
        }
        
        $filename = 'customer_records_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
