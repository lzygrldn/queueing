<?php
// Connect to database
$db = new mysqli('localhost', 'root', '', 'queueing_system');
if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

echo "=== Fixing Marriage tickets in wrong windows ===\n";

// Find all Marriage tickets that are not in Window 4
$query = "SELECT q.id, q.ticket_number, q.window_id, w.window_name, w.window_number, q.status
          FROM queues q 
          JOIN windows w ON q.window_id = w.id 
          WHERE q.ticket_number LIKE 'MARRIAGE%' AND w.window_number != 4
          ORDER BY q.created_at";

$result = $db->query($query);
if ($result->num_rows > 0) {
    echo "Found Marriage tickets in wrong windows:\n";
    $ticketsToFix = [];
    while ($row = $result->fetch_assoc()) {
        echo "Ticket: " . $row['ticket_number'] . " | Current Window: " . $row['window_name'] . " (" . $row['window_number'] . ") | Status: " . $row['status'] . "\n";
        $ticketsToFix[] = $row;
    }
    
    echo "\nMoving these tickets to Window 4 (Marriage Registration)...\n";
    
    // Update each ticket to Window 4
    foreach ($ticketsToFix as $ticket) {
        $updateQuery = "UPDATE queues SET window_id = 4 WHERE id = " . $ticket['id'];
        if ($db->query($updateQuery)) {
            echo "✓ Moved " . $ticket['ticket_number'] . " to Window 4\n";
        } else {
            echo "✗ Failed to move " . $ticket['ticket_number'] . ": " . $db->error . "\n";
        }
    }
    
    echo "\n=== Verification ===\n";
    
    // Verify the fix
    $verifyQuery = "SELECT q.ticket_number, q.window_id, w.window_name, w.window_number, q.status
                    FROM queues q 
                    JOIN windows w ON q.window_id = w.id 
                    WHERE q.ticket_number LIKE 'MARRIAGE%'
                    ORDER BY q.created_at DESC LIMIT 10";
    
    $verifyResult = $db->query($verifyQuery);
    if ($verifyResult->num_rows > 0) {
        echo "Updated Marriage tickets:\n";
        while ($row = $verifyResult->fetch_assoc()) {
            echo "Ticket: " . $row['ticket_number'] . " | Window: " . $row['window_name'] . " (" . $row['window_number'] . ") | Status: " . $row['status'] . "\n";
        }
    }
    
} else {
    echo "No Marriage tickets found in wrong windows. All good!\n";
}

echo "\n=== Checking for other incorrectly assigned tickets ===\n";

// Check for Birth tickets not in Window 2
$birthQuery = "SELECT COUNT(*) as count FROM queues q JOIN windows w ON q.window_id = w.id WHERE q.ticket_number LIKE 'BIRTH%' AND w.window_number != 2";
$birthResult = $db->query($birthQuery);
$birthCount = $birthResult->fetch_assoc()['count'];
echo "Birth tickets in wrong windows: " . $birthCount . "\n";

// Check for Death tickets not in Window 3
$deathQuery = "SELECT COUNT(*) as count FROM queues q JOIN windows w ON q.window_id = w.id WHERE q.ticket_number LIKE 'DEATH%' AND w.window_number != 3";
$deathResult = $db->query($deathQuery);
$deathCount = $deathResult->fetch_assoc()['count'];
echo "Death tickets in wrong windows: " . $deathCount . "\n";

// Check for BREQS tickets not in Window 1
$breqsQuery = "SELECT COUNT(*) as count FROM queues q JOIN windows w ON q.window_id = w.id WHERE q.ticket_number LIKE 'BREQS%' AND w.window_number != 1";
$breqsResult = $db->query($breqsQuery);
$breqsCount = $breqsResult->fetch_assoc()['count'];
echo "BREQS tickets in wrong windows: " . $breqsCount . "\n";

$db->close();
echo "\nFix completed!\n";
?>
