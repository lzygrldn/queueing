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
        $this->forge->addKey('transaction_number', false, true);
        $this->forge->addForeignKey('window_id', 'windows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customer_records');
    }

    public function down()
    {
        $this->forge->dropTable('customer_records');
    }
}
