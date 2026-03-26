<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixTimeColumnsDefaultValues extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Fix any records with 0000-00-00 or invalid time values by setting them to NULL
        $sql = "UPDATE customer_records SET 
                queueing_time = NULL 
                WHERE queueing_time = '0000-00-00' OR queueing_time = '0000-00-00 00:00:00' OR queueing_time = ''";
        $db->query($sql);
        
        $sql = "UPDATE customer_records SET 
                start_time = NULL 
                WHERE start_time = '0000-00-00' OR start_time = '0000-00-00 00:00:00' OR start_time = ''";
        $db->query($sql);
        
        $sql = "UPDATE customer_records SET 
                end_time = NULL 
                WHERE end_time = '0000-00-00' OR end_time = '0000-00-00 00:00:00' OR end_time = ''";
        $db->query($sql);
        
        $sql = "UPDATE customer_records SET 
                waiting_time = NULL 
                WHERE waiting_time = '0000-00-00' OR waiting_time = ''";
        $db->query($sql);
        
        $sql = "UPDATE customer_records SET 
                serving_time = NULL 
                WHERE serving_time = '0000-00-00' OR serving_time = ''";
        $db->query($sql);
        
        log_message('info', 'Fixed time columns default values');
    }

    public function down()
    {
        // No need to revert
    }
}
