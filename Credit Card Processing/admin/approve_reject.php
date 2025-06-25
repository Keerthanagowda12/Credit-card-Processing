<?php
include 'db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get application ID and action (approve/reject)
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id']; // Application ID
    $action = $_GET['action'];

    // Fetch user_id from applicationforms
    $userQuery = "SELECT user_id FROM applicationforms WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $id);
    $userStmt->execute();
    $userStmt->bind_result($user_id);
    $userStmt->fetch();
    $userStmt->close();

    // Check if user_id exists
    if (!$user_id) {
        die("Error: User ID not found for this application.");
    }

    // Update application status
    if ($action == 'approve') {
        $status = 'Approved';

        // Generate Credit Card Details
        $card_number = rand(4000000000000000, 4999999999999999); // 16-digit card number
        $cvv = rand(100, 999); // 3-digit CVV
        $expiry_date = date('Y-m-d', strtotime("+5 years")); // Expiry date (5 years from now)

        // Insert into issue_cards table
        $insertCard = "INSERT INTO issue_cards (user_id, card_number, cvv, expiry_date, status) VALUES (?, ?, ?, ?, 'Active')";
        $stmtCard = $conn->prepare($insertCard);
        $stmtCard->bind_param("iisi", $user_id, $card_number, $cvv, $expiry_date);
        $stmtCard->execute();
        $stmtCard->close();
    } elseif ($action == 'reject') {
        $status = 'Rejected';
    }

    // Update application status in applicationforms
    $sql = "UPDATE applicationforms SET application_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
