
<?php
// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Check the user role and include the appropriate content
if ($_SESSION['role'] === 'admin') {
    // Content for admin users
    include 'admin_panel_content.php'; // Create this file for admin specific content
} elseif ($_SESSION['role'] === 'member') {
    // Content for member users
    include 'member_panel_content.php'; // Create this file for member specific content
} else {
    echo "<p>Access Denied: Invalid user role.</p>";
}
?>