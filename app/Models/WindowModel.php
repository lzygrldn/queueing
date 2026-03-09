<?php

namespace App\Models;

use CodeIgniter\Model;

class WindowModel extends Model
{
    protected $table = 'windows';
    protected $primaryKey = 'id';
    protected $allowedFields = ['window_number', 'window_name', 'prefix', 'current_number', 'last_released', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getAllWindows()
    {
        return $this->findAll();
    }

    public function getWindowByNumber($number)
    {
        return $this->where('window_number', $number)->first();
    }

    public function updateCurrentNumber($windowId, $number)
    {
        return $this->update($windowId, ['current_number' => $number]);
    }

    public function updateLastReleased($windowId, $number)
    {
        return $this->update($windowId, ['last_released' => $number]);
    }

    public function resetAllWindows()
    {
        return $this->set(['current_number' => 0, 'last_released' => 0])->update();
    }
}
