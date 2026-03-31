<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWindowsTable extends Migration
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
        
        // Insert default windows
        $data = [
            [
                'window_number' => 1,
                'window_name' => 'BREQS',
                'prefix' => 'BREQS',
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
        $this->forge->dropTable('windows');
    }
}
