<?php
require_once 'helpers.php';
start_secure_session();

// Clear all session variables
$_SESSION = [];

// If it's desired to delete the session cookie as well, then use the following code
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to the login page after session destruction
header("Location: index.php");
exit();

