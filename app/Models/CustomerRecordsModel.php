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

    public function createCustomerRecord($data)
    {
        // Calculate time metrics if queue item exists
        if (isset($data['queue_id']) && $data['queue_id']) {
            $queueModel = new \App\Models\QueueModel();
            $queueItem = $queueModel->find($data['queue_id']);
            
            if ($queueItem) {
                $data['queueing_time'] = $queueItem['created_at'];
                $data['start_time'] = $queueItem['served_at'];
                
                // Calculate waiting time (start_time - queueing_time)
                if ($queueItem['served_at'] && $queueItem['created_at']) {
                    $waitingSeconds = strtotime($queueItem['served_at']) - strtotime($queueItem['created_at']);
                    $data['waiting_time'] = gmdate('H:i:s', $waitingSeconds);
                }
                
                // Calculate serving time if completed
                if ($queueItem['completed_at'] && $queueItem['served_at']) {
                    $servingSeconds = strtotime($queueItem['completed_at']) - strtotime($queueItem['served_at']);
                    $data['serving_time'] = gmdate('H:i:s', $servingSeconds);
                    $data['end_time'] = $queueItem['completed_at'];
                }
            }
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function updateCustomerStatus($transactionNumber, $status, $endTime = null)
    {
        $updateData = ['status' => $status];
        
        if ($endTime) {
            $updateData['end_time'] = $endTime;
            
            // Calculate serving time
            $record = $this->getCustomerRecordByTransactionNumber($transactionNumber);
            if ($record && $record['start_time']) {
                $servingSeconds = strtotime($endTime) - strtotime($record['start_time']);
                $updateData['serving_time'] = gmdate('H:i:s', $servingSeconds);
            }
        }
        
        return $this->where('transaction_number', $transactionNumber)
                    ->update($updateData);
    }
}
