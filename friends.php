<?php
require 'session.php';
require 'config.php';
require 'theme.php';

$user_id = $_SESSION['user_id'];

// Get all users except current user, along with their friend request status
$query = "
    SELECT 
        u.id,
        u.username,
        u.profile_picture,
        u.bio,
        CASE
            WHEN fr1.status = 'pending' AND fr1.sender_id = ? THEN 'pending_sent'
            WHEN fr1.status = 'pending' AND fr1.receiver_id = ? THEN 'pending_received'
            WHEN fr1.status = 'accepted' THEN 'friends'
            ELSE 'none'
        END as friendship_status
    FROM users u
    LEFT JOIN friend_requests fr1 ON 
        (fr1.sender_id = u.id AND fr1.receiver_id = ?) OR 
        (fr1.sender_id = ? AND fr1.receiver_id = u.id)
    WHERE u.id != ?
    ORDER BY u.username";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html data-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
    <title>Friends - Expressify</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <nav class="navbar">
        <div class="nav-brand">
            <h2>Expressify</h2>
        </div>
        <div class="nav-links">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i> Home</a>
            <a href="posts.php" class="nav-item"><i class="fas fa-pen"></i> Create Post</a>
            <a href="explore.php" class="nav-item"><i class="fas fa-compass"></i> Explore</a>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i> My Profile</a>
            <a href="friends.php" class="nav-item active"><i class="fas fa-users"></i> Friends</a>
            <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

    <div class="content">
        <div class="friends-section">
            <h2><i class="fas fa-users"></i> Connect with Others</h2>
            <div class="users-grid">
                <?php while ($user = $result->fetch_assoc()) { ?>
                    <div class="user-card">
                        <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($user['username']); ?>" 
                             class="user-avatar">
                        <div class="user-info">
                            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                            <?php if (!empty($user['bio'])) { ?>
                                <p class="user-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                            <?php } ?>
                            <div class="friend-actions" data-user-id="<?php echo $user['id']; ?>">
                                <?php switch($user['friendship_status']) {
                                    case 'none': ?>
                                        <button class="friend-btn add-friend" onclick="handleFriendRequest(<?php echo $user['id']; ?>, 'send')">
                                            <i class="fas fa-user-plus"></i> Add Friend
                                        </button>
                                        <?php break;
                                    case 'pending_sent': ?>
                                        <button class="friend-btn cancel-request" onclick="handleFriendRequest(<?php echo $user['id']; ?>, 'cancel')">
                                            <i class="fas fa-user-clock"></i> Cancel Request
                                        </button>
                                        <?php break;
                                    case 'pending_received': ?>
                                        <div class="friend-request-actions">
                                            <button class="friend-btn accept" onclick="handleFriendRequest(<?php echo $user['id']; ?>, 'accept')">
                                                <i class="fas fa-check"></i> Accept
                                            </button>
                                            <button class="friend-btn reject" onclick="handleFriendRequest(<?php echo $user['id']; ?>, 'cancel')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                        <?php break;
                                    case 'friends': ?>
                                        <button class="friend-btn unfriend" onclick="handleFriendRequest(<?php echo $user['id']; ?>, 'unfriend')">
                                            <i class="fas fa-user-minus"></i> Unfriend
                                        </button>
                                        <?php break;
                                } ?>
                            </div>
                            <a href="profile.php?id=<?php echo $user['id']; ?>" class="view-profile-btn">
                                <i class="fas fa-user"></i> View Profile
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('.nav-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

async function handleFriendRequest(userId, action) {
    try {
        const response = await fetch('api/friend_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                action: action
            })
        });

        const data = await response.json();
        
        if (data.success) {
            const friendActions = document.querySelector(`.friend-actions[data-user-id="${userId}"]`);
            let newButton = '';
            
            switch(data.newStatus) {
                case 'none':
                    newButton = `
                        <button class="friend-btn add-friend" onclick="handleFriendRequest(${userId}, 'send')">
                            <i class="fas fa-user-plus"></i> Add Friend
                        </button>`;
                    break;
                case 'pending_sent':
                    newButton = `
                        <button class="friend-btn cancel-request" onclick="handleFriendRequest(${userId}, 'cancel')">
                            <i class="fas fa-user-clock"></i> Cancel Request
                        </button>`;
                    break;
                case 'friends':
                    newButton = `
                        <button class="friend-btn unfriend" onclick="handleFriendRequest(${userId}, 'unfriend')">
                            <i class="fas fa-user-minus"></i> Unfriend
                        </button>`;
                    break;
            }
            
            friendActions.innerHTML = newButton;
            
            // Show notification
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
</body>
</html>
