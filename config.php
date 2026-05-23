<?php
// config.php - database connection and common settings

// Start session on pages that include this file (safe to call multiple times)
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start PHP session to use $_SESSION
}

// Database connection moved to mysql/connection.php to separate concerns
// This file now only contains session setup and helper functions.
// To connect to the database include mysql/connection.php where needed.

// Helper: clean input to prevent basic XSS (trim and remove tags)
function clean_input($data)
{
    $data = trim($data); // Remove extra spaces
    $data = stripslashes($data); // Remove backslashes
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Convert special chars
    return $data; // Return cleaned string
}
?>
