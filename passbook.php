<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo show_alert('Your session expired. Please refresh the page and try again.', 'warning');
    exit();
}

$account_no = null;
$is_admin = $_SESSION['role'] === 'admin';

if ($is_admin && (isset($_POST['account_no']) || isset($_GET['account_no']))) {
    $account_no = filter_input(INPUT_POST, 'account_no', FILTER_VALIDATE_INT);
    if ($account_no === null || $account_no === false) {
        $account_no = filter_input(INPUT_GET, 'account_no', FILTER_VALIDATE_INT);
    }
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
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 25;
$offset = ($page - 1) * $perPage;

$query = "SELECT date, description, payment_mode, amount, transaction_type, balance_before, balance_after, processed_by FROM payments WHERE account_no = ? ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo show_alert('Passbook setup is incomplete. Please ask the administrator to run the payments-table migration from README.md.', 'danger');
    exit();
}
$stmt->bind_param("iii", $account_no, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$countStmt = $conn->prepare("SELECT COUNT(*) FROM payments WHERE account_no = ?");
$countStmt->bind_param("i", $account_no);
$countStmt->execute();
$countStmt->bind_result($totalTransactions);
$countStmt->fetch();
$countStmt->close();
$totalPages = max(1, (int) ceil($totalTransactions / $perPage));
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
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Balance Before</th>
                            <th>Balance After</th>
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
                                    <td>" . htmlspecialchars(ucfirst($row['transaction_type'])) . "</td>
                                    <td>₹" . number_format((float) $row['amount'], 2) . "</td>
                                    <td>₹" . number_format((float) $row['balance_before'], 2) . "</td>
                                    <td>₹" . number_format((float) $row['balance_after'], 2) . "</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <nav class="d-flex justify-content-between" aria-label="Passbook pages">
                    <?php if ($page > 1): ?><a class="btn btn-outline-secondary" href="passbook.php?account_no=<?php echo urlencode((string) $account_no); ?>&page=<?php echo $page - 1; ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
                    <?php if ($page < $totalPages): ?><a class="btn btn-outline-primary" href="passbook.php?account_no=<?php echo urlencode((string) $account_no); ?>&page=<?php echo $page + 1; ?>">Next</a><?php endif; ?>
                </nav>
            </section>
        </main>
    </div>
</body>
</html>
