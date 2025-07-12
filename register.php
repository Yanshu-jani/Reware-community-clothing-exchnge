<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ? OR username = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$email, $username]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Email or username already exists';
        } else {
            // Hash password and create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([$username, $email, $hashed_password])) {
                $success = 'Account created successfully! Please login.';
                // Redirect to login after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ReWear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            color: #2ecc71;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2ecc71;
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-footer a {
            color: #2ecc71;
            text-decoration: none;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #2ecc71; }
    </style>
</head>
<style>
.auth-bg {
  min-height: 100vh !important;
  background-color: #1c5c4b !important;
  background-image:
    repeating-linear-gradient(120deg, rgba(255,255,255,0.05) 0px, rgba(255,255,255,0.05) 2px, transparent 2px, transparent 60px),
    repeating-linear-gradient(60deg, rgba(255,255,255,0.05) 0px, rgba(255,255,255,0.05) 2px, transparent 2px, transparent 60px),
    repeating-linear-gradient(0deg, rgba(255,255,255,0.05) 0px, rgba(255,255,255,0.05) 2px, transparent 2px, transparent 60px) !important;
  background-size: 70px 120px !important;
}
</style>
<body class="auth-bg">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-recycle"></i> ReWear</h1>
                <p>Join our sustainable fashion community!</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php" id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-submit">Create Account</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="index.php">Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let message = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength < 2) {
                message = 'Weak password';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength < 4) {
                message = 'Medium strength password';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                message = 'Strong password';
                strengthDiv.className = 'password-strength strength-strong';
            }
            
            strengthDiv.textContent = message;
        });
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                ReWear.showNotification('Please fill in all fields', 'error');
                return false;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                ReWear.showNotification('Please enter a valid email address', 'error');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                ReWear.showNotification('Passwords do not match', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                ReWear.showNotification('Password must be at least 6 characters long', 'error');
                return false;
            }
        });
    </script>
</body>
</html> 