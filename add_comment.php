<?php
require 'session.php';
require 'config.php';
require 'functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$post_id = $data['post_id'];
$content = $data['content'];
$user_id = $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    // Get post author and user info for notification
    $stmt = $conn->prepare("
        SELECT 
            p.user_id as post_author_id,
            u.username as commenter_username,
            pa.username as post_author_username
        FROM posts p 
        JOIN users u ON u.id = ?
        JOIN users pa ON pa.id = p.user_id
        WHERE p.id = ?
    ");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();

    // Add comment
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $content);
    
    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;

        // Create notification for post author if it's not their own comment
        if ($info['post_author_id'] != $user_id) {
            createNotification(
                $conn,
                $info['post_author_id'],
                'post_comment',
                "{$info['commenter_username']} commented on your post",
                $post_id
            );
        }

        // Get the new comment with user info
        $stmt = $conn->prepare("
            SELECT 
                c.*,
                u.username,
                u.profile_picture
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $comment = $stmt->get_result()->fetch_assoc();

        $conn->commit();
        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $comment['id'],
                'content' => $comment['content'],
                'username' => $comment['username'],
                'profile_picture' => $comment['profile_picture'],
                'created_at' => $comment['created_at']
            ]
        ]);
    } else {
        throw new Exception("Failed to add comment");
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 