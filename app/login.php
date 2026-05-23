<?php
// login.php - shows login form on GET and authenticates on POST

require_once './../config.php'; // Include DB connection and helpers
require_once './../mysql/connection.php'; // Include DB connection and helpers

$email = $password = '';
$errors = [];

// Process POST when login form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? ''); // Read email from POST
    $password = $_POST['password'] ?? ''; // Read password from POST
    $remember = isset($_POST['remember']); // Checkbox for cookie

    // Validate
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email required.';
    }
    if (empty($password)) {
        $errors[] = 'Password required.';
    }
    //echo '<pre> Hi....';
    //print_r($conn); // Debug: check DB connection

    if (empty($errors)) {
        // Prepared statement to fetch user by email using MySQLi
        $stmt = $conn->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Password matches - set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // If remember me checked, set a cookie for 7 days as example
            if ($remember) {
                setcookie('remember_user', $user['id'], time() + (7 * 24 * 60 * 60), '/', '', false, true);
            }

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - MiniProject</title>
    <link rel="stylesheet" href="./../style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Login</h1>
            <p>Welcome back! Please login to your account</p>
        </div>

        <?php if (!empty($errors)) : ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out') : ?>
            <div class="success">
                ✅ You have been successfully logged out.
            </div>
        <?php endif; ?>

        <form action="./../app/login.php" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin: 0; font-weight: 500;">Remember me for 7 days</label>
            </div>

            <button type="submit">🚀 Login</button>
        </form>

        <div class="links">
            <p style="margin-bottom: 0; color: #666;">Don't have an account?</p>
            <a href="./../app/register.php">Register here →</a>
            <span style="color: #ddd;"> | </span>
            <a href="./../app/index.php">← Back Home</a>
        </div>
    </div>
</body>
</html>
