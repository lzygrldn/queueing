<?php

namespace App\Controllers;

use App\Models\WindowModel;
use App\Models\QueueModel;

class Display extends BaseController
{
    protected $windowModel;
    protected $queueModel;

    public function __construct()
    {
        $this->windowModel = new WindowModel();
        $this->queueModel = new QueueModel();
        date_default_timezone_set('Asia/Manila');
    }

    public function index()
    {
        $data['windows'] = $this->getWindowData();
        return view('display/index', $data);
    }

    public function getData()
    {
        return $this->response->setJSON([
            'success' => true,
            'windows' => $this->getWindowData(),
            'current_time' => date('h:i:s A'),
            'current_date' => date('F d, Y')
        ]);
    }

    private function getWindowData()
    {
        $windows = $this->windowModel->getAllWindows();
        $data = [];

        foreach ($windows as $window) {
            $serving = $this->queueModel->getServingByWindow($window['id']);
            $waiting = $this->queueModel->getWaitingByWindow($window['id']);

            $data[] = [
                'window_number' => $window['window_number'],
                'window_name' => $window['window_name'],
                'prefix' => $window['prefix'],
                'now_serving' => $serving ? $serving['ticket_number'] : 'None',
                'waiting_list' => $waiting
            ];
        }

        return $data;
    }
}
