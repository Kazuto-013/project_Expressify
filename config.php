<?php
$host = 'localhost';
$db = 'expressify_db';
$user = 'root';
$pass = 'root1';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>