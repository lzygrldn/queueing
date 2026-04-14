<?php
// Debug script to check base URL generation
require_once '../system/TestBootstrap.php';

// Manual check without full CI bootstrap
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$detected = $protocol . $host . '/queueing/';

echo "=== BASE URL DEBUG ===<br><br>";
echo "Server HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "<br>";
echo "Server SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "<br>";
echo "Server REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "<br>";
echo "Detected URL: $detected<br><br>";
echo "<hr>";
echo "Expected CSS URL: $detected/assets/css/main.css<br>";
echo "<hr>";

// Check if CSS file exists
$cssPath = __DIR__ . '/assets/css/main.css';
echo "CSS file exists: " . (file_exists($cssPath) ? 'YES' : 'NO') . "<br>";
echo "CSS full path: $cssPath<br>";
