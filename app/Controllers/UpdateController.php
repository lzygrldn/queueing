<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UpdateController extends BaseController
{
    public function index()
    {
        //
    }
    
    public function updatePsaToBreqs()
    {
        try {
            $db = \Config\Database::connect();
            
            // Update windows table
            $db->query("UPDATE windows SET window_name = 'BREQS', prefix = 'BREQS' WHERE window_number = 1");
            
            // Update any existing queue records with PSA prefix
            $db->query("UPDATE queues SET ticket_number = REPLACE(ticket_number, 'PSA-', 'BREQS-') WHERE ticket_number LIKE 'PSA-%'");
            
            echo "Database updated successfully!\n";
            echo "PSA has been changed to BREQS in windows table\n";
            echo "Existing PSA tickets have been updated to BREQS format\n";
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
