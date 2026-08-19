<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
require_member();

$account_no = filter_var($_SESSION['account_no'], FILTER_VALIDATE_INT);

if (!$account_no) {
    echo show_alert('Invalid account number in session.', 'danger');
    exit();
}

$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 25;
$offset = ($page - 1) * $perPage;
$query = "SELECT date, description, payment_mode, amount, transaction_type, balance_before, balance_after FROM payments WHERE account_no = ? ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
  echo show_alert('Passbook setup is incomplete. Please ask the administrator to run the payments-table migration from README.md.', 'danger');
  exit();
}
$stmt->bind_param("iii", $account_no, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM payments WHERE account_no = ?");
$count_stmt->bind_param("i", $account_no);
$count_stmt->execute();
$count_stmt->bind_result($totalTransactions);
$count_stmt->fetch();
$count_stmt->close();
$totalPages = max(1, (int) ceil($totalTransactions / $perPage));

$balance_query = "SELECT wallet_balance FROM users WHERE account_no = ?";
$balance_stmt = $conn->prepare($balance_query);
$balance_stmt->bind_param("i", $account_no);
$balance_stmt->execute();
$balance_result = $balance_stmt->get_result();
$balance_data = $balance_result->fetch_assoc();
$balance = $balance_data['wallet_balance'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Passbook</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <Style>
body {
  font-family: 'Arial', sans-serif;
  background: linear-gradient(to bottom, #f0f8ff, #ffffff); /* Light gradient background */
  color: #333;
  margin: 0;
  padding: 0;
}

.container {
  padding: 50px;
  max-width: 100%;
  background: #ffffff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
  border-radius: 10px; /* Smooth corners */
}

.navbar {
  padding: 15px;
  border-radius: 10px;
  background: linear-gradient(to right, teal, #2c3e50); /* Stylish gradient */
  color: #fff;
  font-size: 18px;
}

.navbar .navbar-brand {
  font-weight: bold;
  color: #ffffff;
}

.content h2 {
  font-size: 24px;
  color: #2575fc; /* Accent color */
  margin-bottom: 20px;
  display: inline-block;
  border-bottom: 3px solid #6a11cb; /* Underline effect */
  padding-bottom: 5px;
}

.content p {
  font-size: 18px;
  margin-bottom: 15px;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th {
  background-color: #2575fc;
  color: white;
  text-align: left;
  padding: 10px;
  font-size: 16px;
}

.table td {
  padding: 10px;
  border: 1px solid #ddd;
  font-size: 15px;
}

.table tbody tr:nth-child(even) {
  background-color: #f9f9f9; /* Alternating row color */
}

.table tbody tr:hover {
  background-color: #f1f5ff; /* Row hover effect */
}

.custom-icon {
  color: teal;
  font-size: 22px;
  vertical-align: middle;
  margin-right: 10px;
}

footer {
  text-align: center;
  color: #888;
  font-size: 14px;
}


    </style>
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
                <i class="fa fa-book custom-icon"></i> <h2>Your Passbook</h2>
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
                  <?php if ($page > 1): ?><a class="btn btn-outline-secondary" href="member_passbook.php?page=<?php echo $page - 1; ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
                  <?php if ($page < $totalPages): ?><a class="btn btn-outline-primary" href="member_passbook.php?page=<?php echo $page + 1; ?>">Next</a><?php endif; ?>
                </nav>
            </section>
        </main>
    </div>
</body>

</html>