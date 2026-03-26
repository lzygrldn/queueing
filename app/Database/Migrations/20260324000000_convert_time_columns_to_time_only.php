<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConvertTimeColumnsToTimeOnly extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // First, convert existing DATETIME values to TIME-only values
        $sql = "UPDATE customer_records SET 
                queueing_time = TIME(queueing_time),
                start_time = TIME(start_time),
                end_time = TIME(end_time)
                WHERE queueing_time IS NOT NULL OR start_time IS NOT NULL OR end_time IS NOT NULL";
        
        $db->query($sql);
        
        echo "Converted existing DATETIME values to TIME-only format<br>";
        
        // Update waiting_time and serving_time to proper format if they're in TIME format
        $sql = "UPDATE customer_records SET 
                waiting_time = CASE 
                    WHEN waiting_time LIKE '%hours%' THEN waiting_time
                    WHEN waiting_time IS NOT NULL THEN CONCAT(HOUR(waiting_time), ' hours ', MINUTE(waiting_time), ' minutes')
                    ELSE waiting_time
                END,
                serving_time = CASE 
                    WHEN serving_time LIKE '%hours%' THEN serving_time
                    WHEN serving_time IS NOT NULL THEN CONCAT(HOUR(serving_time), ' hours ', MINUTE(serving_time), ' minutes')
                    ELSE serving_time
                END";
        
        $db->query($sql);
        
        echo "Updated waiting_time and serving_time to human-readable format<br>";
    }

    public function down()
    {
        // This migration is one-way, we don't need to revert it
    }
}
