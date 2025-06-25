<?php
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's issued card details
$sql = "SELECT card_number, cvv, expiry_date, balance FROM issue_cards WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $card_number = $row['card_number'];
    $cvv = $row['cvv'];
    $balance = $row['balance'];

    // If expiry date is 0000-00-00, set it to 5 years from today
    if ($row['expiry_date'] == '0000-00-00') {
        $expiry_date = date('Y-m-d', strtotime('+5 years'));
    } else {
        $expiry_date = $row['expiry_date'];
    }
} else {
    $error = "No issued credit card found.";
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Credit Card</title>
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

        .card-container {
            background: linear-gradient(135deg, #004d00, #007000); /* Dark Green Gradient */
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            width: 380px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.4);
            color: white;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }

        .card-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            filter: blur(50px);
        }

        h2 {
            margin-bottom: 15px;
            font-size: 22px;
            text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.5);
        }

        .card-details {
            font-size: 18px;
            letter-spacing: 1px;
            margin: 10px 0;
        }

        .chip {
            width: 50px;
            height: 40px;
            background: gold;
            border-radius: 8px;
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .cvv {
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 5px;
            display: inline-block;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="card-container">
        <div class="chip"></div> <!-- Gold Chip Image -->
        <h2> Credit Card</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php else: ?>
            <p class="card-details"><strong>Card Number:</strong> <?php echo htmlspecialchars($card_number); ?></p>
            <p class="card-details"><strong>CVV:</strong> <span class="cvv"><?php echo htmlspecialchars($cvv); ?></span></p>
            <p class="card-details"><strong>Expiry Date:</strong> <?php echo htmlspecialchars($expiry_date); ?></p>
            <p class="card-details"><strong>Balance:</strong> $<?php echo htmlspecialchars(number_format($balance, 2)); ?></p>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

</body>
</html>
