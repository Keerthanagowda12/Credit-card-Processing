<?php
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's card number & balance
$sql = "SELECT card_number, balance FROM issue_cards WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $card_number = $row['card_number'];
    $balance = $row['balance'];
} else {
    exit(); // No issued card, just stop execution
}

$stmt->close();

// Handle transaction submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $transaction_type = $_POST['transaction_type'];

    // Validate amount (should be greater than 0)
    if ($amount <= 0) {
        header("Location: make_transaction.php"); // Redirect to refresh the page
        exit();
    }

    // Check balance for purchase
    if ($transaction_type == "purchase" && $amount > $balance) {
        header("Location: make_transaction.php"); // Redirect instead of displaying error
        exit();
    }

    // Insert transaction
    $insertTransaction = "INSERT INTO transactions (user_id, card_number, amount, transaction_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertTransaction);
    $stmt->bind_param("iids", $user_id, $card_number, $amount, $transaction_type);
    $stmt->execute();
    $stmt->close();

    // Update balance
    $new_balance = ($transaction_type == "purchase") ? $balance - $amount : $balance + $amount;
    $updateBalance = "UPDATE issue_cards SET balance = ? WHERE card_number = ?";
    $stmtUpdate = $conn->prepare($updateBalance);
    $stmtUpdate->bind_param("di", $new_balance, $card_number);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    header("Location: make_transaction.php"); // Refresh page after success
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make A Transaction</title>
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
            width: 350px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #0056b3;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input, select {
            padding: 8px;
            border: 1px solid #0056b3;
            border-radius: 5px;
            width: 100%;
            background-color: #cce5ff;
            color: black;
        }
        button {
            padding: 10px;
            background: #0056b3;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #004494;
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
        <h2>Make a Transaction</h2>

        <?php if ($card_number): ?>
            <form method="post">
                <label>Amount:</label>
                <input type="number" name="amount" step="0.01" min="0.01" required>

                <label>Transaction Type:</label>
                <select name="transaction_type">
                    <option value="purchase">Purchase</option>
                    <option value="payment">Payment</option>
                </select>

                <button type="submit">Submit</button>
            </form>
        <?php else: ?>
            <p>No issued credit card found.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
