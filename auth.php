<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Database connection
    $host = 'localhost';
    $dbname = 'Expressify_db';
    $username = 'root';
    $password_db = 'root1';
    
    try {
        $conn = new mysqli($host, $username, $password_db, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        
        if ($_POST['action'] === 'register') {
            $username = $_POST['username'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                header("Location: auth.php");
                exit;
            } else {
                $error = "Registration failed!";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result && password_verify($password, $result['password'])) {
                $_SESSION['user_id'] = $result['id'];
                header("Location: home.php");
                exit;
            } else {
                $error = "Invalid login!";
            }
            $stmt->close();
        }
        
        $conn->close();
    } catch (Exception $e) {
        $error = "Database error occurred. Please try again.";
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expressify - Login/Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dark-theme">
    <nav class="landing-navbar">
        <div class="nav-brand">
            <a href="index.php">
                <img src="logo.png" alt="Expressify Logo" class="logo">
                <h1>Expressify</h1>
            </a>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-box" data-form="login" id="auth-box">
            <div class="auth-header">
                <h2 id="auth-title">Login to Expressify</h2>
                <p id="auth-subtitle">Welcome back! Please login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div id="register-fields" style="display:none;">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i></label>
                        <input type="text" id="username" name="username" placeholder="Username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i></label>
                    <input type="email" id="email" name="email" required placeholder="Email">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i></label>
                    <input type="password" id="password" name="password" required placeholder="Password">
                </div>

                <input type="hidden" id="action" name="action" value="login">
                <button type="submit" class="auth-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    <span id="submit-text">Login</span>
                </button>
            </form>

            <div class="auth-switch">
                <p id="switch-text">Don't have an account?</p>
                <button onclick="toggleAuth()" class="switch-btn" id="switch-btn">Register</button>
            </div>
        </div>
    </div>

    <script>
    function toggleAuth() {
        const registerFields = document.getElementById('register-fields');
        const action = document.getElementById('action');
        const authTitle = document.getElementById('auth-title');
        const authSubtitle = document.getElementById('auth-subtitle');
        const submitText = document.getElementById('submit-text');
        const switchText = document.getElementById('switch-text');
        const switchBtn = document.getElementById('switch-btn');
        const authBox = document.getElementById('auth-box');
        
        if (registerFields.style.display === 'none') {
            registerFields.style.display = 'block';
            action.value = 'register';
            authTitle.textContent = 'Create Account';
            authSubtitle.textContent = 'Join our community today!';
            submitText.textContent = 'Register';
            switchText.textContent = 'Already have an account?';
            switchBtn.textContent = 'Login';
            authBox.dataset.form = 'register';
        } else {
            registerFields.style.display = 'none';
            action.value = 'login';
            authTitle.textContent = 'Login to Expressify';
            authSubtitle.textContent = 'Welcome back! Please login to your account';
            submitText.textContent = 'Login';
            switchText.textContent = "Don't have an account?";
            switchBtn.textContent = 'Register';
            authBox.dataset.form = 'login';
        }
    }
    </script>
</body>
</html>
