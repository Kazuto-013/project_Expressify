<?php
require 'session.php';
require 'config.php';
$user_id = $_SESSION['user_id'];

// Get unread notifications count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];

// Get posts with media, like counts and check if user liked each post
$result = $conn->query("
    SELECT 
        posts.*,
        users.username,
        users.profile_picture,
        media.file_path,
        media.file_type,
        COUNT(DISTINCT likes.id) as like_count,
        COUNT(DISTINCT comments.id) as comment_count,
        MAX(CASE WHEN likes.user_id = $user_id THEN 1 ELSE 0 END) as user_liked
    FROM posts 
    JOIN users ON posts.user_id = users.id
    LEFT JOIN media ON posts.media_id = media.id
    LEFT JOIN likes ON posts.id = likes.post_id
    LEFT JOIN comments ON posts.id = comments.post_id
    GROUP BY posts.id
    ORDER BY posts.created_at DESC
");

$theme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html data-theme="<?php echo htmlspecialchars($theme ?? 'light'); ?>">
<head>
    <title>Home - Expressify</title>
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
            <a href="home.php" class="nav-item active"><i class="fas fa-home"></i> Home</a>
            <a href="posts.php" class="nav-item"><i class="fas fa-pen"></i> Create Post</a>
            <a href="explore.php" class="nav-item"><i class="fas fa-compass"></i> Explore</a>
            <div class="nav-item notifications-dropdown">
                <button class="notifications-btn">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </button>
                <div class="notifications-menu">
                    <div class="notifications-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read">Mark all as read</button>
                    </div>
                    <div class="notifications-list">
                        <!-- Notifications will be loaded here -->
                    </div>
                    <div class="notifications-footer">
                        <a href="notifications.php" class="view-all">View All Notifications</a>
                    </div>
                </div>
            </div>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i> My Profile</a>
            <a href="friends.php" class="nav-item"><i class="fas fa-users"></i> Friends</a>
            <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>
    
    <div class="content">
        <h3>Recent Posts</h3>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="post" data-post-id="<?php echo $row['id']; ?>">
                <div class="post-header">
                    <div class="post-user">
                        <img src="<?php echo htmlspecialchars($row['profile_picture'] ?? 'assets/default-avatar.png'); ?>" 
                             alt="<?php echo htmlspecialchars($row['username'] ?? ''); ?>" class="post-avatar">
                        <div class="post-user-info">
                            <strong><?php echo htmlspecialchars($row['username'] ?? ''); ?></strong>
                            <span class="post-date"><?php echo date('M d, Y', strtotime($row['created_at'] ?? '')); ?></span>
                        </div>
                    </div>
                    <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                        <div class="post-actions-menu">
                            <button class="delete-post-btn" data-post-id="<?php echo $row['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="post-content"><?php echo htmlspecialchars($row['content'] ?? ''); ?></p>
                <?php if (!empty($row['file_path'])) : ?>
                    <div class="post-media">
                        <?php if (strpos($row['file_type'] ?? '', 'image/') !== false || in_array($row['file_type'] ?? '', ['jpg', 'jpeg', 'png', 'gif'])) : ?>
                            <img src="<?php echo htmlspecialchars($row['file_path'] ?? ''); ?>" alt="Post image" class="post-image">
                        <?php elseif (strpos($row['file_type'] ?? '', 'video/') !== false || in_array($row['file_type'] ?? '', ['mp4', 'mov'])) : ?>
                            <video controls class="post-video">
                                <source src="<?php echo htmlspecialchars($row['file_path'] ?? ''); ?>" type="video/<?php echo htmlspecialchars($row['file_type'] ?? ''); ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="post-actions">
                    <button class="action-btn like-btn <?php echo $row['user_liked'] ? 'liked' : ''; ?>" 
                            data-post-id="<?php echo $row['id']; ?>"
                            data-liked="<?php echo $row['user_liked']; ?>">
                        <i class="<?php echo $row['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
                        <span class="like-count"><?php echo $row['like_count']; ?></span>
                    </button>
                    <button class="action-btn comment-btn" data-post-id="<?php echo $row['id']; ?>">
                        <i class="far fa-comment"></i>
                        <span class="comment-count"><?php echo $row['comment_count']; ?></span>
                    </button>
                </div>
                <div class="comments-section" id="comments-<?php echo $row['id']; ?>" style="display: none;">
                    <form class="comment-form" data-post-id="<?php echo $row['id']; ?>">
                        <input type="text" class="comment-input" placeholder="Write a comment...">
                        <button type="submit" class="comment-submit">Post</button>
                    </form>
                    <div class="comments-list" id="comments-list-<?php echo $row['id']; ?>">
                        <!-- Comments will be loaded here -->
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<style>
.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px;
}

.post-actions-menu {
    position: relative;
}

.delete-post-btn {
    background: none;
    border: none;
    color: #ff4444;
    cursor: pointer;
    padding: 5px;
    transition: color 0.2s;
}

.delete-post-btn:hover {
    color: #ff0000;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background-color: var(--bg-color);
    margin: 15% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 500px;
}

.modal-header {
    margin-bottom: 20px;
}

.modal-footer {
    margin-top: 20px;
    text-align: right;
}

.modal-btn {
    padding: 8px 16px;
    margin-left: 10px;
    border-radius: 4px;
    cursor: pointer;
}

.modal-btn.cancel {
    background-color: var(--bg-color);
    color: var(--text-color);
    border: 1px solid var(--border-color);
}

.modal-btn.delete {
    background-color: #ff4444;
    color: white;
    border: none;
}

.modal-btn:hover {
    opacity: 0.8;
}
</style>

<!-- Add this at the bottom of the body tag -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Post</h3>
        </div>
        <p>Are you sure you want to delete this post? This action cannot be undone.</p>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="modal-btn delete" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
document.querySelector('.nav-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

// Like functionality
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', async function() {
        const postId = this.dataset.postId;
        const isLiked = this.dataset.liked === '1';
        
        try {
            const response = await fetch('like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ post_id: postId })
            });
            
            if (response.ok) {
                const data = await response.json();
                this.dataset.liked = isLiked ? '0' : '1';
                this.querySelector('i').className = isLiked ? 'far fa-heart' : 'fas fa-heart';
                this.querySelector('.like-count').textContent = data.like_count;
                this.classList.toggle('liked');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

// Comment functionality
document.querySelectorAll('.comment-btn').forEach(button => {
    button.addEventListener('click', function() {
        const postId = this.dataset.postId;
        const commentsSection = document.getElementById(`comments-${postId}`);
        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
        
        if (commentsSection.style.display === 'block') {
            loadComments(postId);
        }
    });
});

// Handle comment submission
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const postId = this.dataset.postId;
        const input = this.querySelector('.comment-input');
        const comment = input.value.trim();
        
        if (comment) {
            try {
                const response = await fetch('add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId,
                        content: comment
                    })
                });
                
                if (response.ok) {
                    input.value = '';
                    await loadComments(postId);
                    // Update comment count
                    const countElement = document.querySelector(`.comment-btn[data-post-id="${postId}"] .comment-count`);
                    countElement.textContent = parseInt(countElement.textContent) + 1;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });
});

async function loadComments(postId) {
    try {
        const response = await fetch(`get_comments.php?post_id=${postId}`);
        if (response.ok) {
            const comments = await response.json();
            const commentsList = document.getElementById(`comments-list-${postId}`);
            commentsList.innerHTML = comments.map(comment => `
                <div class="comment">
                    <strong>${comment.username}</strong>
                    <p>${comment.content}</p>
                    <small>${comment.created_at}</small>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Notifications functionality
document.addEventListener('DOMContentLoaded', function() {
    const notificationsBtn = document.querySelector('.notifications-btn');
    const notificationsMenu = document.querySelector('.notifications-menu');
    const notificationsList = document.querySelector('.notifications-list');
    const markAllReadBtn = document.querySelector('.mark-all-read');
    
    // Toggle notifications menu
    notificationsBtn.addEventListener('click', function(e) {
        e.preventDefault();
        notificationsMenu.classList.toggle('show');
        if (notificationsMenu.classList.contains('show')) {
            loadNotifications();
        }
    });
    
    // Close notifications menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notifications-dropdown')) {
            notificationsMenu.classList.remove('show');
        }
    });
    
    // Load notifications
    async function loadNotifications() {
        try {
            const response = await fetch('api/notifications.php');
            const data = await response.json();
            
            if (data.notifications) {
                notificationsList.innerHTML = data.notifications.map(notification => `
                    <div class="notification-item ${notification.is_read ? '' : 'unread'}" 
                         data-notification-id="${notification.id}">
                        <img src="${notification.profile_picture || 'assets/default-avatar.png'}" 
                             alt="Profile picture" class="notification-avatar">
                        <div class="notification-content">
                            <p>${notification.content}</p>
                            <small>${timeAgo(new Date(notification.created_at))}</small>
                        </div>
                        ${!notification.is_read ? '<div class="notification-status"></div>' : ''}
                    </div>
                `).join('') || '<div class="no-notifications">No notifications</div>';
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            notificationsList.innerHTML = '<div class="error">Failed to load notifications</div>';
        }
    }
    
    // Mark all notifications as read
    markAllReadBtn.addEventListener('click', async function() {
        try {
            const response = await fetch('api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            });
            
            if (response.ok) {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                    item.querySelector('.notification-status')?.remove();
                });
                document.querySelector('.notification-badge')?.remove();
            }
        } catch (error) {
            console.error('Error marking notifications as read:', error);
        }
    });
    
    // Mark individual notification as read
    notificationsList.addEventListener('click', async function(e) {
        const notificationItem = e.target.closest('.notification-item');
        if (notificationItem && !notificationItem.classList.contains('read')) {
            const notificationId = notificationItem.dataset.notificationId;
            
            try {
                const response = await fetch('api/notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_read&notification_id=${notificationId}`
                });
                
                if (response.ok) {
                    notificationItem.classList.remove('unread');
                    notificationItem.querySelector('.notification-status')?.remove();
                    
                    // Update badge count
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        const count = parseInt(badge.textContent) - 1;
                        if (count <= 0) {
                            badge.remove();
                        } else {
                            badge.textContent = count;
                        }
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }
    });
});

// Helper function for time ago format
function timeAgo(timestamp) {
    const seconds = Math.floor((new Date() - new Date(timestamp)) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval > 1) return interval + ' years ago';
    
    interval = Math.floor(seconds / 2592000);
    if (interval > 1) return interval + ' months ago';
    
    interval = Math.floor(seconds / 86400);
    if (interval > 1) return interval + ' days ago';
    
    interval = Math.floor(seconds / 3600);
    if (interval > 1) return interval + ' hours ago';
    
    interval = Math.floor(seconds / 60);
    if (interval > 1) return interval + ' minutes ago';
    
    return 'just now';
}

// Delete post functionality
let currentPostId = null;
const deleteModal = document.getElementById('deleteModal');

document.querySelectorAll('.delete-post-btn').forEach(button => {
    button.addEventListener('click', function() {
        currentPostId = this.dataset.postId;
        deleteModal.style.display = 'block';
    });
});

function closeDeleteModal() {
    deleteModal.style.display = 'none';
    currentPostId = null;
}

async function confirmDelete() {
    if (!currentPostId) return;
    
    try {
        const formData = new FormData();
        formData.append('post_id', currentPostId);
        
        const response = await fetch('api/delete_post.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Remove the post from the DOM
            const post = document.querySelector(`.post[data-post-id="${currentPostId}"]`);
            post.remove();
            closeDeleteModal();
        } else {
            alert(data.error || 'Failed to delete post');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the post');
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
});
</script>
</body>
</html>
