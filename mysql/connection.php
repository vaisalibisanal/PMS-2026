<?php
// mysql/connection.php - centralised DB  and helpers

// Start session if not already started (safe to call multiple times)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration - update these values to match your environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_app');
define('DB_USER', 'student_db');
define('DB_PASS', 'test1234');

// Create MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    // For beginners: show a simple message and stop execution
    die('Database connection failed.');
}

// Ensure proper UTF-8 handling
$conn->set_charset('utf8mb4');

// Basic input cleaning helper (trim, strip slashes, escape HTML)
// function clean_input($data)
// {
//     $data = trim($data);
//     $data = stripslashes($data);
//     $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
//     return $data;
// }

?>
