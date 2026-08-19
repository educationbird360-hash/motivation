<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
redirect_if_logged_in();

$error_message = '';
$adminResult = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$adminExists = $adminResult && $adminResult->num_rows > 0;
if ($adminResult) {
    $adminResult->free();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $error_message = show_alert('Your session expired. Please refresh the page and try again.', 'warning');
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = sanitize_text($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = sanitize_email($_POST['email'] ?? '');

    if ($username === '' || $password === '' || $email === '') {
        $error_message = show_alert('Please fill in all required fields.', 'warning');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = show_alert('Please enter a valid email address.', 'warning');
    } else {
        if ($adminExists) {
            $error_message = show_alert('An admin account already exists. This registration is only for the first admin.', 'warning');
        } else {
            do {
                $accountNo = random_int(10000000, 99999999);
                $accountCheck = $conn->prepare("SELECT id FROM users WHERE account_no = ? LIMIT 1");
                $accountCheck->bind_param("i", $accountNo);
                $accountCheck->execute();
                $accountCheck->store_result();
                $accountExists = $accountCheck->num_rows > 0;
                $accountCheck->close();
            } while ($accountExists);

            $securePassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (account_no, username, password, email, role) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->bind_param("isss", $accountNo, $username, $securePassword, $email);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php");
                exit();
            }

            $error_message = show_alert('Unable to register admin account. Please try again.', 'danger');
            $stmt->close();
        }

    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #6a11cb, #ffff);
    color: #333;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    padding: 0 10px; /* Add padding to prevent the content from touching the edges on small screens */
}

.signup-container {
    background: #fff;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 400px; /* This controls the maximum width */
    text-align: center;
    box-sizing: border-box; /* Ensures padding doesn't affect the width */
}

.signup-container h1 {
    color: #2575fc;
    margin-bottom: 20px;
    font-size: 24px;
}

label {
    display: block;
    text-align: left;
    margin: 10px 0 5px;
    font-weight: bold;
    color: #555;
}

input[type="text"], input[type="password"], input[type="email"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

button {
    width: 100%;
    background: #2575fc;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #1a5bb8;
}

.signup-container p {
    margin-top: 15px;
    font-size: 14px;
    color: #777;
}

.signup-container p a {
    color: #2575fc;
    text-decoration: none;
}

.signup-container p a:hover {
    text-decoration: underline;
}

/* Media Queries for mobile responsiveness */
@media (max-width: 300px) {
    .signup-container {
        padding: 15px; /* Reduce padding on small screens */
        max-width: 100%; /* Allow full-width form on small screens */
    }

    .signup-container h1 {
        font-size: 20px; /* Smaller font size for headings on small screens */
    }

    input[type="text"], input[type="password"], input[type="email"], button {
        font-size: 16px; /* Larger font size for easier typing on small screens */
        padding: 12px; /* Increase padding for better touch interaction */
    }
}

    </style>
</head>
<body>
<div class="signup-container">
    <!-- Display error message if there is an admin account already -->
    <?php if (!empty($error_message)) echo $error_message; ?>

    <h1><?php echo $adminExists ? 'Admin Setup Complete' : 'Initial Admin Setup'; ?></h1>
    <?php if ($adminExists): ?>
        <p>An administrator account already exists. Sign in from the member portal.</p>
    <?php else: ?>
    <form method="POST" action="">
        <?php echo csrf_field(); ?>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Sign Up</button>
    </form>
    <p class="login-link">
        Already have an account? <a href="index.php">Login here</a>
    </p>
    <?php endif; ?>
</div>


</body>
</html>
