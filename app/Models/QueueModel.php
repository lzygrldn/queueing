<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table = 'queues';
    protected $primaryKey = 'id';
    protected $allowedFields = ['window_id', 'ticket_number', 'queue_number', 'status', 'created_at', 'served_at', 'completed_at'];
    protected $useTimestamps = false;

    public function getWaitingByWindow($windowId)
    {
        return $this->where('window_id', $windowId)
                    ->where('status', 'waiting')
                    ->orderBy('queue_number', 'ASC')
                    ->findAll();
    }

    public function getServingByWindow($windowId)
    {
        return $this->where('window_id', $windowId)
                    ->where('status', 'serving')
                    ->first();
    }

    public function getWaitingCount($windowId)
    {
        return $this->where('window_id', $windowId)
                    ->where('status', 'waiting')
                    ->countAllResults();
    }

    public function getAllQueues()
    {
        try {
            // First, get all queues with their window_id
            $builder = $this->db->table('queues');
            $builder->select('id, ticket_number, window_id, status, created_at, completed_at');
            $builder->orderBy('id', 'DESC');
            
            $queues = $builder->get()->getResultArray();
            
            error_log("Queues found: " . count($queues));
            
            if (empty($queues)) {
                return [];
            }
            
            // Get all window names separately
            $windowBuilder = $this->db->table('windows');
            $windowBuilder->select('id, window_name');
            $windows = $windowBuilder->get()->getResultArray();
            
            error_log("Windows found: " . count($windows));
            
            // Create window name lookup
            $windowLookup = [];
            foreach ($windows as $window) {
                $windowLookup[$window['id']] = $window['window_name'];
            }
            
            error_log("Window lookup: " . json_encode($windowLookup));
            
            // Combine queue data with window names
            $results = [];
            foreach ($queues as $queue) {
                $windowName = isset($windowLookup[$queue['window_id']]) 
                    ? $windowLookup[$queue['window_id']] 
                    : 'Window ' . $queue['window_id'];
                
                $results[] = [
                    'ticket_number' => $queue['ticket_number'],
                    'window_name' => $windowName,
                    'status' => $queue['status'],
                    'created_at' => $queue['created_at'],
                    'completed_at' => $queue['completed_at']
                ];
            }
            
            error_log("Final results count: " . count($results));
            if (!empty($results)) {
                error_log("First final result: " . json_encode($results[0]));
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("getAllQueues error: " . $e->getMessage());
            return [];
        }
    }

    public function addToQueue($data)
    {
        date_default_timezone_set('Asia/Manila');
        return $this->insert($data);
    }

    public function markAsServing($id)
    {
        return $this->update($id, [
            'status' => 'serving',
            'served_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsCompleted($id)
    {
        return $this->update($id, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsSkipped($id)
    {
        return $this->update($id, [
            'status' => 'skipped',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getNextInQueue($windowId)
    {
        return $this->where('window_id', $windowId)
                    ->where('status', 'waiting')
                    ->orderBy('queue_number', 'ASC')
                    ->first();
    }

    public function clearAllQueues()
    {
        return $this->where('status', 'waiting')->delete();
    }

    public function getQueueDataTable()
    {
        return $this->select('queues.*, windows.window_name, windows.prefix')
                    ->join('windows', 'windows.id = queues.window_id')
                    ->orderBy('queues.created_at', 'DESC')
                    ->findAll();
    }
}
