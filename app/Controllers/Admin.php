<?php

namespace App\Controllers;

use App\Models\WindowModel;
use App\Models\QueueModel;
use App\Models\ServiceRecordModel;

class Admin extends BaseController
{
    protected $windowModel;
    protected $queueModel;
    protected $serviceRecordModel;

    public function __construct()
    {
        $this->windowModel = new WindowModel();
        $this->queueModel = new QueueModel();
        $this->serviceRecordModel = new ServiceRecordModel();
    }

    public function index()
    {
        // Check if admin is logged in
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('index.php/');
        }

        $data['windows'] = $this->getWindowData();
        $data['queue_data'] = $this->queueModel->getQueueDataTable();
        
        // Daily stats
        $today = date('Y-m-d');
        $data['daily_stats'] = $this->serviceRecordModel->getDailyStats($today);
        
        // Monthly stats
        $year = date('Y');
        $month = date('m');
        $data['monthly_stats'] = $this->serviceRecordModel->getMonthlyStats($year, $month);

        return view('admin/dashboard', $data);
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if ($username === 'admin' && $password === 'admin123') {
            session()->set('admin_logged_in', true);
            return redirect()->to('index.php/admin');
        }

        return redirect()->to('index.php/')->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('index.php/');
    }

    public function kiosk()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('index.php/');
        }
        
        $data['from_admin'] = true;
        return view('kiosk/index', $data);
    }

    public function display()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('index.php/');
        }
        
        $data['windows'] = $this->getWindowData();
        $data['from_admin'] = true;
        return view('display/index', $data);
    }

    public function completeQueue($queueId)
    {
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $queue = $this->queueModel->find($queueId);
        if ($queue) {
            $this->queueModel->markAsCompleted($queueId);
            
            // Add to service records
            $recordData = [
                'window_id' => $queue['window_id'],
                'ticket_number' => $queue['ticket_number'],
                'service_date' => date('Y-m-d'),
                'service_type' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'daily_reset_excluded' => 0, // Include in daily stats
                'monthly_reset_excluded' => 0 // Include in monthly stats
            ];
            
            error_log("Complete queue - Adding service record: " . json_encode($recordData));
            $result = $this->serviceRecordModel->addRecord($recordData);
            error_log("Service record result: " . $result);

            // Serve next in queue
            $this->serveNext($queue['window_id']);

            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Queue not found']);
    }

    public function skipQueue($queueId)
    {
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $queue = $this->queueModel->find($queueId);
        if ($queue) {
            // Mark queue as skipped
            $this->queueModel->markAsSkipped($queueId);

            // Record service record for statistics
            $this->serviceRecordModel->addRecord([
                'window_id' => $queue['window_id'],
                'ticket_number' => $queue['ticket_number'],
                'service_date' => date('Y-m-d'),
                'service_type' => 'skipped',
                'created_at' => date('Y-m-d H:i:s'),
                'daily_reset_excluded' => 0, // Include in daily stats
                'monthly_reset_excluded' => 0 // Include in monthly stats
            ]);

            // Serve next in queue
            $this->serveNext($queue['window_id']);

            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Queue not found']);
    }

    public function resetWindows()
    {
        error_log("resetWindows method called");
        
        if (!session()->get('admin_logged_in')) {
            error_log("User not logged in for resetWindows");
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            // Reset all windows current serving numbers to 0
            $result1 = $this->windowModel->where('id >', 0)->set(['current_number' => 0])->update();
            error_log("Current numbers reset result: " . $result1);
            
            // Reset all windows last released numbers to 0
            $result2 = $this->windowModel->where('id >', 0)->set(['last_released' => 0])->update();
            error_log("Released numbers reset result: " . $result2);
            
            // Clear ALL queue records (waiting, serving, completed, skipped)
            $result3 = $this->queueModel->truncate();
            error_log("Queue truncate result: " . $result3);
            
            error_log("resetWindows completed successfully");
            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            error_log("resetWindows error: " . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getQueueData()
    {
        // Debug: Log that method was called
        error_log("getQueueData method called");
        
        if (!session()->get('admin_logged_in')) {
            error_log("User not logged in");
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $queues = $this->queueModel->getAllQueues();
        error_log("Queues found: " . count($queues));
        
        // Debug: Log ALL queue data to identify duplication
        error_log("All queue data: " . json_encode($queues));
        
        // Check for duplicates
        $ticketNumbers = [];
        foreach ($queues as $index => $queue) {
            $ticketNumber = $queue['ticket_number'] ?? 'N/A';
            if (isset($ticketNumbers[$ticketNumber])) {
                error_log("DUPLICATE FOUND: '$ticketNumber' at index $ticketNumbers[$ticketNumber] and $index");
            } else {
                $ticketNumbers[$ticketNumber] = $index;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $queues
        ]);
    }

    public function resetNumbers()
    {
        error_log("resetNumbers method called");
        
        if (!session()->get('admin_logged_in')) {
            error_log("User not logged in for resetNumbers");
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            // Reset last released numbers back to 0 for all windows
            $result1 = $this->windowModel->where('id >', 0)->set(['last_released' => 0])->update();
            error_log("Released numbers reset result: " . $result1);
            
            // Also reset current serving numbers to 0
            $result2 = $this->windowModel->where('id >', 0)->set(['current_number' => 0])->update();
            error_log("Current numbers reset result: " . $result2);

            error_log("resetNumbers completed successfully");
            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            error_log("resetNumbers error: " . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function resetDailyStats()
    {
        error_log("resetDailyStats method called");
        
        if (!session()->get('admin_logged_in')) {
            error_log("User not logged in for resetDailyStats");
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            // Mark today's service records as excluded from daily stats
            // but keep them for monthly statistics
            $today = date('Y-m-d');
            $db = \Config\Database::connect();
            $result = $db->table('service_records')
                ->where('service_date', $today)
                ->update(['daily_reset_excluded' => 1]);
            
            error_log("Daily stats reset result: " . $result);
            error_log("resetDailyStats completed successfully");
            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            error_log("resetDailyStats error: " . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function resetMonthlyStats()
    {
        error_log("resetMonthlyStats method called");
        
        if (!session()->get('admin_logged_in')) {
            error_log("User not logged in for resetMonthlyStats");
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            // Mark current month's service records as excluded from monthly stats
            // but keep them for daily statistics
            $year = date('Y');
            $month = date('m');
            $db = \Config\Database::connect();
            $result = $db->table('service_records')
                ->where('MONTH(service_date)', $month)
                ->where('YEAR(service_date)', $year)
                ->update(['monthly_reset_excluded' => 1]);
            
            error_log("Monthly stats reset result: " . $result);
            error_log("resetMonthlyStats completed successfully");
            return $this->response->setJSON(['success' => true]);
        } catch (Exception $e) {
            error_log("resetMonthlyStats error: " . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getWindowData()
    {
        $windows = $this->windowModel->getAllWindows();
        $data = [];

        foreach ($windows as $window) {
            $serving = $this->queueModel->getServingByWindow($window['id']);
            $waiting = $this->queueModel->getWaitingByWindow($window['id']);

            $data[] = [
                'id' => $window['id'],
                'window_number' => $window['window_number'],
                'window_name' => $window['window_name'],
                'prefix' => $window['prefix'],
                'current_number' => $window['current_number'],
                'last_released' => $window['last_released'],
                'now_serving' => $serving ? $serving['ticket_number'] : 'None',
                'serving_queue_id' => $serving ? $serving['id'] : null,
                'waiting_count' => count($waiting),
                'waiting_list' => $waiting
            ];
        }

        return $data;
    }

    private function serveNext($windowId)
    {
        $next = $this->queueModel->getNextInQueue($windowId);
        if ($next) {
            $this->queueModel->markAsServing($next['id']);
            $this->windowModel->updateCurrentNumber($windowId, $next['queue_number']);
        } else {
            $this->windowModel->updateCurrentNumber($windowId, 0);
        }
    }

    public function getData()
    {
        if (!session()->get('admin_logged_in')) {
            return $this->response->setJSON(['success' => false]);
        }

        $windows = $this->getWindowData();
        $today = date('Y-m-d');
        $year = date('Y');
        $month = date('m');

        $dailyStats = $this->serviceRecordModel->getDailyStats($today);
        $monthlyStats = $this->serviceRecordModel->getMonthlyStats($year, $month);
        
        // Calculate totals to verify
        $monthlyCompleted = 0;
        $monthlySkipped = 0;
        foreach ($monthlyStats as $stat) {
            $monthlyCompleted += $stat['completed'];
            $monthlySkipped += $stat['skipped'];
        }
        
        error_log("getData - Today: " . $today);
        error_log("getData - Daily stats: " . json_encode($dailyStats));
        error_log("getData - Monthly stats raw: " . json_encode($monthlyStats));
        error_log("getData - Monthly calculated totals - Completed: " . $monthlyCompleted . ", Skipped: " . $monthlySkipped);

        return $this->response->setJSON([
            'success' => true,
            'windows' => $windows,
            'daily_stats' => $dailyStats,
            'monthly_stats' => $monthlyStats,
            'queue_data' => $this->queueModel->getQueueDataTable()
        ]);
    }
}
