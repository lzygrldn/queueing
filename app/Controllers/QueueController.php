<?php

namespace App\Controllers;

use App\Models\WindowModel;
use App\Models\QueueModel;

class QueueController extends BaseController
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
        $data['from_admin'] = $this->request->getGet('from_admin') === 'true';
        return view('queue/index', $data);
    }

    public function printTicket()
    {
        // Set timezone FIRST before any date operations
        date_default_timezone_set('Asia/Manila');
        
        $service = $this->request->getPost('service');
        
        // Map service to window, ticket prefix, and full service type
        $serviceMap = [
            'breqs' => [
                'window' => 1,
                'prefix' => 'BREQS',
                'service_name' => 'BREQS',
                'service_type' => 'BREQS'
            ],
            'birth-regular' => [
                'window' => 2, 'prefix' => 'BIRTH',
                'service_name' => 'REGULAR',
                'service_type' => 'Birth - Regular'
            ],
            'birth-delayed' => [
                'window' => 2,
                'prefix' => 'BIRTH',
                'service_name' => 'DELAYED', 
                'service_type' => 'Birth - Delayed'
            ],
            'birth-out-of-town' => [
                'window' => 2,
                'prefix' => 'BIRTH',
                'service_name' => 'OUT OF TOWN',
                'service_type' => 'Birth - Out-of-Town'
            ],
            'death-regular' => [
                'window' => 3, 'prefix' => 'DEATH',
                'service_name' => 'REGULAR',
                'service_type' => 'Death - Regular'
            ],
            'death-delayed' => [
                'window' => 3,
                'prefix' => 'DEATH',
                'service_name' => 'DELAYED',
                'service_type' => 'Death - Delayed'
            ],
            'marriage-regular' => [
                'window' => 4,
                'prefix' => 'MARRIAGE',
                'service_name' => 'REGULAR',
                'service_type' => 'Marriage - Regular'
            ],
            'marriage-delayed' => [
                'window' => 4,
                'prefix' => 'MARRIAGE',
                'service_name' => 'DELAYED',
                'service_type' => 'Marriage - Delayed'
            ],
            'marriage-license-endorsement' => [
                'window' => 4,
                'prefix' => 'MARRIAGE',
                'service_name' => 'LICENSE ENDORSEMENT',
                'service_type' => 'Marriage - License Endorsement'
            ],
            'marriage-license-application' => [
                'window' => 4,
                'prefix' => 'MARRIAGE',
                'service_name' => 'LICENSE APPLICATION',
                'service_type' => 'Marriage - License Application'
            ]
        ];
        
        $windowId = $serviceMap[$service]['window'] ?? 1;
        $ticketPrefix = $serviceMap[$service]['prefix'] ?? 'BREQS';
        $serviceName = $serviceMap[$service]['service_name'] ?? 'BREQS';
        
        $serviceType = $serviceMap[$service]['service_type'] ?? 'BREQS';
        
        // Get next number
        $window = $this->windowModel->find($windowId);
        $nextNumber = ($window['last_released'] ?? 0) + 1;
        $ticketNumber = $ticketPrefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Add to queue
        $queueData = [
            'window_id' => $windowId,
            'ticket_number' => $ticketNumber,
            'queue_number' => $nextNumber,
            'status' => 'waiting',
            'service_type' => $serviceType,
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
                'datetime' => $dateTimeStamp,
                'window_number' => $window['window_number'],
                'service' => $serviceName,
                'service_type' => $serviceType
            ]
        ]);
    }
}
