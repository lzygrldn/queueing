<?php
// Simple debug - just echo server vars
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "<br>";
echo "Full URL would be: http://" . $_SERVER['HTTP_HOST'] . "/queueing/assets/css/main.css<br>";
?>
