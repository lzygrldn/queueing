<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table = 'queues';
    protected $primaryKey = 'id';
    protected $allowedFields = ['window_id', 'ticket_number', 'queue_number', 'status', 'created_at', 'served_at', 'completed_at', 'service_type'];
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
            // First, get all queues with their window_id, excluding waiting status
            $builder = $this->db->table('queues');
            $builder->select('id, ticket_number, window_id, status, created_at, completed_at');
            $builder->where('status', 'serving');
            $builder->orWhere('status', 'completed');
            $builder->orWhere('status', 'skipped');
            $builder->orderBy('id', 'DESC');
            
            // Log the exact SQL query
            $sql = $builder->getCompiledSelect();
            error_log("SQL Query: " . $sql);
            
            $queues = $builder->get()->getResultArray();
            
            error_log("Queues found (excluding waiting): " . count($queues));
            
            if (empty($queues)) {
                return [];
            }
            
            // Debug: Log all statuses found
            $statusCounts = [];
            foreach ($queues as $queue) {
                $status = $queue['status'];
                if (!isset($statusCounts[$status])) {
                    $statusCounts[$status] = 0;
                }
                $statusCounts[$status]++;
            }
            error_log("Status distribution: " . json_encode($statusCounts));
            
            // Debug: Log individual queue data
            foreach ($queues as $queue) {
                error_log("Queue: " . json_encode($queue));
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
        error_log("markAsServing called for id: $id");
        $result = $this->update($id, [
            'status' => 'serving',
            'served_at' => date('Y-m-d H:i:s')
        ]);
        error_log("markAsServing result: $result");
        return $result;
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
        $result = $this->where('window_id', $windowId)
                    ->where('status', 'waiting')
                    ->orderBy('queue_number', 'ASC')
                    ->first();
        
        error_log("getNextInQueue for windowId $windowId: " . ($result ? json_encode($result) : 'null'));
        return $result;
    }

    public function getQueueDataTable()
    {
        return $this->select('queues.*, windows.window_name, windows.prefix')
                    ->join('windows', 'windows.id = queues.window_id')
                    ->where('queues.status', 'serving')
                    ->orWhere('queues.status', 'completed')
                    ->orWhere('queues.status', 'skipped')
                    ->orderBy('queues.created_at', 'DESC')
                    ->findAll();
    }

    public function getSkippedByWindow($windowId)
    {
        return $this->where('window_id', $windowId)
                    ->where('status', 'skipped')
                    ->orderBy('completed_at', 'DESC')
                    ->findAll();
    }

    public function getCompletedByWindow($windowId)
    {
        return $this->where('window_id', $windowId)
                    ->where('status', 'completed')
                    ->orderBy('completed_at', 'DESC')
                    ->findAll();
    }

    public function markAsWaiting($id)
    {
        return $this->update($id, [
            'status' => 'waiting',
            'served_at' => null,
            'completed_at' => null
        ]);
    }
}
