<?php
session_start();

// Check if the user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 24px;
            color: #ffffff;
            text-align: left;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #FFFFFF;
            color: #2c3e50;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        .content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Toggle Button */
        .toggle-button {
            display: none;
            background-color: #2c3e50;
            color: white;
            border: none;
            font-size: 18px;
            padding: 10px 15px;
            cursor: pointer;
            position: fixed;
            top: 10px;
            right: 10px; /* Move to the right side of the page */
            z-index: 1001;
            border-radius: 5px;
        }

        /* Sidebar hidden on small screens */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%); /* Hide sidebar by default on small screens */
            }

            .sidebar.active {
                transform: translateX(0); /* Show sidebar when active */
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-button {
                display: block;
            }
        }
    </style>

</head>
</head>
<body>
    <!-- Toggle button for small screens -->
    <button class="toggle-button" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar">
    
    <h2>Admin Dashboard</h2>
    <ul>
        <!-- Note the target attribute on the links pointing to the iframe's name -->
            <li><a href="admin_panel.php" target="contentFrame"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="payment.php" target="contentFrame"><i class="fas fa-book"></i> Payment</a></li>
            <li><a href="report.php" target="contentFrame"><i class="fas fa-question-circle"></i> Report</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

</div>

<div class="main-content">
        <!-- Content iframe for loading dynamic content -->
        <iframe src="admin_panel.php" name="contentFrame" style="width: 100%; height: 90vh; border: none;"></iframe>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>