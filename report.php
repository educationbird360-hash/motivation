<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
require_admin();

function getUserReport($conn, $interval) {
    $query = "SELECT COUNT(*) as user_count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->bind_result($userCount);
    $stmt->fetch();
    $stmt->close();
    return $userCount;
}

$allowed = ['daily' => 'DAY', 'weekly' => 'WEEK', 'monthly' => 'MONTH'];
$reportType = $_POST['report_type'] ?? 'daily';
$reportType = array_key_exists($reportType, $allowed) ? $reportType : 'daily';
$userCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interval = $allowed[$reportType];
    $userCount = getUserReport($conn, $interval);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">User Registration Report</h2>
    <form method="POST" action="report.php" class="form-inline justify-content-center mb-4">
        <label for="report_type" class="mr-2 font-weight-bold">Select Report Type:</label>
        <select name="report_type" id="report_type" class="form-control mr-2">
            <option value="daily" <?php if ($reportType == 'daily') echo 'selected'; ?>>Daily</option>
            <option value="weekly" <?php if ($reportType == 'weekly') echo 'selected'; ?>>Weekly</option>
            <option value="monthly" <?php if ($reportType == 'monthly') echo 'selected'; ?>>Monthly</option>
        </select>
        <button type="submit" class="btn btn-primary">View Report</button>
    </form>

    <div class="text-center">
        <h4>New Users: <?php echo htmlspecialchars($userCount); ?></h4>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
