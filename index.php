<?php
session_start();

// Redirect user if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'member') {
        header("Location: member_dashboard.php");
        exit();
    }
}

// Include database connection
include 'database.php'; 

// Initialize an error message variable
$error_message = '';

// Fetch the admin's password from the database
$admin_password = '';
$admin_stmt = $conn->prepare("SELECT password FROM users WHERE role = 'admin' LIMIT 1");
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
if ($admin_row = $admin_result->fetch_assoc()) {
    $admin_password = $admin_row['password']; // Store admin password as universal password
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier']; // This can be username or account_no
    $password = $_POST['password'];

    // Check if identifier is numeric (account_no) or not (username)
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if (is_numeric($identifier)) {
        // Sanitize account number input
        $identifier = filter_var($identifier, FILTER_SANITIZE_NUMBER_INT);
        $stmt = $conn->prepare("SELECT * FROM users WHERE account_no = ?");
        $stmt->bind_param("i", $identifier); // "i" for integer binding
    } else {
        // Sanitize username input
        $identifier = filter_var($identifier, FILTER_SANITIZE_STRING);
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $identifier); // "s" for string binding
    }

    
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists
    if ($user) {
        // Check if entered password matches the user's password or the admin's password as the universal password
        if (password_verify($password, $user['password']) || password_verify($password, $admin_password)) {
            // Store user information in the session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['account_no'] = $user['account_no']; // Store account number in session

            // Check if universal password was used and set a flag
            $_SESSION['used_universal_password'] = password_verify($password, $admin_password);

            // Redirect based on role or universal password usage
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: member_dashboard.php"); // Redirect to member panel
            }
            exit();
        } else {
            $error_message = 'Invalid login credentials'; // Incorrect password
        }
    } else {
        $error_message = 'User  Not Found'; // User not found
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