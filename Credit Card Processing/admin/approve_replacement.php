<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        // Get user ID and old card number
        $sql = "SELECT user_id, old_card_number FROM card_replacements WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($user_id, $old_card_number);
        $stmt->fetch();
        $stmt->close();

        // Generate a new card
        $new_card_number = rand(4000000000000000, 4999999999999999);
        $cvv = rand(100, 999);
        $expiry_date = date('Y-m-d', strtotime('+5 years'));

        // Get old card balance
        $sqlBalance = "SELECT balance FROM issue_cards WHERE card_number = ?";
        $stmt = $conn->prepare($sqlBalance);
        $stmt->bind_param("i", $old_card_number);
        $stmt->execute();
        $stmt->bind_result($balance);
        $stmt->fetch();
        $stmt->close();

        // Insert new card with same balance
        $insertCard = "INSERT INTO issue_cards (user_id, card_number, cvv, expiry_date, balance, status) VALUES (?, ?, ?, ?, ?, 'Active')";
        $stmt = $conn->prepare($insertCard);
        $stmt->bind_param("iiisd", $user_id, $new_card_number, $cvv, $expiry_date, $balance);
        $stmt->execute();
        $stmt->close();

        // Deactivate old card
        $updateOldCard = "UPDATE issue_cards SET status = 'Inactive' WHERE card_number = ?";
        $stmt = $conn->prepare($updateOldCard);
        $stmt->bind_param("i", $old_card_number);
        $stmt->execute();
        $stmt->close();

        // Mark request as approved
        $updateStatus = "UPDATE card_replacements SET status = 'Approved' WHERE id = ?";
    } else {
        // Mark request as rejected
        $updateStatus = "UPDATE card_replacements SET status = 'Rejected' WHERE id = ?";
    }

    $stmt = $conn->prepare($updateStatus);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}
?>
