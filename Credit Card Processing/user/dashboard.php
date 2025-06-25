<?php
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user details
$sql = "SELECT email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        /* Full-page background */
        body {
            font-family: Arial, sans-serif;
            background: url('/background1.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        /* Dashboard Container */
        .dashboard-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        /* Title */
        h2 {
            color: #333;
            margin-bottom: 10px;
        }

        p {
            color: #555;
        }

        /* Tab Navigation */
        .nav-tabs {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-tabs li {
            background: #007bff;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            transition: 0.3s;
        }

        .nav-tabs li:hover {
            background: #0056b3;
        }

        .nav-tabs a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            display: block;
        }

        /* Logout Button */
        .logout-btn {
            margin-top: 20px;
            background: red;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            color: white;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>

        <ul class="nav-tabs">
            <li><a href="apply_card.php">Apply for a Credit Card</a></li>
            <li><a href="view_card.php">View My Credit Card</a></li>
            <li><a href="make_transaction.php">Make a Transaction</a></li>
            <li><a href="view_transactions.php">View Transactions</a></li>
            <li><a href="replace_card.php">Request Card Replacement</a></li>
            <li><a href="renew_card.php">Request Card Renewal</a></li>
        </ul>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

</body>
</html>
