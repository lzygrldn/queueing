<?php
// Fix transaction numbers to include time
require_once 'system/bootstrap.php';

$db = \Config\Database::connect();

// Get all records that need fixing
$records = $db->query("SELECT id, ticket_number, created_at FROM customer_records WHERE transaction_number NOT LIKE '%-%'")->getResult();

echo "Fixing transaction numbers...\n";

foreach ($records as $record) {
    // Generate proper transaction number with time
    $dateTime = new \DateTime($record->created_at);
    $date = $dateTime->format('Ymd');
    $time = $dateTime->format('Hi');
    $newTransactionNumber = 'TRX-' . $date . '-' . $time;
    
    // Update the record
    $db->query("UPDATE customer_records SET transaction_number = ? WHERE id = ?", [$newTransactionNumber, $record->id]);
    
    echo "Updated record {$record->id}: {$record->transaction_number} -> {$newTransactionNumber}\n";
}

echo "Done!\n";
?>
