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
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <div class="dashboard-brand">
                <span class="dashboard-brand-mark"><i class="fas fa-compass" aria-hidden="true"></i></span>
                <span>
                    <strong>Member Portal</strong>
                    <small>Account workspace</small>
                </span>
            </div>

            <nav class="dashboard-nav" aria-label="Member navigation">
                <a href="member_panel.php" target="contentFrame"><i class="fas fa-house" aria-hidden="true"></i><span>Overview</span></a>
                <a href="member_passbook.php" target="contentFrame"><i class="fas fa-book-open" aria-hidden="true"></i><span>My passbook</span></a>
                <a href="help.php" target="contentFrame"><i class="fas fa-circle-question" aria-hidden="true"></i><span>Help & support</span></a>
                <a class="dashboard-nav-logout" href="logout.php"><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Sign out</span></a>
            </nav>

            <p class="dashboard-sidebar-footer">Your account information is available only inside this authenticated member portal.</p>
        </aside>

        <main class="dashboard-main">
            <header class="dashboard-topbar">
                <div>
                    <p class="dashboard-kicker">Private member access</p>
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                </div>
                <div class="dashboard-user"><i class="fas fa-user" aria-hidden="true"></i><span>Member account</span></div>
                <button class="dashboard-menu-toggle" type="button" aria-label="Open navigation" aria-controls="dashboardSidebar" aria-expanded="false" onclick="toggleSidebar(this)">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </header>

            <section class="dashboard-workspace" aria-label="Member workspace">
                <iframe class="dashboard-frame" src="member_panel.php" name="contentFrame" title="Member workspace"></iframe>
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
