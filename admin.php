<?php
require 'session.php';
require 'config.php';
$user_id = $_SESSION['user_id'];

// Check if the current user is admin (for demo purposes, assume ID 1 is admin)
if ($user_id != 1) {
    echo "Access Denied.";
    exit;
}

$result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html>
<head><title>Admin Panel - Expressify</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
    <h2>Admin Dashboard</h2>
    <h3>Registered Users</h3>
    <table border="1">
        <tr><th>ID</th><th>Username</th><th>Email</th></tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['username']; ?></td><td><?php echo $row['email']; ?></td></tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
