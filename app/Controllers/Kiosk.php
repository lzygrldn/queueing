<?php

namespace App\Controllers;

use App\Models\WindowModel;
use App\Models\QueueModel;

class Kiosk extends BaseController
{
    protected $windowModel;
    protected $queueModel;

    public function __construct()
    {
        $this->windowModel = new WindowModel();
        $this->queueModel = new QueueModel();
    }

    public function index()
    {
        return view('kiosk/index');
    }

    public function printTicket()
    {
        $service = $this->request->getPost('service');
        
        // Map service to window
        $serviceMap = [
            'psa' => 1,
            'birth' => 2,
            'death' => 3,
            'marriage' => 4
        ];
        
        $windowId = $serviceMap[$service] ?? 1;
        
        // Set timezone first
        date_default_timezone_set('Asia/Manila');
        
        // Get next number
        $window = $this->windowModel->find($windowId);
        $nextNumber = ($window['last_released'] ?? 0) + 1;
        $ticketNumber = $window['prefix'] . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Add to queue
        $queueData = [
            'window_id' => $windowId,
            'ticket_number' => $ticketNumber,
            'queue_number' => $nextNumber,
            'status' => 'waiting',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->queueModel->addToQueue($queueData);

        // Update last released
        $this->windowModel->updateLastReleased($windowId, $nextNumber);

        // If no one is being served, serve this one
        $serving = $this->queueModel->getServingByWindow($windowId);
        if (!$serving) {
            $this->queueModel->markAsServing($this->queueModel->getInsertID());
            $this->windowModel->updateCurrentNumber($windowId, $nextNumber);
        }

        // Format date and time
        $dateTimeStamp = date('M. d, Y h:i A');

        return $this->response->setJSON([
            'success' => true,
            'ticket' => [
                'number' => $ticketNumber,
                'datetime' => $dateTimeStamp
            ]
        ]);
    }
}
