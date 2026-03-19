<?php
// Connect to database
$db = new mysqli('localhost', 'root', '', 'queueing_system');
if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

echo "=== Checking Window 1 for Marriage tickets ===\n";

// Check queues table for marriage tickets in window 1
$query = "SELECT q.ticket_number, q.window_id, w.window_name, w.window_number, q.status 
          FROM queues q 
          JOIN windows w ON q.window_id = w.id 
          WHERE w.window_number = 1 AND (q.ticket_number LIKE 'MARRIAGE%' OR q.ticket_number LIKE 'MARRIAGE%')
          ORDER BY q.created_at DESC LIMIT 10";

$result = $db->query($query);
if ($result->num_rows > 0) {
    echo "Found Marriage tickets in Window 1:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Ticket: " . $row['ticket_number'] . " | Window ID: " . $row['window_id'] . " | Window: " . $row['window_name'] . " (" . $row['window_number'] . ") | Status: " . $row['status'] . "\n";
    }
} else {
    echo "No Marriage tickets found in Window 1\n";
}

echo "\n=== All Marriage tickets and their windows ===\n";

// Also check all marriage tickets to see which windows they're in
$query2 = "SELECT q.ticket_number, q.window_id, w.window_name, w.window_number, q.status
           FROM queues q 
           JOIN windows w ON q.window_id = w.id 
           WHERE q.ticket_number LIKE 'MARRIAGE%'
           ORDER BY q.created_at DESC LIMIT 10";

$result2 = $db->query($query2);
if ($result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        echo "Ticket: " . $row['ticket_number'] . " | Window ID: " . $row['window_id'] . " | Window: " . $row['window_name'] . " (" . $row['window_number'] . ") | Status: " . $row['status'] . "\n";
    }
} else {
    echo "No Marriage tickets found\n";
}

echo "\n=== Window assignments ===\n";
$windowQuery = "SELECT id, window_number, window_name FROM windows ORDER BY window_number";
$windowResult = $db->query($windowQuery);
while ($row = $windowResult->fetch_assoc()) {
    echo "Window ID: " . $row['id'] . " | Number: " . $row['window_number'] . " | Name: " . $row['window_name'] . "\n";
}

$db->close();
?>
