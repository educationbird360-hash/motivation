<?php
session_start();

// Check if the user is logged in and is an admin
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
    <title>Admin Dashboard</title>
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
        }

        /* Dashboard layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main content styling */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 0px; /* Normal margin for larger screens */
            transition: margin-left 0.3s ease;
        }

        header {
            margin-bottom: 20px;
        }

        header h1 {
            margin: 0;
        }

        nav {
            background-color: #ffffff;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }


        /* Responsive styling */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%); /* Hide sidebar by default on small screens */
            }

            .sidebar.active {
                transform: translateX(0); /* Show sidebar when active */
            }

            .main-content {
                margin-left: 0; /* Remove margin on smaller screens */
            }

            .toggle-button {
                display: block; /* Show toggle button */
            }
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <main class="main-content">
            <header>
                <nav>
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </nav>
            </header>

            <section class="content" id="content">
                <?php include 'admin_panel_content.php'; ?>
            </section>
        </main>
    </div>

    <script>
</body>
</html>
