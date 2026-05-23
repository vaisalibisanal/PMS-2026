<?php
// register.php - handles GET to show form and POST to create a new user

require_once './../mysql/connection.php'; // Include database connection and helper functions
require_once './../config.php'; // Include config


// Initialize variables for form values and errors
$name = $email = $password = $confirm_password = '';
$errors = [];

// If form submitted via POST, process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use $_POST to access form fields sent via POST
    $name = clean_input($_POST['name'] ?? ''); // Clean and assign name
    $email = clean_input($_POST['email'] ?? ''); // Clean and assign email
    $password = $_POST['password'] ?? ''; // Passwords are not escaped here
    $confirm_password = $_POST['confirm_password'] ?? ''; // Confirmation

    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password required (min 6 chars).';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // If no validation errors, proceed to check uniqueness and insert
    if (empty($errors)) {
        // Prepared statement to check whether email already exists using MySQLi
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->fetch_assoc()) {
            $errors[] = 'Email already registered.';
        } else {
            // Hash the password securely
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Prepared statement to insert new user safely using MySQLi
            $insert = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $insert->bind_param('sss', $name, $email, $password_hash);
            
            if ($insert->execute()) {
                // Get new user id
                $user_id = $conn->insert_id;

                // Set session to log in the user
                $_SESSION['user_id'] = $user_id; // Store user id in session
                $_SESSION['user_name'] = $name; // Store user name in session

                // Set a cookie as an example (remember me style) for 1 hour
                setcookie('user_email', $email, time() + 3600, '/', '', false, true); // HttpOnly

                // Redirect to dashboard after successful registration
                header('Location: dashboard.php');
                exit; // Stop script after redirect
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
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
    <title>Register - MiniProject</title>
    <link rel="stylesheet" href="./../style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Register</h1>
            <p>Create a new account to get started</p>
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

        <form action="./../app/register.php" method="post">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Min. 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter your password" required>
            </div>

            <button type="submit">✅ Register Account</button>
        </form>

        <div class="links">
            <p style="margin-bottom: 0; color: #666;">Already have an account?</p>
            <a href="./../app/login.php">Login here →</a>
            <span style="color: #ddd;"> | </span>
            <a href="./../app/index.php">← Back Home</a>
        </div>
    </div>
</body>
</html>
