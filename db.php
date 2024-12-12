<?php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload

header('Content-Type: application/json; charset=utf-8');

// Database connection configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'alumni_search';

// Initialize the database connection
try {
    $db = new MysqliDb($host, $username, $password, $database);

    // Test the connection
    if (!$db->ping()) {
        throw new Exception("Database connection error: " . $db->getLastError());
    }
} catch (Exception $th) {
    header("HTTP/1.0 500 Error");
    echo json_encode(['error' => $th->getMessage()]);
    exit;
}
