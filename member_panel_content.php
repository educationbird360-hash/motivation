
<?php
require_once 'helpers.php';
require_once 'database.php';

start_secure_session();
require_member();

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo "User  ID is not set in the session.";
    exit();
}

// Function to credit ₹90 to each user in the chain up to the admin
function creditIncentiveToChain($userId, $conn) {
    // Traverse up the hierarchy to credit each user in the chain
    while ($userId) {
        // Credit ₹90 to the current user in the chain
        $update_wallet = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + 90 WHERE id = ?");
        $update_wallet->bind_param("i", $userId);
        $update_wallet->execute();
        $update_wallet->close();

        // Find the parent ID of the current user to move up the chain
        $stmt = $conn->prepare("SELECT parent_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($parentId);
        $stmt->fetch();
        $stmt->close();

        // Set the next user to process as the parent
        $userId = $parentId;
    }
}

// Add new user functionality with a limit check
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user']) && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    // Count the number of users created by the current user
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE parent_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($userCount);
    $stmt->fetch();
    $stmt->close();

// Enable exceptions for MySQLi to catch SQL errors as exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the limit of 3 members is reached
if ($userCount >= 3) {
    echo '<div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <strong>Notice:</strong> You have reached the maximum limit of 3 members.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
} else {
    $new_account_no = filter_input(INPUT_POST, 'new_account_no', FILTER_SANITIZE_NUMBER_INT);
    $new_username = sanitize_text($_POST['new_username'] ?? '');
    $new_password_raw = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['retype_password'] ?? '';

    if (!$new_account_no || $new_username === '' || $new_password_raw === '' || $confirm_password === '') {
        echo show_alert('Please fill in all required member fields.', 'warning');
    } elseif ($new_password_raw !== $confirm_password) {
        echo show_alert('Passwords do not match.', 'warning');
    } else {
        $new_password = password_hash($new_password_raw, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (account_no, username, password, role, parent_id, wallet_balance) VALUES (?, ?, ?, 'member', ?, 0)");
        $stmt->bind_param("sssi", $new_account_no, $new_username, $new_password, $_SESSION['user_id']);
    
    try {
        $stmt->execute();
        $userAddedId = $stmt->insert_id; // Get the ID of the newly created user
        $stmt->close();

        // Credit ₹90 to each user in the chain from the creator up to the root
        creditIncentiveToChain($_SESSION['user_id'], $conn);

        echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
               Member added successfully! ₹90 has been credited to In you wallet.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';

    } catch (mysqli_sql_exception $e) {
        // Check for duplicate entry error (SQL error code 1062)
        if ($e->getCode() === 1062) {
            echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <strong>Error:</strong> Account number already taken. Please use a another account number.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        } else {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }
    }

}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user']) && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo show_alert('Your session expired. Please refresh the page and try again.', 'warning');
}

}

// Get wallet balance for the logged-in user
$stmt_balance = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt_balance->bind_param("i", $_SESSION['user_id']);
$stmt_balance->execute();
$stmt_balance->bind_result($walletBalance);
$stmt_balance->fetch();
$stmt_balance->close();

// Function to display the user tree recursively and count users in the chain
function displayUserTree($parentId, $conn, &$count = 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE parent_id = ?");
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<ol>";
        while ($row = $result->fetch_assoc()) {
            $count++; // Increment count for each user found
            echo "<li>" . htmlspecialchars($row['username']) . " (Account no: " . htmlspecialchars($row['account_no']) . ", Wallet: ₹" . htmlspecialchars($row['wallet_balance']) . ")";
            displayUserTree($row['id'], $conn, $count); // Recursive call to display sub-users
            echo "</li>";
        }
        echo "</ol>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Panel</title>
    
    <style>

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Prevent horizontal scrollbar */
        }

        .container {
            width: 100%;
            max-width: 90%;
            margin: 50px auto;
            padding: 0px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        h3 {
            text-align: left;
            color: #333;
            margin-bottom: 30px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            padding: 10px;
        }

        .user-tree {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
        }

        .user-tree li {
            flex-direction: column; /* Stack username and sub-tree vertically */
            padding: 15px 5px;
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .user-tree li:hover {
            background-color: #e9e9e9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .user-tree ul {
            margin-left: 0px;
        }

        .user-tree ul li {
            font-style: italic;
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

        .container-border {
        border: 3px solid teal; /* Green border */
        border-radius: 15px; /* Rounded corners */
        padding: 20px; /* Space inside the border */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Soft shadow for depth */
        background: linear-gradient(to bottom, #ffffff, #f0f0f0); /* Subtle gradient background */
        transition: transform 0.3s ease-in-out; /* Smooth animation on hover */
        }

        .container-border:hover {
        transform: scale(1.02); /* Slight zoom effect on hover */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        .centered-root-member {
        text-align: center; /* Centers the text horizontally */
        font-size: 1rem; /* Optional: Adjust font size if needed */
        margin: 10px 0; /* Optional: Add some vertical spacing */
        font-weight: bold; /* Optional: Make it stand out */
        color: #333; /* Optional: Set a custom color */
        }


        /* Responsive design */
        @media (max-width: 768px) { /* Adjust breakpoint for tablets */
    .user-tree ul {
        margin-left: 0px; /* Reduce indentation for smaller screens */
    }
    .user-tree li {
        font-size: 15px; /* Smaller font size */
        padding: 2px 2px; /* Less padding */
    }

    .container {
            width: 100%;
            max-width: 100%;
            margin: 10px 0px;
            padding: 10px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

}
@media (max-width: 480px) { /* Adjust breakpoint for phones */
    .user-tree ul {
        margin-left: 10px; /* Further reduced indentation */
    }
    .user-tree li {
        font-size: 12px; /* Even smaller font */
        padding: 8px 12px; /* Minimal padding */
    }
}
</style>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
<div class="container my-3">

<h4><i class="fa fa-wallet custom-icon"></i> ₹ <span id="walletBalance">0</span></h4>
</div>

<div class="container my-5">
    <h3 class="text-center mb-4">Add New Member</h3>
    <form method="POST" action="" class="form-row align-items-center bg-light p-4 rounded shadow" onsubmit="return validateForm()">
                <?php echo csrf_field(); ?>
        <div class="col-md-3 mb-3">
            <label for="new_account_no" class="form-label font-weight-bold">Account No:</label>
            <input type="text" class="form-control" name="new_account_no" id="new_account_no" placeholder="Account No" required pattern="\d+" title="Only numeric values are allowed.">
        </div>
        <div class="col-md-3 mb-3">
            <label for="new_username" class="form-label font-weight-bold">Username:</label>
            <input type="text" class="form-control" name="new_username" id="new_username" placeholder="Username" required pattern="^[a-zA-Z0-9_ ]+$" title="Only letters, numbers, and underscores are allowed.">
        </div>
        <div class="col-md-3 mb-3">
            <label for="new_password" class="form-label font-weight-bold">Password:</label>
            <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Password" required minlength="6" title="Password must be at least 6 characters long.">
        </div>
        <div class="col-md-3 mb-3">
            <label for="retype_password" class="form-label font-weight-bold">Retype Password:</label>
            <input type="password" class="form-control" name="retype_password" id="retype_password" placeholder="Retype Password" required minlength="6" title="Please retype the password.">
        </div>
        <div class="col-md-12 mb-3">
                <button type="submit" name="add_user" class="btn btn-primary w-100 mt-4">Add New Member</button>
        </div>
    </form> 
</div>

<div class="container"> 
        <h3> <i class="fa fa-users custom-icon"></i> Members Tree </h3>
    <div class="container-border">
    <h6 class="centered-root-member">The Root Member is <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></h6>

        <ul class="user-tree">
        <?php
            // Initialize user count
            $userCount = 0;

            // Display the connected users tree starting from the logged-in user
            if (isset($_SESSION['user_id'])) {
                displayUserTree($_SESSION['user_id'], $conn, $userCount);
                echo " <h6><i class= 'fa fa-chain-broken custom-icon'></i> Total Members in this Chain: $userCount</h6>"; // Display the total user count
            } else {
                echo "<p>User ID not found in session.</p>";
            }

            $conn->close();
        ?>
        </ul>
    </div>
 </div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#walletBalance').text(<?php echo $walletBalance; ?>);
    });


    // Form validation 
    function validateForm() {
        const accountNo = document.getElementById('new_account_no').value;
        const username = document.getElementById('new_username').value;
        const password = document.getElementById('new_password').value;
        const retypePassword = document.getElementById('retype_password').value;

        // Check if account number is numeric
        if (!/^\d+$/.test(accountNo)) {
            alert("Account No must be numeric.");
            return false;
        }

        // Check if username contains special characters
        if (/[^a-zA-Z0-9_ ]/.test(username)) {
            alert("Username can only contain letters, numbers, underscores, and spaces.");
            return false;
        }

        // Check if passwords match
        if (password !== retypePassword) {
            alert("Passwords do not match. Please try again.");
            return false;
        }

        return true; // Allow the form to submit if all validations pass
    }

</script>
</body>
</html>

