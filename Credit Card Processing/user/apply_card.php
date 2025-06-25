<?php
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from database
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

// Handle form submission (but no message displayed on top)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $income = $_POST['income'];
    $address = $_POST['address'];

    // Insert into database if valid
    $sql = "INSERT INTO applicationforms (user_id, name, email, phone, income, address, application_status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $name, $email, $phone, $income, $address);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // Redirect after success
        exit();
    } else {
        echo "<script>alert('Error submitting application. Please try again.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Credit Card</title>
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

        .form-container {
            background: rgba(173, 216, 230, 0.9);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #004085;
            margin-bottom: 10px;
        }

        .form-container input, 
        .form-container textarea {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #007bff;
            border-radius: 5px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-container button:hover {
            background: #0056b3;
        }

        .back-link {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            color: #004085;
            font-weight: bold;
        }
    </style>

    <script>
        function validateForm() {
            var phone = document.forms["applicationForm"]["phone"].value;
            var phonePattern = /^\d{10}$/;

            if (!phonePattern.test(phone)) {
                alert("Invalid phone number! Please enter exactly 10 digits.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

    <div class="form-container">
        <h2>Apply for a Credit Card</h2>

        <form name="applicationForm" method="POST" onsubmit="return validateForm()">
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" readonly><br>

            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly><br>

            <input type="text" name="phone" placeholder="Phone Number (10 digits)" required><br>

            <input type="number" name="income" placeholder="Income" required><br>

            <textarea name="address" placeholder="Enter your address" required></textarea><br>

            <button type="submit">Submit Application</button>
        </form>

        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

</body>
</html>
