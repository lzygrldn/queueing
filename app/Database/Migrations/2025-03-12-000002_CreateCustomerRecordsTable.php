<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerRecordsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'transaction_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'window_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'window_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'ticket_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'document_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'service' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['serving', 'completed', 'pending'],
                'default' => 'serving',
            ],
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
            'waiting_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'serving_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('transaction_number', false, true);
        $this->forge->addKey('window_id');
        $this->forge->addKey('ticket_number');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('window_id', 'windows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customer_records');
    }

    public function down()
    {
        $this->forge->dropTable('customer_records');
    }
}
