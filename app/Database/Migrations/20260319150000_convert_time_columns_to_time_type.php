<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConvertTimeColumnsToTimeType extends Migration
{
    public function up()
    {
        // Convert time columns from DATETIME to TIME type
        $this->forge->modifyColumn('customer_records', [
            'queueing_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'start_time' => [
                'type' => 'TIME', 
                'null' => true,
            ],
            'end_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Revert back to DATETIME if needed
        $this->forge->modifyColumn('customer_records', [
            'queueing_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'start_time' => [
                'type' => 'DATETIME', 
                'null' => true,
            ],
            'end_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }
}
