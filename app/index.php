<?php
// index.php - simple project entry page with links to key pages
require_once './../config.php'; // start session and DB (safe to include)
require_once './../constants/constants.php'; // Include project constants

// If user is already logged in, send them to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= PROJECT_NAME?> - Home</title>
    <link rel="stylesheet" href="./../style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= PROJECT_NAME?></h1>
            <p>PHP Authentication & User Management System</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 25px; border-radius: 12px; margin-bottom: 30px; border-left: 5px solid #667eea;">
            <p style="margin: 0; text-align: left; color: #333; line-height: 1.8;">
                <strong style="color: #667eea; font-size: 15px;">✨ A Complete Authentication Demo</strong><br>
                Experience a fully functional user authentication system with secure login, registration, session management, and cookie-based "Remember Me" functionality.
            </p>
        </div>

        <!---div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
            <div style="background: #f0f4ff; padding: 15px; border-radius: 8px; border: 2px solid #667eea; text-align: center;">
                <div style="font-size: 28px; margin-bottom: 8px;">🔐</div>
                <p style="margin: 0; color: #333; font-weight: 600; font-size: 13px;">Secure Authentication</p>
                <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">Password hashing & validation</p>
            </div>
            <div style="background: #fff0f5; padding: 15px; border-radius: 8px; border: 2px solid #764ba2; text-align: center;">
                <div style="font-size: 28px; margin-bottom: 8px;">📊</div>
                <p style="margin: 0; color: #333; font-weight: 600; font-size: 13px;">Session Management</p>
                <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">Secure user sessions</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
            <div style="background: #f5fff0; padding: 15px; border-radius: 8px; border: 2px solid #27ae60; text-align: center;">
                <div style="font-size: 28px; margin-bottom: 8px;">💾</div>
                <p style="margin: 0; color: #333; font-weight: 600; font-size: 13px;">Database Ready</p>
                <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">MySQLi prepared statements</p>
            </div>
            <div style="background: #fffaf0; padding: 15px; border-radius: 8px; border: 2px solid #e67e22; text-align: center;">
                <div style="font-size: 28px; margin-bottom: 8px;">🍪</div>
                <p style="margin: 0; color: #333; font-weight: 600; font-size: 13px;">Remember Me</p>
                <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">Cookie-based persistence</p>
            </div>
        </div-->

        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 25px; text-align: center; border: 1px solid #eee;">
            <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
                <strong>🎯 Get Started:</strong> Choose an option below to continue
            </p>
        </div>

        <div class="nav-links" style="gap: 15px;">
            <a href="register.php" class="cta-button register-btn">
                <span class="cta-icon">📝</span>
                <span class="cta-text">
                    <strong>Create Account</strong>
                    <small>Join now</small>
                </span>
            </a>
            <a href="login.php" class="cta-button login-btn">
                <span class="cta-icon">🔐</span>
                <span class="cta-text">
                    <strong>Sign In</strong>
                    <small>Login here</small>
                </span>
            </a>
        </div>

        <div style="background: #f0f8ff; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #667eea; text-align: center;">
            <p style="margin: 0; font-size: 13px; color: #333;">
                <?php 
                   // require_once 'config.php';
                    if (!empty($_SESSION['user_id'])) {
                        echo '<strong style="color: #27ae60;">✅ You are logged in as: ' . htmlspecialchars($_SESSION['user_name']) . '</strong><br><small style="color: #999;"><a href="dashboard.php" style="color: #667eea; text-decoration: none;">Go to Dashboard →</a></small>';
                    } else {
                        echo '<strong style="color: #e74c3c;">❌ Not logged in</strong><br><small style="color: #999;">Sign in or register to access your dashboard</small>';
                    }
                ?>
            </p>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #999;">
            <p style="margin: 0;">🛠️ Built with PHP • MySQLi • Sessions & Cookies</p>
        </div>
    </div>
</body>
</html>