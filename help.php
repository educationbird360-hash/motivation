<?php
require_once 'helpers.php';

start_secure_session();
require_member();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Help</title>
    <style>
        body {
            margin: 0;
            padding: 2rem;
            background: #f4f4f4;
            color: #243746;
            font-family: Arial, sans-serif;
        }
        .help-content {
            max-width: 720px;
            margin: 0 auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-top: 0;
        }
        li {
            margin-bottom: 0.75rem;
        }
        .support-note {
            color: #586b78;
        }
    </style>
</head>
<body>
    <main class="help-content">
        <h1>Member Help</h1>
        <p>Use this private portal to review your wallet balance, passbook, and member information.</p>
        <ul>
            <li>Use your username or account number and password to sign in.</li>
            <li>Review transactions in My Passbook.</li>
            <li>Contact the organization that issued your account if your credentials or account details need to be updated.</li>
        </ul>
        <p class="support-note">Do not share your password with anyone. This portal does not request bank card numbers, one-time passwords, or payment credentials.</p>
    </main>
</body>
</html>
