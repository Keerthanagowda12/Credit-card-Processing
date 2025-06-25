<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email already exists
    $sqlCheck = "SELECT id FROM users WHERE email = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo "User already registered! <a href='login.php'>Login here</a>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $sqlInsert = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("sss", $name, $email, $hashed_password);

        if ($stmtInsert->execute()) {
            echo "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            echo "Error: " . $stmtInsert->error;
        }

        $stmtInsert->close();
    }

    $stmtCheck->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Card Processing - Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('/background1.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .signup-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            border-top: 5px solid #007bff; /* Light blue tab */
        }
        h2 {
            color: #0056b3;
        }
        label {
            font-size: 16px;
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #333;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .login-link {
            margin-top: 15px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Signup</h2>
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Register</button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
