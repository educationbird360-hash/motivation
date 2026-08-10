<?php
session_start();
include 'database.php'; // Database connection file

$error_message = ''; // Initialize the error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    // Check if an admin already exists
    $checkAdminQuery = "SELECT * FROM users WHERE role = 'admin'";
    $adminResult = $conn->query($checkAdminQuery);

    if ($adminResult->num_rows > 0) {
        // If an admin exists, set the error message
        $error_message = '<div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                            <strong>Notice:</strong> Admin account already exists.
                          </div>';
    } else {
        // If no admin exists, proceed with the user registration
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
        $stmt->bind_param("sss", $username, $password, $email);

        if ($stmt->execute()) {
            echo "Registration successful!";
            header("Location: index.php"); // Redirect after successful registrations
            exit();
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    $adminResult->free();
    $conn->close();
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

    <h1>Create Your Account</h1>
    <form method="POST" action="">
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
</div>


</body>
</html>
