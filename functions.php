<?php
function createNotification($conn, $user_id, $type, $message, $related_user_id = null, $post_id = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, related_user_id, post_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $type, $message, $related_user_id, $post_id);
    return $stmt->execute();
}

function timeAgo($timestamp) {
    $seconds = time() - strtotime($timestamp);
    
    $intervals = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($intervals as $seconds_in_interval => $interval) {
        $count = floor($seconds / $seconds_in_interval);
        if ($count > 0) {
            if ($count == 1) {
                return "1 $interval ago";
            } else {
                return "$count {$interval}s ago";
            }
        }
    }
    
    return "just now";
} 