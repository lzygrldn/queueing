<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceRecordModel extends Model
{
    protected $table = 'service_records';
    protected $primaryKey = 'id';
    protected $allowedFields = ['window_id', 'ticket_number', 'service_date', 'service_type', 'created_at', 'daily_reset_excluded', 'monthly_reset_excluded'];
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
        // First get all windows
        $windows = $this->db->table('windows')
            ->select('id, window_name, prefix')
            ->get()
            ->getResultArray();
        
        $stats = [];
        foreach ($windows as $window) {
            // Get stats for each window (excluding daily reset records but including monthly reset records)
            $windowStats = $this->db->table('service_records')
                ->select('
                    SUM(CASE WHEN service_type = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN service_type = "skipped" THEN 1 ELSE 0 END) as skipped,
                    COUNT(*) as total
                ')
                ->where('window_id', $window['id'])
                ->where('service_date', $date)
                ->where('daily_reset_excluded', 0) // Exclude daily reset records
                // Note: NO monthly_reset_excluded filter - include monthly reset records for daily
                ->get()
                ->getRowArray();
            
            $stats[] = [
                'window_name' => $window['window_name'],
                'prefix' => $window['prefix'],
                'completed' => $windowStats['completed'] ?? 0,
                'skipped' => $windowStats['skipped'] ?? 0,
                'total' => $windowStats['total'] ?? 0
            ];
        }
        
        return $stats;
    }

    public function getMonthlyStats($year, $month)
    {
        // First get all windows
        $windows = $this->db->table('windows')
            ->select('id, window_name, prefix')
            ->get()
            ->getResultArray();
        
        $stats = [];
        foreach ($windows as $window) {
            // Get stats for each window (excluding monthly reset records)
            $windowStats = $this->db->table('service_records')
                ->select('
                    SUM(CASE WHEN service_type = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN service_type = "skipped" THEN 1 ELSE 0 END) as skipped,
                    COUNT(*) as total
                ')
                ->where('window_id', $window['id'])
                ->where('MONTH(service_date)', $month)
                ->where('YEAR(service_date)', $year)
                ->where('monthly_reset_excluded', 0) // Exclude monthly reset records
                ->get()
                ->getRowArray();
            
            $stats[] = [
                'window_name' => $window['window_name'],
                'prefix' => $window['prefix'],
                'completed' => $windowStats['completed'] ?? 0,
                'skipped' => $windowStats['skipped'] ?? 0,
                'total' => $windowStats['total'] ?? 0
            ];
        }
        
        return $stats;
    }

    public function clearAllRecords()
    {
        return $this->truncate();
    }
}
