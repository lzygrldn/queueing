<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixTimeColumnsInCustomerRecords extends Migration
{
    public function up()
    {
        // Change waiting_time and serving_time to VARCHAR to store duration strings
        $this->forge->modifyColumn('customer_records', [
            'waiting_time' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'serving_time' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
        ]);
        
        log_message('info', 'Fixed time columns in customer_records table');
    }

    public function down()
    {
        // Revert back to TIME type
        $this->forge->modifyColumn('customer_records', [
            'waiting_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'serving_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
        ]);
    }
}
