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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <div class="dashboard-brand">
                <span class="dashboard-brand-mark"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
                <span>
                    <strong>Admin Workspace</strong>
                    <small>Operations portal</small>
                </span>
            </div>

            <nav class="dashboard-nav" aria-label="Admin navigation">
                <a href="admin_panel.php" target="contentFrame"><i class="fas fa-grid-2" aria-hidden="true"></i><span>Overview</span></a>
                <a href="payment.php" target="contentFrame"><i class="fas fa-wallet" aria-hidden="true"></i><span>Payments</span></a>
                <a href="report.php" target="contentFrame"><i class="fas fa-chart-line" aria-hidden="true"></i><span>Reports</span></a>
                <a class="dashboard-nav-logout" href="logout.php"><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Sign out</span></a>
            </nav>

            <p class="dashboard-sidebar-footer">Manage member accounts, wallet activity, and operational reports from one place.</p>
        </aside>

        <main class="dashboard-main">
            <header class="dashboard-topbar">
                <div>
                    <p class="dashboard-kicker">Administrator access</p>
                    <h1>Good to see you, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                </div>
                <div class="dashboard-user"><i class="fas fa-user" aria-hidden="true"></i><span>Administrator</span></div>
                <button class="dashboard-menu-toggle" type="button" aria-label="Open navigation" aria-controls="dashboardSidebar" aria-expanded="false" onclick="toggleSidebar(this)">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </header>

            <section class="dashboard-workspace" aria-label="Admin workspace">
                <iframe class="dashboard-frame" src="admin_panel.php" name="contentFrame" title="Admin workspace"></iframe>
            </section>
        </main>
    </div>

    <script>
        function toggleSidebar(button) {
            const sidebar = document.getElementById('dashboardSidebar');
            const isOpen = sidebar.classList.toggle('is-open');
            button.setAttribute('aria-expanded', String(isOpen));
        }
    </script>
</body>
</html>