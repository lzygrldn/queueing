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

        if ($startDate && $endDate) {
            $builder->where('customer_records.created_at >=', $startDate . ' 00:00:00');
            $builder->where('customer_records.created_at <=', $endDate . ' 23:59:59');
        }

        return $builder->orderBy('customer_records.created_at', 'DESC')->findAll();
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
        $queueingTime = $queueData['created_at'] ?? date('Y-m-d H:i:s');

        $data = [
            'transaction_number' => $transactionNumber,
            'window_id' => $queueData['window_id'],
            'window_name' => $queueData['window_name'],
            'customer_name' => $queueData['customer_name'],
            'document_name' => $queueData['document_name'],
            'service' => $service, // ✅ EXTRACTED FROM TICKET
            'remarks' => $queueData['remarks'],
            'status' => 'serving',

            // ✅ TIME WHEN TICKET WAS PRINTED (from queue created_at)
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

        $startTime = date('Y-m-d H:i:s');

        $waitingTime = $this->calculateWaitingTime(
            $record['queueing_time'],
            $startTime
        );

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

        log_message('info', 'Found record: ' . json_encode([
            'id' => $record['id'],
            'status' => $record['status'],
            'start_time' => $record['start_time'],
            'queueing_time' => $record['queueing_time']
        ]));

        $endTime = date('Y-m-d H:i:s');
        log_message('info', 'Setting end time to: ' . $endTime);

        $servingTime = $this->calculateServiceTime(
            $record['start_time'],
            $endTime
        );

        $result = $this->where('transaction_number', $transactionNumber)
            ->update([
                'status' => $status, // completed OR skipped
                'end_time' => $endTime,
                'serving_time' => $servingTime
            ]);

        log_message('info', 'Update result: ' . ($result ? 'success' : 'failed'));
        
        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATIONS
    |--------------------------------------------------------------------------
    */
    private function calculateWaitingTime($queueingTime, $startTime)
    {
        $queue = new \DateTime($queueingTime);
        $start = new \DateTime($startTime);
        $diff = $queue->diff($start);

        return $diff->h . ' hour(s) ' . $diff->i . ' minute(s)';
    }

    private function calculateServiceTime($startTime, $endTime)
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $diff = $start->diff($end);

        return $diff->h . ' hour(s) ' . $diff->i . ' minute(s)';
    }
}