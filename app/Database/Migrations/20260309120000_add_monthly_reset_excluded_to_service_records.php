<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMonthlyResetExcludedToServiceRecords extends Migration
{
    public function up()
    {
        $this->forge->addColumn('service_records', [
            'monthly_reset_excluded' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'daily_reset_excluded'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('service_records', 'monthly_reset_excluded');
    }
}
