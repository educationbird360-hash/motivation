<?php
session_start();

// Check if the user is logged in and is a member
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'member') {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 0px; /* Matches sidebar width */
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

        .custom-icon {
            display: flex; /* Ensures height and width are applied */
            align-items:center;
            height: 50px; /* Set desired height */
            width: 50px; /* Set desired width */
            font-size: 40px; /* Adjust the icon size within the container */
            color: teal;
            line-height: 50px; /* Centers the icon vertically */
            text-align: center; /* Centers the icon horizontally */
            border-radius: 10px; /* Optional: Adds rounded corners */
        }


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
    <div>
        <main class="main-content">
            <header>
                
                <nav> <span class="glyphicon glyphicon-search"></span>
                <i class="fa fa-user custom-icon"></i>  <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </nav>
            </header>

            <section class="content">
                <?php include 'member_panel_content.php'; // Include member-specific content ?>
            </section>
        </main>
    </div>
</body>
</html>
