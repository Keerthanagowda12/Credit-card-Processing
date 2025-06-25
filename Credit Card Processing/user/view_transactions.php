<?php
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch transaction history
$sql = "SELECT amount, transaction_type, transaction_date FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($amount, $transaction_type, $transaction_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('/background2.jpeg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 400px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: lightblue;
            color: black;
        }
        td {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 10px;
            margin-top: 15px;
            background: #0056b3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #004494;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Transaction History</h2>
        <table>
            <tr>
                <th>Amount</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            <?php while ($stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($amount); ?></td>
                    <td><?php echo htmlspecialchars($transaction_type); ?></td>
                    <td><?php echo htmlspecialchars($transaction_date); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php $stmt->close(); ?>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
