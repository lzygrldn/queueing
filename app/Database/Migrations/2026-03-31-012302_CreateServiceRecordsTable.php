<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServiceRecordsTable extends Migration
{
    // Service records table for reports
    public function up()
    {
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
            'daily_reset_excluded' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'created_at'
            ],
            'monthly_reset_excluded' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'daily_reset_excluded'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('window_id', 'windows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('service_records');
    }

    public function down()
    {
        $this->forge->dropTable('service_records');
    }
}
