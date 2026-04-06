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
    public function getCustomerRecords($windowId = null, $startDate = null, $endDate = null, $search = null)
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

        // Search across all columns
        if ($search) {
            $builder->groupStart()
                ->like('customer_records.transaction_number', $search)
                ->orLike('customer_records.customer_name', $search)
                ->orLike('customer_records.document_name', $search)
                ->orLike('customer_records.service', $search)
                ->orLike('customer_records.remarks', $search)
                ->orLike('windows.window_name', $search)
                ->orLike('windows.window_number', $search)
                ->groupEnd();
        }

        return $builder->orderBy('customer_records.id', 'DESC')->findAll();
    }

    public function getCustomerRecordByTransactionNumber($transactionNumber)
    {
        return $this->where('transaction_number', $transactionNumber)->first();
    }

    // GENERATE TRANSACTION NUMBER
    private function generateTransactionNumber($service, $queueNumber, $windowId = null)
    {
        $date = date('Ymd'); // Format: YYYYMMDD

        $number = str_pad($queueNumber, 3, '0', STR_PAD_LEFT);

        // First check window_id for correct prefix
        if ($windowId) {
            if ($windowId == 1) {
                $prefix = 'BREQS';
            } elseif ($windowId == 2) {
                $prefix = 'BIRTH';
            } elseif ($windowId == 3) {
                $prefix = 'DEATH';
            } elseif ($windowId == 4) {
                $prefix = 'MARRIAGE';
            } else {
                // Fallback to service-based detection
                $prefix = $this->getPrefixFromService($service);
            }
        } else {
            // Fallback to service-based detection
            $prefix = $this->getPrefixFromService($service);
        }

        return $prefix . $date . '-' . $number; // Format: BIRTH20260406-002
    }

    private function getPrefixFromService($service)
    {
        if (stripos($service, 'Birth') !== false || stripos($service, 'BIRTH') !== false) {
            return 'BIRTH';
        } elseif (stripos($service, 'Death') !== false || stripos($service, 'DEATH') !== false) {
            return 'DEATH';
        } elseif (stripos($service, 'Marriage') !== false || stripos($service, 'MARRIAGE') !== false) {
            return 'MARRIAGE';
        } else {
            return 'BREQS';
        }
    }

    // CREATE RECORD
    public function createCustomerRecord($data)
    {
        // Extract service from data or default to BREQS
        $service = $data['service'] ?? 'BREQS';
        $windowId = $data['window_id'] ?? null;
        
        // Generate transaction number using queue_number and window_id
        $queueNumber = $data['queue_number'] ?? 1;
        $transactionNumber = $this->generateTransactionNumber($service, $queueNumber, $windowId);

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