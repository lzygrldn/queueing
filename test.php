<?php
// Test script to check transaction numbers
require_once 'system/bootstrap.php';

$db = \Config\Database::connect();
$results = $db->query("SELECT transaction_number FROM customer_records LIMIT 5")->getResult();

echo "Transaction numbers in database:\n";
foreach ($results as $row) {
    echo "- " . $row->transaction_number . "\n";
}
?>
