<?php
// Test what base_url() generates
define('CI_DEBUG', true);
require_once '../system/TestBootstrap.php';

// Get the actual base URL
echo "=== BASE URL TEST ===<br><br>";
echo "baseURL config: " . config('App')->baseURL . "<br>";
echo "base_url() output: " . base_url() . "<br>";
echo "base_url('admin') output: " . base_url('admin') . "<br>";
echo "base_url('assets/css/main.css') output: " . base_url('assets/css/main.css') . "<br><br>";

echo "=== SERVER VARS ===<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "<br>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "<br>";
