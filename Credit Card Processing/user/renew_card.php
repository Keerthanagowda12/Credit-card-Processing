<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user’s card details
$sql = "SELECT card_number, expiry_date FROM issue_cards WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "No issued credit card found.";
    exit();
}

$card_number = $row['card_number'];
$expiry_date = $row['expiry_date'];

// Debugging expiry date (Check console)
echo "<script>console.log('Expiry Date from DB: " . $expiry_date . "');</script>";

// Handle invalid expiry dates
if (empty($expiry_date) || $expiry_date == "0000-00-00") {
    $expiry_date_display = "Not Set";
} else {
    $expiry_date_display = htmlspecialchars($expiry_date);
}

// Check if renewal is already requested
$checkRequest = "SELECT status FROM card_renewal WHERE user_id = ? AND card_number = ? AND status = 'Pending'";
$stmt = $conn->prepare($checkRequest);
$stmt->bind_param("ii", $user_id, $card_number);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

// Handle renewal request
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$status) {
    $insertRenewal = "INSERT INTO card_renewal (user_id, card_number) VALUES (?, ?)";
    $stmt = $conn->prepare($insertRenewal);
    $stmt->bind_param("ii", $user_id, $card_number);
    
    if ($stmt->execute()) {
        echo "<script>alert('Card renewal request submitted!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Card</title>
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
        p {
            font-size: 18px;
        }
        button {
            padding: 10px;
            width: 100%;
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
        <h2>Renew Card</h2>
        <p><strong>Card Number:</strong> <?php echo htmlspecialchars($card_number); ?></p>
        <p><strong>Expiry Date:</strong> <?php echo $expiry_date_display; ?></p>

        <?php if ($status == 'Pending'): ?>
            <p>Your renewal request is pending approval.</p>
        <?php else: ?>
            <form method="post">
                <button type="submit">Request Renewal</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
