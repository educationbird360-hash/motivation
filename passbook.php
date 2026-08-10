<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
require_auth();

$account_no = null;
$is_admin = $_SESSION['role'] === 'admin';

if ($is_admin && isset($_POST['account_no'])) {
    $account_no = filter_input(INPUT_POST, 'account_no', FILTER_VALIDATE_INT);
} elseif (isset($_SESSION['account_no'])) {
    $account_no = filter_var($_SESSION['account_no'], FILTER_VALIDATE_INT);
}

if (!$account_no) {
    echo show_alert('No valid account number was provided.', 'danger');
    exit();
}

$user_query = "SELECT username, wallet_balance FROM users WHERE account_no = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $account_no);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user_data) {
    echo show_alert('User not found for the requested account number.', 'warning');
    exit();
}

$username = htmlspecialchars($user_data['username']);
$balance = $user_data['wallet_balance'];

$query = "SELECT * FROM payments WHERE account_no = ? ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $account_no);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Passbook</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <main class="main-content">
            <header>
                <nav class="navbar navbar-light bg-light">
                    <span class="navbar-brand mb-0 h1">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </nav>
            </header>

            <section class="content mt-4">
                <h2><?php echo htmlspecialchars($username); ?>'s Passbook</h2>
                <p><strong>Current Wallet Balance:</strong> ₹<?php echo number_format($balance, 2); ?></p>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Mode</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Display each transaction
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . date("d-m-Y H:i:s", strtotime($row['date'])) . "</td>
                                    <td>" . htmlspecialchars($row['description']) . "</td>
                                    <td>" . htmlspecialchars($row['payment_mode']) . "</td>
                                    <td>₹" . number_format($row['amount'], 2) . "</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
