<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
redirect_if_logged_in();

// Initialize an error message variable
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error_message = 'Please enter both username/account number and password.';
    } else {
        if (is_numeric($identifier)) {
            $identifier = filter_var($identifier, FILTER_SANITIZE_NUMBER_INT);
            $stmt = $conn->prepare("SELECT * FROM users WHERE account_no = ?");
            $stmt->bind_param("i", $identifier);
        } else {
            $identifier = sanitize_text($identifier);
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $identifier);
        }

        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['account_no'] = $user['account_no'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: member_dashboard.php");
                }
                exit();
            }

            $error_message = 'Invalid login credentials.';
        } else {
            $error_message = 'Unable to process login. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

<style>
/* Fade-out transition effect */
.fade-out {
    opacity: 0;
    transition: opacity 1s ease;
    }

    @media (max-width: 768px) {
  /* Adjust font sizes for better readability on smaller screens */
  h4 {
    font-size: 1.5rem;
  }

  /* Increase input field size for easier typing on smaller screens */
  input[type="text"], input[type="password"] {
    font-size: 1rem;
    padding: 8px;
  }

  /* Adjust button size for better touch interaction */
  button {
    font-size: 1rem;
    padding: 10px 20px;
  }
}
</style>
</head>
<body>

    <div class="form-container">
    <?php if (!empty($error_message)): ?>
        <div id="alertMessage" class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-4">
    <!-- Logo Image -->
    <img src="assets/img/logo.webp" alt="Logo" class="img-fluid" style="max-width: 150px;">
    </div>

        <h4 class="text-left mb-4">Login</h4>
        <form action="index.php" method="post">
            <div class="mb-3">
                <label for="identifier" class="form-label">Username or Account No</label>
                <input type="text" class="form-control" id="identifier" name="identifier" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-custom">Login</button>
        </form>

    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
            // Automatically hide the alert after 3 seconds
    window.addEventListener('DOMContentLoaded', (event) => {
        const alertMessage = document.getElementById('alertMessage');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.classList.add('fade-out');
            }, 3000); // Wait for 3 seconds
        }
    });
    </script>
</body>
</html>