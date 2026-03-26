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
        'customer_name',
        'document_name',
        'service',
        'remarks',
        'status',
        'queueing_time',
        'start_time',
        'end_time',
        'waiting_time',
        'serving_time'
    ];

    protected $useTimestamps = false;

    /*
    |--------------------------------------------------------------------------
    | GET RECORDS (ONLY COMPLETED + SKIPPED)
    |--------------------------------------------------------------------------
    */
    public function getCustomerRecords($windowId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->select('customer_records.*, windows.window_name, windows.window_number')
            ->join('windows', 'windows.id = customer_records.window_id')
            ->whereIn('customer_records.status', ['completed', 'skipped']); // ✅ ONLY THESE

        if ($windowId) {
            $builder->where('customer_records.window_id', $windowId);
        }

        // Since created_at was removed, we'll sort by queueing_time or id
        return $builder->orderBy('customer_records.id', 'DESC')->findAll();
    }

    public function getCustomerRecordByTransactionNumber($transactionNumber)
    {
        return $this->where('transaction_number', $transactionNumber)->first();
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE TRANSACTION NUMBER
    |--------------------------------------------------------------------------
    */
    private function generateTransactionNumber($service, $ticketNumber)
    {
        $date = date('Ymd'); // Format: YYYYMMDD

        preg_match('/(\d+)$/', $ticketNumber, $matches);
        $number = isset($matches[1]) ? str_pad($matches[1], 3, '0', STR_PAD_LEFT) : '001';

        if (stripos($service, 'Birth') !== false || stripos($ticketNumber, 'BIRTH') !== false) {
            $prefix = 'BIRTH';
        } elseif (stripos($service, 'Death') !== false || stripos($ticketNumber, 'DEATH') !== false) {
            $prefix = 'DEATH';
        } elseif (stripos($service, 'Marriage') !== false || stripos($ticketNumber, 'MARRIAGE') !== false) {
            $prefix = 'MARRIAGE';
        } else {
            $prefix = 'BREQS';
        }

        return $prefix . $date . '-' . $number; // Format: BREQS20260323-002
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE RECORD (WHEN TICKET IS PRINTED)
    |--------------------------------------------------------------------------
    */
    public function createCustomerRecord($queueData)
    {
        // Extract service from ticket number if not provided
        $service = $queueData['service'] ?? '';
        if (empty($service)) {
            // Extract from ticket number (e.g., BIRTH-REGULAR-001)
            $parts = explode('-', $queueData['ticket_number']);
            if (count($parts) >= 2) {
                $service = $parts[0] . '-' . $parts[1];
            } else {
                $service = $parts[0] ?? 'BREQS';
            }
        }

        $transactionNumber = $this->generateTransactionNumber($service, $queueData['ticket_number']);

        // Use queue's created_at for queueing time (when ticket was printed)
        // Extract TIME part from DATETIME
        $queueingTime = date('H:i:s', strtotime($queueData['created_at'] ?? date('Y-m-d H:i:s')));

        $data = [
            'transaction_number' => $transactionNumber,
            'window_id' => $queueData['window_id'],
            'window_name' => $queueData['window_name'],
            'customer_name' => $queueData['customer_name'],
            'document_name' => $queueData['document_name'],
            'service' => $service, // ✅ EXTRACTED FROM TICKET
            'remarks' => $queueData['remarks'],
            'status' => 'serving',

            // ✅ TIME ONLY (when ticket was printed)
            'queueing_time' => $queueingTime,
        ];

        return $this->insert($data);
    }

    /*
    |--------------------------------------------------------------------------
    | START SERVICE (WHEN PREVIOUS IS DONE → NEXT STARTS)
    |--------------------------------------------------------------------------
    */
    public function startService($transactionNumber)
    {
        $record = $this->getCustomerRecordByTransactionNumber($transactionNumber);

        if (!$record) return false;

        // Store TIME only (when callNext clicked)
        $startTime = date('H:i:s');

        // For waiting time calculation, we need to create full datetime
        // Handle invalid queueing_time format
        $queueingTime = $record['queueing_time'];
        if (!$queueingTime || strpos($queueingTime, '0000-00-00') !== false) {
            log_message('error', 'Invalid queueing_time in startService: ' . $queueingTime);
            $waitingTime = '0 hours 0 minutes';
        } else {
            // Extract time part if it's a full datetime
            if (strlen($queueingTime) > 8) {
                $queueingTime = date('H:i:s', strtotime($queueingTime));
            }
            
            $queueingDateTime = date('Y-m-d') . ' ' . $queueingTime;
            $startDateTime = date('Y-m-d') . ' ' . $startTime;
            $waitingTime = $this->calculateWaitingTime($queueingDateTime, $startDateTime);
        }

        return $this->update($record['id'], [
            'start_time' => $startTime,
            'waiting_time' => $waitingTime
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLETE OR SKIP SERVICE
    |--------------------------------------------------------------------------
    */
    public function updateCustomerStatus($transactionNumber, $status)
    {
        log_message('info', 'updateCustomerStatus called: ' . $transactionNumber . ' -> ' . $status);
        
        $record = $this->getCustomerRecordByTransactionNumber($transactionNumber);

        if (!$record) {
            log_message('error', 'No record found for transaction number: ' . $transactionNumber);
            return false;
        }

        log_message('info', 'Found record ID: ' . $record['id']);
        log_message('info', 'Record status: ' . $record['status']);
        log_message('info', 'Record start_time: ' . ($record['start_time'] ?? 'NULL'));
        log_message('info', 'Record queueing_time: ' . ($record['queueing_time'] ?? 'NULL'));

        // Store TIME only (when next callNext clicked)
        $endTime = date('H:i:s');
        log_message('info', 'Setting end time to: ' . $endTime);

        $servingTime = '0 hours 0 minutes'; // Default
        if ($record['start_time'] && strpos($record['start_time'], '0000-00-00') === false) {
            // For serving time calculation, create full datetime
            $startDateTime = date('Y-m-d') . ' ' . $record['start_time'];
            $endDateTime = date('Y-m-d') . ' ' . $endTime;
            $servingTime = $this->calculateServiceTime($startDateTime, $endDateTime);
        }

        $updateData = [
            'status' => $status,
            'end_time' => $endTime,
            'serving_time' => $servingTime
        ];

        // Calculate waiting time if not already set and times are valid
        if ($record['queueing_time'] && $record['start_time'] && empty($record['waiting_time'])) {
            if (strpos($record['queueing_time'], '0000-00-00') === false && strpos($record['start_time'], '0000-00-00') === false) {
                $queueingDateTime = date('Y-m-d') . ' ' . $record['queueing_time'];
                $startDateTime = date('Y-m-d') . ' ' . $record['start_time'];
                $updateData['waiting_time'] = $this->calculateWaitingTime($queueingDateTime, $startDateTime);
            }
        }

        log_message('info', 'Update data prepared: ' . json_encode($updateData));
        log_message('info', 'Update data count: ' . count($updateData));

        // Always ensure we have the minimum required data
        if (count($updateData) < 3) {
            log_message('error', 'Insufficient data to update for transaction: ' . $transactionNumber);
            return false;
        }

        try {
            $result = $this->where('transaction_number', $transactionNumber)
                ->update($updateData);

            log_message('info', 'Update result: ' . ($result ? 'success' : 'failed'));
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Exception during update: ' . $e->getMessage());
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATIONS
    |--------------------------------------------------------------------------
    */
    private function calculateWaitingTime($queueingTime, $startTime)
    {
        try {
            if (!$queueingTime || !$startTime) {
                return '0 hours 0 minutes';
            }
            
            // Handle invalid time formats like "2026-03-24 0000-00-00 00:00:00"
            if (strpos($queueingTime, '0000-00-00') !== false) {
                log_message('error', 'Invalid queueing_time format: ' . $queueingTime);
                return '0 hours 0 minutes';
            }
            
            if (strpos($startTime, '0000-00-00') !== false) {
                log_message('error', 'Invalid start_time format: ' . $startTime);
                return '0 hours 0 minutes';
            }
            
            // Extract time part if it's a full datetime
            if (strlen($queueingTime) > 8) {
                $queueingTime = date('H:i:s', strtotime($queueingTime));
            }
            if (strlen($startTime) > 8) {
                $startTime = date('H:i:s', strtotime($startTime));
            }
            
            // Create datetime objects with current date
            $today = date('Y-m-d');
            $queue = new \DateTime($today . ' ' . $queueingTime);
            $start = new \DateTime($today . ' ' . $startTime);
            $diff = $queue->diff($start);

            if ($diff->h == 0 && $diff->i == 0) {
                return 'Less than 1 minute';
            }
            
            return $diff->h . ' hour(s) ' . $diff->i . ' minute(s)';
        } catch (\Exception $e) {
            log_message('error', 'Error calculating waiting time: ' . $e->getMessage());
            log_message('error', 'Queueing time: ' . $queueingTime . ', Start time: ' . $startTime);
            return '0 hours 0 minutes';
        }
    }

    private function calculateServiceTime($startTime, $endTime)
    {
        try {
            if (!$startTime || !$endTime) {
                return '0 hours 0 minutes';
            }
            
            // Handle invalid time formats like "2026-03-24 0000-00-00 00:00:00"
            if (strpos($startTime, '0000-00-00') !== false) {
                log_message('error', 'Invalid start_time format: ' . $startTime);
                return '0 hours 0 minutes';
            }
            
            if (strpos($endTime, '0000-00-00') !== false) {
                log_message('error', 'Invalid end_time format: ' . $endTime);
                return '0 hours 0 minutes';
            }
            
            // Extract time part if it's a full datetime
            if (strlen($startTime) > 8) {
                $startTime = date('H:i:s', strtotime($startTime));
            }
            if (strlen($endTime) > 8) {
                $endTime = date('H:i:s', strtotime($endTime));
            }
            
            // Create datetime objects with current date
            $today = date('Y-m-d');
            $start = new \DateTime($today . ' ' . $startTime);
            $end = new \DateTime($today . ' ' . $endTime);
            $diff = $start->diff($end);

            if ($diff->h == 0 && $diff->i == 0) {
                return 'Less than 1 minute';
            }
            
            return $diff->h . ' hour(s) ' . $diff->i . ' minute(s)';
        } catch (\Exception $e) {
            log_message('error', 'Error calculating service time: ' . $e->getMessage());
            log_message('error', 'Start time: ' . $startTime . ', End time: ' . $endTime);
            return '0 hours 0 minutes';
        }
    }
}