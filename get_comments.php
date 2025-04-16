<?php
require 'session.php';
require 'config.php';

header('Content-Type: application/json');

$post_id = $_GET['post_id'];

// Get comments with usernames
$stmt = $conn->prepare("
    SELECT comments.*, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.post_id = ? 
    ORDER BY comments.created_at DESC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'id' => $row['id'],
        'content' => htmlspecialchars($row['content']),
        'username' => htmlspecialchars($row['username']),
        'created_at' => date('M d, Y H:i', strtotime($row['created_at']))
    ];
}

echo json_encode($comments);
?> 