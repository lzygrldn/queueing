<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDailyResetExcludedToServiceRecords extends Migration
{
    public function up()
    {
        $this->forge->addColumn('service_records', [
            'daily_reset_excluded' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'service_type'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('service_records', 'daily_reset_excluded');
    }
}
