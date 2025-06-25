<?php
include 'db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all pending applications
$sqlApplications = "SELECT id, user_id, name, email, phone, income, address, application_status FROM applicationforms";
$resultApplications = $conn->query($sqlApplications);

// Fetch pending card renewal requests
$sqlRenewals = "SELECT id, user_id, card_number, status FROM card_renewal WHERE status = 'Pending'";
$resultRenewals = $conn->query($sqlRenewals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Admin Dashboard</h2>

    <!-- Application Approvals -->
    <h3>Pending Applications</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Income</th>
            <th>Address</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $resultApplications->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['income']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td><?php echo htmlspecialchars($row['application_status']); ?></td>
                <td>
                    <?php if ($row['application_status'] == 'Pending') { ?>
                        <a href="approve_reject.php?id=<?php echo $row['id']; ?>&action=approve">Approve</a> |
                        <a href="approve_reject.php?id=<?php echo $row['id']; ?>&action=reject">Reject</a>
                    <?php } else { ?>
                        <?php echo $row['application_status']; ?>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Card Renewal Approvals -->
    <h3>Pending Card Renewals</h3>
    <table border="1">
        <tr>
            <th>Request ID</th>
            <th>User ID</th>
            <th>Card Number</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $resultRenewals->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['card_number']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td>
                <a href="/credit_card_system/approve_renewal.php?id=<?php echo $row['id']; ?>&action=approve">Approve</a>
                <a href="/credit_card_system/approve_renewal.php?id=<?php echo $row['id']; ?>&action=reject">Reject</a>

                </td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="admin_logout.php">Logout</a>
</body>
</html>