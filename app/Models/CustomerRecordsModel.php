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
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // GET RECORDS
    public function getCustomerRecords($windowId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->select('customer_records.*, windows.window_name, windows.window_number')
            ->join('windows', 'windows.id = customer_records.window_id');

        if ($windowId) {
            $builder->where('customer_records.window_id', $windowId);
        }

        if ($startDate && $endDate) {
            $builder->where('DATE(customer_records.created_at) >=', $startDate)
                    ->where('DATE(customer_records.created_at) <=', $endDate);
        }

        return $builder->orderBy('customer_records.id', 'DESC')->findAll();
    }

    public function getCustomerRecordByTransactionNumber($transactionNumber)
    {
        return $this->where('transaction_number', $transactionNumber)->first();
    }

    // GENERATE TRANSACTION NUMBER
    private function generateTransactionNumber($service, $queueNumber)
    {
        $date = date('Ymd'); // Format: YYYYMMDD

        $number = str_pad($queueNumber, 3, '0', STR_PAD_LEFT);

        if (stripos($service, 'Birth') !== false || stripos($service, 'BIRTH') !== false) {
            $prefix = 'BIRTH';
        } elseif (stripos($service, 'Death') !== false || stripos($service, 'DEATH') !== false) {
            $prefix = 'DEATH';
        } elseif (stripos($service, 'Marriage') !== false || stripos($service, 'MARRIAGE') !== false) {
            $prefix = 'MARRIAGE';
        } else {
            $prefix = 'BREQS';
        }

        return $prefix . $date . '-' . $number; // Format: BREQS20260323-002
    }

    // CREATE RECORD
    public function createCustomerRecord($data)
    {
        // Extract service from data or default to BREQS
        $service = $data['service'] ?? 'BREQS';
        
        // Generate transaction number using queue_number
        $queueNumber = $data['queue_number'] ?? 1;
        $transactionNumber = $this->generateTransactionNumber($service, $queueNumber);

        $insertData = [
            'transaction_number' => $transactionNumber,
            'window_id' => $data['window_id'],
            'window_name' => $data['window_name'],
            'customer_name' => $data['customer_name'] ?? '',
            'document_name' => $data['document_name'] ?? '',
            'service' => $service,
            'remarks' => $data['remarks'] ?? '',
        ];

        return $this->insert($insertData);
    }

    // UPDATE RECORD
    public function updateCustomerRecord($transactionNumber, $data)
    {
        $record = $this->getCustomerRecordByTransactionNumber($transactionNumber);
        
        if (!$record) {
            return false;
        }

        return $this->update($record['id'], $data);
    }
}