<?php

namespace App\Controllers;

use App\Models\CustomerRecordsModel;
use App\Models\WindowModel;

class CustomerRecords extends BaseController
{
    protected $customerRecordsModel;
    protected $windowModel;

    public function __construct()
    {
        $this->customerRecordsModel = new CustomerRecordsModel();
        $this->windowModel = new WindowModel();
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
        
        $records = $this->customerRecordsModel->getCustomerRecords($windowId, $startDate, $endDate);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $records
        ]);
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
            'Window',
            'Status',
            'Created At',
            'Queueing Time',
            'Start Time',
            'End Time',
            'Waiting Time',
            'Serving Time',
            'Ticket Number',
            'Customer Name',
            'Document Name',
            'Service',
            'Remarks'
        ];
        
        foreach ($records as $record) {
            $csvData[] = [
                $record['transaction_number'],
                $record['window_name'] . ' (Window ' . $record['window_number'] . ')',
                ucfirst($record['status']),
                $record['created_at'],
                $record['queueing_time'] ?: '',
                $record['start_time'] ?: '',
                $record['end_time'] ?: '',
                $record['waiting_time'] ?: '',
                $record['serving_time'] ?: '',
                $record['ticket_number'],
                $record['customer_name'],
                $record['document_name'],
                $record['service'],
                $record['remarks'] ?: ''
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
