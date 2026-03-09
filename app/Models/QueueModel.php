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
        $this->select('q.ticket_number, w.window_name, q.status, q.created_at, q.completed_at');
        $this->from('queues q');
        $this->join('windows w', 'w.id = q.window_id');
        $this->orderBy('q.id', 'DESC');
        return $this->findAll();
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
