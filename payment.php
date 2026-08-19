<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
require_admin();

$message = '';

// Process the payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $message = show_alert('Your session expired. Please refresh the page and try again.', 'warning');
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountNo = filter_input(INPUT_POST, 'account_no', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $paymentMode = sanitize_text($_POST['payment_mode'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($accountNo === false || $accountNo === null) {
        $message = show_alert('Please enter a valid account number.', 'warning');
    } elseif ($amount === false || $amount <= 0) {
        $message = show_alert('Invalid payment amount. Please enter a positive number.', 'warning');
    } elseif ($paymentMode === '' || $description === '') {
        $message = show_alert('Please provide payment mode and description.', 'warning');
    } else {
        $userCheck = $conn->prepare("SELECT id, wallet_balance FROM users WHERE account_no = ?");
        $userCheck->bind_param('i', $accountNo);
        $userCheck->execute();
        $userCheck->store_result();
        $userCheck->bind_result($userId, $walletBalance);

        if ($userCheck->num_rows > 0) {
            $userCheck->fetch();
            $userCheck->close();

            $newBalance = $walletBalance - $amount;
            if ($newBalance >= 0) {
                $conn->begin_transaction();

                $updateWallet = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE account_no = ?");
                $updateWallet->bind_param('di', $newBalance, $accountNo);
                $updated = $updateWallet->execute();
                $updateWallet->close();

                $insertPayment = $conn->prepare("INSERT INTO payments (user_id, account_no, amount, payment_mode, description) VALUES (?, ?, ?, ?, ?)");
                $insertPayment->bind_param('iidss', $userId, $accountNo, $amount, $paymentMode, $description);
                $inserted = $insertPayment->execute();
                $insertPayment->close();

                if ($updated && $inserted) {
                    $conn->commit();
                    $message = show_alert('Payment processed successfully. New wallet balance is: Rs. ' . number_format($newBalance, 2), 'success');
                } else {
                    $conn->rollback();
                    $message = show_alert('Payment could not be completed. Please try again.', 'danger');
                }
            } else {
                $message = show_alert('Insufficient balance in this account.', 'danger');
            }
        } else {
            $userCheck->close();
            $message = show_alert('User not found with this account number.', 'warning');
        }
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
        <?php if (!empty($message)) echo $message; ?>
        <form method="POST" action="" class="bg-light p-5 rounded shadow-lg" style="max-width: 600px; margin: auto;">
            <?php echo csrf_field(); ?>
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
