<?php
include 'db_connect.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Validate request parameters
if (!isset($_GET['id']) || !isset($_GET['type']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$request_id = $_GET['id'];
$request_type = $_GET['type'];
$action = $_GET['action'];

if ($request_type === "application") {
    if ($action === "approve") {
        // Generate card details
        $card_number = rand(4000000000000000, 4999999999999999); // Random 16-digit number
        $cvv = rand(100, 999); // Random 3-digit CVV
        $expiry_date = date('Y-m-d', strtotime('+5 years')); // 5-year validity
        $initial_balance = 10000.00; // Default balance

        // Fetch user ID from application
        $query = $conn->prepare("SELECT user_id FROM applicationforms WHERE id = ?");
        $query->bind_param("i", $request_id);
        $query->execute();
        $query->bind_result($user_id);
        $query->fetch();
        $query->close();

        if ($user_id) {
            // Insert into issue_cards
            $stmt = $conn->prepare("INSERT INTO issue_cards (user_id, card_number, cvv, expiry_date, status, balance) VALUES (?, ?, ?, ?, 'Active', ?)");
            $stmt->bind_param("iissi", $user_id, $card_number, $cvv, $expiry_date, $initial_balance);
            $stmt->execute();
            $stmt->close();

            // Update application status
            $stmt = $conn->prepare("UPDATE applicationforms SET application_status = 'Approved' WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === "reject") {
        $stmt = $conn->prepare("UPDATE applicationforms SET application_status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }
} elseif ($request_type === "renewal") {
    if ($action === "approve") {
        // Update the expiry date to 5 years from today
        $new_expiry_date = date('Y-m-d', strtotime('+5 years'));

        $stmt = $conn->prepare("UPDATE issue_cards SET expiry_date = ? WHERE card_number = (SELECT card_number FROM card_renewal WHERE id = ?)");
        $stmt->bind_param("si", $new_expiry_date, $request_id);
        $stmt->execute();
        $stmt->close();

        // Mark the renewal request as approved
        $stmt = $conn->prepare("UPDATE card_renewal SET status = 'Approved' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === "reject") {
        $stmt = $conn->prepare("UPDATE card_renewal SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }
} elseif ($request_type === "replacement") {
    if ($action === "approve") {
        // Generate a new card number and CVV
        $new_card_number = rand(4000000000000000, 4999999999999999);
        $new_cvv = rand(100, 999);
        $new_expiry_date = date('Y-m-d', strtotime('+5 years'));

        // Fetch user ID and balance from the old card
        $query = $conn->prepare("SELECT user_id, balance FROM issue_cards WHERE card_number = (SELECT card_number FROM card_replacements WHERE id = ?)");
        $query->bind_param("i", $request_id);
        $query->execute();
        $query->bind_result($user_id, $balance);
        $query->fetch();
        $query->close();

        if ($user_id) {
            // Insert new card
            $stmt = $conn->prepare("INSERT INTO issue_cards (user_id, card_number, cvv, expiry_date, status, balance) VALUES (?, ?, ?, ?, 'Active', ?)");
            $stmt->bind_param("iissi", $user_id, $new_card_number, $new_cvv, $new_expiry_date, $balance);
            $stmt->execute();
            $stmt->close();

            // Mark old card as "Replaced"
            $stmt = $conn->prepare("UPDATE issue_cards SET status = 'Replaced' WHERE card_number = (SELECT card_number FROM card_replacements WHERE id = ?)");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();

            // Update replacement request status
            $stmt = $conn->prepare("UPDATE card_replacements SET status = 'Approved' WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === "reject") {
        $stmt = $conn->prepare("UPDATE card_replacements SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
