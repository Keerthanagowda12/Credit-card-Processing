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
        $status = 'Approved';
    } elseif ($action == 'reject') {
        $status = 'Rejected';
    } else {
        die("Invalid action.");
    }

    $sql = "UPDATE card_renewal SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo "Card renewal request has been " . strtolower($status) . ".";
    } else {
        echo "Error updating status.";
    }

    $stmt->close();
    $conn->close();
    header("Location: admin_dashboard.php"); // Redirect back
    exit();
} else {
    echo "Invalid request.";
}
?>

