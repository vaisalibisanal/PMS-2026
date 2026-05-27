<?php
// logout.php - destroys session and clears cookies to log out a user
session_start(); // Start session to access session variables
require_once './../config.php'; // Include config

// Unset all of the session variables.
$_SESSION = [];

// If there's a session cookie, remove it by setting expiration in the past
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Destroy the session.
session_destroy(); // End the session and clear server-side data

// Remove authentication cookies used in this demo
setcookie('user_email', '', time() - 3600, '/'); // Delete cookie by setting past time
setcookie('remember_user', '', time() - 3600, '/'); // Delete remember cookie

// Redirect to login page after logout
header('Location: login.php?message=logged_out');
exit;
