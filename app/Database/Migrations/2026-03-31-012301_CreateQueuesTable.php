<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueuesTable extends Migration
{
    public function up()
{
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
                ],
            'window_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
                ],
            'ticket_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20
                ],
            'queue_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
                ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['waiting', 'serving', 'completed', 'skipped'],
                'default' => 'waiting'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
                ],
            'served_at' => [
                'type' => 'DATETIME',
             'null' => true
             ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true
                ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('window_id', 'windows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('queues');
    }

    public function down()
    {
        $this->forge->dropTable('queues');
    }
}
