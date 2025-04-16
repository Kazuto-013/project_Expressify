<?php
require 'session.php';
require 'config.php';
require 'theme.php';

$search = isset($_GET['q']) ? $_GET['q'] : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'all';

$user_id = $_SESSION['user_id'];

// Get unread notifications count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];

// Search users
$users_query = "SELECT id, username, profile_picture, bio FROM users 
                WHERE username LIKE CONCAT('%', ?, '%') 
                OR email LIKE CONCAT('%', ?, '%')
                OR bio LIKE CONCAT('%', ?, '%')";

$users_stmt = $conn->prepare($users_query);
$users_stmt->bind_param("sss", $search, $search, $search);
$users_stmt->execute();
$users_result = $users_stmt->get_result();

// Search posts with user information and media
$posts_query = "SELECT posts.*, users.username, users.profile_picture, 
                COUNT(DISTINCT likes.id) as likes_count, 
                COUNT(DISTINCT comments.id) as comments_count
                FROM posts 
                JOIN users ON posts.user_id = users.id
                LEFT JOIN likes ON posts.id = likes.post_id
                LEFT JOIN comments ON posts.id = comments.post_id
                WHERE posts.content LIKE CONCAT('%', ?, '%')
                OR users.username LIKE CONCAT('%', ?, '%')
                GROUP BY posts.id
                ORDER BY posts.created_at DESC";

$posts_stmt = $conn->prepare($posts_query);
$posts_stmt->bind_param("ss", $search, $search);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();
?>
<!DOCTYPE html>
<html data-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
    <title>Explore - Expressify</title>
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
            <a href="explore.php" class="nav-item active"><i class="fas fa-compass"></i> Explore</a>
            <a href="notifications.php" class="nav-item">
                <i class="fas fa-bell"></i> Notifications
                <?php if ($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
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
        <h2>Explore</h2>
        <form method="GET" class="search-form">
            <div class="search-container">
                <input type="text" name="q" placeholder="Search users, posts..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <select name="type" class="search-type">
                    <option value="all" <?php echo $search_type === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="users" <?php echo $search_type === 'users' ? 'selected' : ''; ?>>Users</option>
                    <option value="posts" <?php echo $search_type === 'posts' ? 'selected' : ''; ?>>Posts</option>
                </select>
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <?php if (!empty($search)) : ?>
            <?php if ($search_type === 'all' || $search_type === 'users') : ?>
                <div class="search-section">
                    <h3><i class="fas fa-users"></i> People</h3>
                    <div class="users-grid">
                        <?php while ($user = $users_result->fetch_assoc()) : ?>
                            <div class="user-card">
                                <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/default-avatar.png'; ?>" 
                                     alt="Profile Picture" class="user-avatar">
                                <div class="user-info">
                                    <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                    <?php if (!empty($user['bio'])) : ?>
                                        <p class="user-bio"><?php echo htmlspecialchars(substr($user['bio'], 0, 100)) . (strlen($user['bio']) > 100 ? '...' : ''); ?></p>
                                    <?php endif; ?>
                                    <a href="profile.php?id=<?php echo $user['id']; ?>" class="view-profile-btn">View Profile</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php if ($users_result->num_rows === 0) : ?>
                            <p class="no-results">No users found matching your search.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($search_type === 'all' || $search_type === 'posts') : ?>
                <div class="search-section">
                    <h3><i class="fas fa-file-alt"></i> Posts</h3>
                    <div class="posts-container">
                        <?php while ($post = $posts_result->fetch_assoc()) : ?>
                            <div class="post">
                                <div class="post-header">
                                    <div class="post-user">
                                        <img src="<?php echo !empty($post['profile_picture']) ? htmlspecialchars($post['profile_picture']) : 'assets/default-avatar.png'; ?>" 
                                             alt="Profile Picture" class="post-avatar">
                                        <div class="post-user-info">
                                            <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                                            <span class="post-date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="post-content"><?php echo htmlspecialchars($post['content']); ?></p>
                                <div class="post-stats">
                                    <span><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php if ($posts_result->num_rows === 0) : ?>
                            <p class="no-results">No posts found matching your search.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="explore-welcome">
                <i class="fas fa-search fa-3x"></i>
                <h3>Search for Users and Posts</h3>
                <p>Enter a search term to find users and posts on Expressify.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelector('.nav-toggle').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});
</script>
</body>
</html>
