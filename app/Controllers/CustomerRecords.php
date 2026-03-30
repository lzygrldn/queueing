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
        
        try {
            $records = $this->customerRecordsModel->getCustomerRecords($windowId, $startDate, $endDate);
            
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

            // Generate ticket number
            $nextNumber = ($window['last_released'] ?? 0) + 1;
            $ticketNumber = strtoupper($service) . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

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
                
                // Update customer record status to serving and set start time
                $transactionNumber = $this->customerRecordsModel
                    ->where('id', $recordId)
                    ->first()['transaction_number'];
                $this->customerRecordsModel->startService($transactionNumber);
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
            'Queueing Time',
            'Start Time',
            'End Time',
            'Waiting Time',
            'Serving Time',
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
                $record['queueing_time'] ?: '',
                $record['start_time'] ?: '',
                $record['end_time'] ?: '',
                $record['waiting_time'] ?: '',
                $record['serving_time'] ?: '',
               
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

    /**
     * Update all existing customer records with new time format
     */
    public function updateDatabase()
    {
        try {
            $result = $this->customerRecordsModel->updateAllExistingRecords();
            
            if ($result) {
                echo "Database update started successfully! Processing all records...<br>";
                echo "Please refresh the customer records page to see the changes.<br>";
                echo "All time fields (queueing_time, start_time, end_time) should now show time-only format.<br>";
                echo "Waiting time and serving time should show 'X hours Y minutes' format.";
            } else {
                echo "Failed to update database.";
            }
        } catch (Exception $e) {
            echo "Error updating database: " . $e->getMessage();
        }
    }

    /**
     * Run migration to convert existing DATETIME values to TIME-only
     */
    public function convertTimeColumns()
    {
        try {
            $db = \Config\Database::connect();
            
            // Convert existing DATETIME values to TIME-only
            $sql = "UPDATE customer_records SET 
                    queueing_time = TIME(queueing_time),
                    start_time = TIME(start_time),
                    end_time = TIME(end_time)
                    WHERE queueing_time IS NOT NULL OR start_time IS NOT NULL OR end_time IS NOT NULL";
            
            $db->query($sql);
            
            // Update waiting_time and serving_time to human-readable format
            $sql = "UPDATE customer_records SET 
                    waiting_time = CASE 
                        WHEN waiting_time LIKE '%hours%' THEN waiting_time
                        WHEN waiting_time IS NOT NULL THEN CONCAT(HOUR(waiting_time), ' hours ', MINUTE(waiting_time), ' minutes')
                        ELSE waiting_time
                    END,
                    serving_time = CASE 
                        WHEN serving_time LIKE '%hours%' THEN serving_time
                        WHEN serving_time IS NOT NULL THEN CONCAT(HOUR(serving_time), ' hours ', MINUTE(serving_time), ' minutes')
                        ELSE serving_time
                    END";
            
            $db->query($sql);
            
            echo "✅ Time columns converted successfully!<br>";
            echo "- queueing_time, start_time, end_time converted to TIME-only format (HH:MM:SS)<br>";
            echo "- waiting_time, serving_time converted to human-readable format (X hours Y minutes)<br>";
            echo "<br><strong>New records will now store time-only values!</strong><br>";
            echo "<a href='" . base_url('customerRecords') . "'>Back to Customer Records</a>";
            
        } catch (Exception $e) {
            echo "❌ Error converting time columns: " . $e->getMessage();
        }
    }

    /**
     * Run migration to convert time columns to TIME type
     */
    public function runMigration()
    {
        try {
            $db = \Config\Database::connect();
            
            // Alter table columns to TIME type
            $sql = "ALTER TABLE customer_records 
                    MODIFY COLUMN queueing_time TIME NULL,
                    MODIFY COLUMN start_time TIME NULL,
                    MODIFY COLUMN end_time TIME NULL";
            
            $db->query($sql);
            
            echo "Migration completed successfully!<br>";
            echo "Time columns (queueing_time, start_time, end_time) are now TIME type (no date).<br>";
            echo "Please run the database update again to populate the time-only values.";
        } catch (Exception $e) {
            echo "Error running migration: " . $e->getMessage();
        }
    }
}
