<?php
// Start the session
session_start();

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

// Optionally, redirect to a login or home page after session destruction
header("Location: index.php"); // Change to your desired page
exit();
?>
?>
