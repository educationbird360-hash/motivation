<?php
session_start();
require 'database.php'; // Include your database connection

// Check if the user is logged in as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Process the payment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accountNo = $_POST['account_no'];
    $amount = $_POST['amount'];
    $paymentMode = $_POST['payment_mode'];
    $description = $_POST['description'];

    // Check if the user exists and retrieve their balance using account_no
    $userCheck = $conn->prepare("SELECT id, wallet_balance FROM users WHERE account_no = ?");
    $userCheck->bind_param('i', $accountNo);
    $userCheck->execute();
    $userCheck->store_result();
    $userCheck->bind_result($userId, $walletBalance);
    
    if ($userCheck->num_rows > 0) {
        $userCheck->fetch();
        
        // Subtract the amount from wallet balance
        $newBalance = $walletBalance - $amount;
        if ($newBalance >= 0) {
            // Update the wallet balance in the users table
            $updateWallet = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE account_no = ?");
            $updateWallet->bind_param('di', $newBalance, $accountNo);
            $updateWallet->execute();

        // Record the payment in the payments table
        $insertPayment = $conn->prepare("INSERT INTO payments (user_id, account_no, amount, payment_mode, description) VALUES (?, ?, ?, ?, ?)");
        $insertPayment->bind_param('iidss', $userId, $accountNo, $amount, $paymentMode, $description);
        $insertPayment->execute();

        echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <strong>Success:</strong> Payment processed successfully. New wallet balance is: Rs.'." ". number_format($newBalance, 2) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';



    } else {
        echo '<div class="alert alert-Danger alert-dismissible fade show mt-3" role="alert">
        Insufficient Balance in this account.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        
        }

    } else {
        echo '<div class="alert alert-Warning alert-dismissible fade show mt-3" role="alert">
        User not found with this account no.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery (only needed for Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle (including Popper.js) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Additional custom styling (optional) */
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 5%;
        }
        h3 {
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="container my-5">
        <form method="POST" action="" class="bg-light p-5 rounded shadow-lg" style="max-width: 600px; margin: auto;">
            <h3 class="text-center text-primary mb-4">Payment Form</h3>
            
            <div class="mb-3">
                <label for="account_no" class="form-label fw-bold text-muted">Account Number</label>
                <input type="number" name="account_no" class="form-control form-control-lg border-primary shadow-sm" id="account_no" required placeholder="Enter account number">
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label fw-bold text-muted">Amount</label>
                <input type="number" name="amount" class="form-control form-control-lg border-primary shadow-sm" id="amount" required step="0.01" placeholder="Enter amount">
            </div>
            
            <div class="mb-3">
                <label for="payment_mode" class="form-label fw-bold text-muted">Payment Mode</label>
                <select name="payment_mode" id="payment_mode" class="form-select form-select-lg border-primary shadow-sm">
                    <option value="online">Online</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="description" class="form-label fw-bold text-muted">Description</label>
                <input type="text" name="description" class="form-control form-control-lg border-primary shadow-sm" id="description" required placeholder="Enter description">
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg shadow">Make Payment</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS (optional for additional Bootstrap features) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
