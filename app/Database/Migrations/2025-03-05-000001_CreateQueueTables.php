<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueTables extends Migration
{
    public function up()
    {
        // Windows table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'window_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'window_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'current_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'last_released' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('windows');

        // Queue table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'window_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ticket_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'queue_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['waiting', 'serving', 'completed', 'skipped'],
                'default' => 'waiting',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'served_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('window_id', 'windows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('queues');

        // Service records table for reports
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'window_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ticket_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'service_date' => [
                'type' => 'DATE',
            ],
            'service_type' => [
                'type' => 'ENUM',
                'constraint' => ['completed', 'skipped'],
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('window_id', 'windows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('service_records');

        // Insert default windows
        $data = [
            [
                'window_number' => 1,
                'window_name' => 'PSA',
                'prefix' => 'PSA',
                'current_number' => 0,
                'last_released' => 0,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'window_number' => 2,
                'window_name' => 'Birth Registration',
                'prefix' => 'BIRTH',
                'current_number' => 0,
                'last_released' => 0,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'window_number' => 3,
                'window_name' => 'Death Registration',
                'prefix' => 'DEATH',
                'current_number' => 0,
                'last_released' => 0,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'window_number' => 4,
                'window_name' => 'Marriage Registration',
                'prefix' => 'MARRIAGE',
                'current_number' => 0,
                'last_released' => 0,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('windows')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('service_records');
        $this->forge->dropTable('queues');
        $this->forge->dropTable('windows');
    }
}
