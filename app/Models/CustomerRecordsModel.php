<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerRecordsModel extends Model
{
    protected $table = 'customer_records';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'transaction_number', 
        'window_id', 
        'window_name', 
        'ticket_number', 
        'customer_name', 
        'document_name', 
        'service', 
        'remarks', 
        'status', 
        'queueing_time', 
        'start_time', 
        'end_time', 
        'waiting_time', 
        'serving_time',
        'created_at'
    ];
    protected $useTimestamps = false;

    public function getCustomerRecords($windowId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->select('customer_records.*, windows.window_name, windows.window_number')
                        ->join('windows', 'windows.id = customer_records.window_id');
        
        if ($windowId) {
            $builder->where('customer_records.window_id', $windowId);
        }
        
        if ($startDate && $endDate) {
            $builder->where('customer_records.created_at >=', $startDate . ' 00:00:00');
            $builder->where('customer_records.created_at <=', $endDate . ' 23:59:59');
        }
        
        return $builder->orderBy('customer_records.created_at', 'DESC')
                        ->findAll();
    }

    public function getCustomerRecordByTransactionNumber($transactionNumber)
    {
        return $this->select('customer_records.*, windows.window_name, windows.window_number')
                    ->join('windows', 'windows.id = customer_records.window_id')
                    ->where('customer_records.transaction_number', $transactionNumber)
                    ->first();
    }

    public function createCustomerRecord($queueData)
    {
        // Extract time from transaction number (format: TRX-YYYYMMDD-HHMM)
        $transactionNumber = $queueData['transaction_number'];
        $timeFromTransaction = '00:00:00'; // Default
        
        if (preg_match('/TRX-\d{8}-(\d{2})(\d{2})/', $transactionNumber, $matches)) {
            $hours = $matches[1];
            $minutes = $matches[2];
            $timeFromTransaction = $hours . ':' . $minutes . ':00';
        }
        
        $data = [
            'transaction_number' => $queueData['transaction_number'],
            'window_id' => $queueData['window_id'],
            'window_name' => $queueData['window_name'],
            'ticket_number' => $queueData['ticket_number'],
            'customer_name' => $queueData['customer_name'],
            'document_name' => $queueData['document_name'],
            'service' => $queueData['service'],
            'remarks' => $queueData['remarks'],
            'status' => 'serving',
            'queueing_time' => $timeFromTransaction, // Extract from transaction number
            'start_time' => $timeFromTransaction, // Same as queueing time when called
            // waiting_time will be calculated separately
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }

    public function updateCustomerStatus($transactionNumber, $status, $endTime = null)
    {
        $updateData = ['status' => $status];
        
        // Get current record to calculate times
        $record = $this->getCustomerRecordByTransactionNumber($transactionNumber);
        
        if ($endTime && $record) {
            // Extract time from transaction number for end_time
            $timeFromTransaction = '00:00:00';
            if (preg_match('/TRX-\d{8}-(\d{2})(\d{2})/', $transactionNumber, $matches)) {
                $hours = $matches[1];
                $minutes = $matches[2];
                $timeFromTransaction = $hours . ':' . $minutes . ':00';
            }
            
            $updateData['end_time'] = $timeFromTransaction;
            
            // Calculate serving time
            if ($record['start_time']) {
                $updateData['serving_time'] = $this->calculateServiceTime($record['start_time'], $timeFromTransaction);
            }
            
            // Calculate waiting time (if not already calculated)
            if ($record['queueing_time'] && $record['start_time'] && empty($record['waiting_time'])) {
                $updateData['waiting_time'] = $this->calculateWaitingTime($record['queueing_time'], $record['start_time']);
            }
        }
        
        return $this->where('transaction_number', $transactionNumber)
                    ->update($updateData);
    }

    public function updateCustomerStatusByTicketNumber($ticketNumber, $status, $endTime = null)
    {
        $updateData = ['status' => $status];
        
        // Get current record to calculate times
        $record = $this->where('ticket_number', $ticketNumber)->first();
        
        if ($endTime && $record) {
            // Extract time from transaction number for end_time
            $transactionNumber = $record['transaction_number'];
            $timeFromTransaction = '00:00:00';
            if (preg_match('/TRX-\d{8}-(\d{2})(\d{2})/', $transactionNumber, $matches)) {
                $hours = $matches[1];
                $minutes = $matches[2];
                $timeFromTransaction = $hours . ':' . $minutes . ':00';
            }
            
            $updateData['end_time'] = $timeFromTransaction;
            
            // Calculate serving time
            if ($record['start_time']) {
                $updateData['serving_time'] = $this->calculateServiceTime($record['start_time'], $timeFromTransaction);
            }
            
            // Calculate waiting time (if not already calculated)
            if ($record['queueing_time'] && $record['start_time'] && empty($record['waiting_time'])) {
                $updateData['waiting_time'] = $this->calculateWaitingTime($record['queueing_time'], $record['start_time']);
            }
        }
        
        return $this->where('ticket_number', $ticketNumber)
                    ->update($updateData);
    }

    private function calculateWaitingTime($queueingTime, $startTime)
    {
        try {
            $queueing = new \DateTime($queueingTime);
            $start = new \DateTime($startTime);
            $interval = $queueing->diff($start);
            
            // Always show hours and minutes format: "X hours Y minutes"
            $hours = $interval->h;
            $minutes = $interval->i;
            
            return $hours . ' hours ' . $minutes . ' minutes';
        } catch (Exception $e) {
            return 'Calculating...';
        }
    }

    private function calculateServiceTime($startTime, $endTime)
    {
        try {
            $start = new \DateTime($startTime);
            $end = new \DateTime($endTime);
            $interval = $start->diff($end);
            
            // Always show hours and minutes format: "X hours Y minutes"
            $hours = $interval->h;
            $minutes = $interval->i;
            
            return $hours . ' hours ' . $minutes . ' minutes';
        } catch (Exception $e) {
            return 'Calculating...';
        }
    }

    /**
     * Update all existing customer records with proper time calculations and time-only format
     */
    public function updateAllExistingRecords()
    {
        // Get all records that need time calculations
        $records = $this->findAll();
        $updatedCount = 0;
        
        echo "Found " . count($records) . " records to process.<br>";
        
        foreach ($records as $record) {
            $updateData = [];
            $recordId = $record['id'];
            
            // Convert queueing_time to time-only format (extract from datetime)
            if ($record['queueing_time']) {
                // Handle both datetime format and already time-only format
                if (strpos($record['queueing_time'], ' ') !== false) {
                    // It's a datetime, extract time part
                    $timePart = explode(' ', $record['queueing_time'])[1] ?? '00:00:00';
                    $updateData['queueing_time'] = $timePart;
                    echo "Record $recordId: queueing_time '{$record['queueing_time']}' -> '$timePart'<br>";
                } else {
                    // Already time-only format
                    $updateData['queueing_time'] = $record['queueing_time'];
                }
            }
            
            // Convert start_time to time-only format (extract from datetime)
            if ($record['start_time']) {
                // Handle both datetime format and already time-only format
                if (strpos($record['start_time'], ' ') !== false) {
                    // It's a datetime, extract time part
                    $timePart = explode(' ', $record['start_time'])[1] ?? '00:00:00';
                    $updateData['start_time'] = $timePart;
                    echo "Record $recordId: start_time '{$record['start_time']}' -> '$timePart'<br>";
                } else {
                    // Already time-only format
                    $updateData['start_time'] = $record['start_time'];
                }
            }
            
            // Convert end_time to time-only format (extract from datetime)
            if ($record['end_time']) {
                // Handle both datetime format and already time-only format
                if (strpos($record['end_time'], ' ') !== false) {
                    // It's a datetime, extract time part
                    $timePart = explode(' ', $record['end_time'])[1] ?? '00:00:00';
                    $updateData['end_time'] = $timePart;
                    echo "Record $recordId: end_time '{$record['end_time']}' -> '$timePart'<br>";
                } else {
                    // Already time-only format
                    $updateData['end_time'] = $record['end_time'];
                }
            }
            
            // Calculate waiting time if we have both queueing_time and start_time
            if ($record['queueing_time'] && $record['start_time']) {
                // Use the original datetime values for calculation
                $queueingTime = $record['queueing_time'];
                $startTime = $record['start_time'];
                
                // If they're datetime format, use them directly
                if (strpos($queueingTime, ' ') !== false && strpos($startTime, ' ') !== false) {
                    $newWaitingTime = $this->calculateWaitingTime($queueingTime, $startTime);
                } else {
                    // If they're time-only, create dummy dates for calculation
                    $newWaitingTime = $this->calculateWaitingTime(
                        '2024-01-01 ' . ($updateData['queueing_time'] ?? $record['queueing_time']),
                        '2024-01-01 ' . ($updateData['start_time'] ?? $record['start_time'])
                    );
                }
                $updateData['waiting_time'] = $newWaitingTime;
                echo "Record $recordId: waiting_time -> '$newWaitingTime'<br>";
            }
            
            // Calculate service time if we have both start_time and end_time
            if ($record['start_time'] && $record['end_time']) {
                // Use the original datetime values for calculation
                $startTime = $record['start_time'];
                $endTime = $record['end_time'];
                
                // If they're datetime format, use them directly
                if (strpos($startTime, ' ') !== false && strpos($endTime, ' ') !== false) {
                    $newServingTime = $this->calculateServiceTime($startTime, $endTime);
                } else {
                    // If they're time-only, create dummy dates for calculation
                    $newServingTime = $this->calculateServiceTime(
                        '2024-01-01 ' . ($updateData['start_time'] ?? $record['start_time']),
                        '2024-01-01 ' . ($updateData['end_time'] ?? $record['end_time'])
                    );
                }
                $updateData['serving_time'] = $newServingTime;
                echo "Record $recordId: serving_time -> '$newServingTime'<br>";
            }
            
            // Update the record if we have new data
            if (!empty($updateData)) {
                $this->update($recordId, $updateData);
                $updatedCount++;
                echo "Record $recordId: Updated successfully.<br>";
            }
        }
        
        echo "Update complete. Processed $updatedCount records.<br>";
        return true;
    }
}
