<?php
session_start();
include 'database.php';

// Check if user is logged in and has the correct role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    echo "Access denied. Only logged-in members can view this page.";
    exit();
}

// Check if account_no is set in session
if (!isset($_SESSION['account_no'])) {
    echo "Access denied. You are not logged in.";
    exit();
}

$account_no = $_SESSION['account_no'];

// Fetch user transactions from payments table
$query = "SELECT * FROM payments WHERE account_no = ? ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $account_no);
$stmt->execute();
$result = $stmt->get_result();

// Fetch wallet balance for the user
$balance_query = "SELECT wallet_balance FROM users WHERE account_no = ?";
$balance_stmt = $conn->prepare($balance_query);
$balance_stmt->bind_param("i", $account_no);
$balance_stmt->execute();
$balance_result = $balance_stmt->get_result();
$balance = $balance_result->fetch_assoc()['wallet_balance'];
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