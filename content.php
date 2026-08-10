
<?php
require_once 'helpers.php';
start_secure_session();
require_auth();

if ($_SESSION['role'] === 'admin') {
    include 'admin_panel_content.php';
} elseif ($_SESSION['role'] === 'member') {
    include 'member_panel_content.php';
} else {
    echo show_alert('Access denied: Invalid user role.', 'danger');
}
?>