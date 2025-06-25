<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user’s card
$sql = "SELECT card_number FROM issue_cards WHERE user_id = ?";
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

// Check if replacement request is already pending
$checkRequest = "SELECT status FROM card_replacements WHERE user_id = ? AND old_card_number = ? AND status = 'Pending'";
$stmt = $conn->prepare($checkRequest);
$stmt->bind_param("ii", $user_id, $card_number);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$status) {
    $insertReplacement = "INSERT INTO card_replacements (user_id, old_card_number) VALUES (?, ?)";
    $stmt = $conn->prepare($insertReplacement);
    $stmt->bind_param("ii", $user_id, $card_number);
    
    if ($stmt->execute()) {
        echo "<script>alert('Card replacement request submitted!');</script>";
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
    <title>Replace Card</title>
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
        <h2>Replace Card</h2>
        <p><strong>Card Number:</strong> <?php echo htmlspecialchars($card_number); ?></p>

        <?php if ($status == 'Pending'): ?>
            <p>Your replacement request is pending approval.</p>
        <?php else: ?>
            <form method="post">
                <button type="submit">Request Replacement</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
