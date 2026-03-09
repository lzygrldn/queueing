<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceRecordModel extends Model
{
    protected $table = 'service_records';
    protected $primaryKey = 'id';
    protected $allowedFields = ['window_id', 'ticket_number', 'service_date', 'service_type', 'created_at'];
    protected $useTimestamps = false;

    public function addRecord($data)
    {
        return $this->insert($data);
    }

    public function getDailyReport($date)
    {
        return $this->select('service_records.*, windows.window_name, windows.prefix')
                    ->join('windows', 'windows.id = service_records.window_id')
                    ->where('service_date', $date)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getMonthlyReport($year, $month)
    {
        return $this->select('service_records.*, windows.window_name, windows.prefix')
                    ->join('windows', 'windows.id = service_records.window_id')
                    ->where('MONTH(service_date)', $month)
                    ->where('YEAR(service_date)', $year)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getDailyStats($date)
    {
        return $this->select('windows.window_name, windows.prefix, 
                              COUNT(CASE WHEN service_type = "completed" THEN 1 END) as completed,
                              COUNT(CASE WHEN service_type = "skipped" THEN 1 END) as skipped,
                              COUNT(*) as total')
                    ->join('windows', 'windows.id = service_records.window_id')
                    ->where('service_date', $date)
                    ->groupBy('window_id')
                    ->findAll();
    }

    public function getMonthlyStats($year, $month)
    {
        return $this->select('windows.window_name, windows.prefix,
                              COUNT(CASE WHEN service_type = "completed" THEN 1 END) as completed,
                              COUNT(CASE WHEN service_type = "skipped" THEN 1 END) as skipped,
                              COUNT(*) as total')
                    ->join('windows', 'windows.id = service_records.window_id')
                    ->where('MONTH(service_date)', $month)
                    ->where('YEAR(service_date)', $year)
                    ->groupBy('window_id')
                    ->findAll();
    }

    public function clearAllRecords()
    {
        return $this->truncate();
    }
}
