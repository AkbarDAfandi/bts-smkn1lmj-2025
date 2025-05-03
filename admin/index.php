<?php
session_start();
require_once __DIR__ . '/../config.php';


if (isset($_SESSION['admin_id'])) {
 header("Location: " . BASE_URL . "admin/views/dashboard.php");
 exit();
}

?> 


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="public/css/index.css ">
</head>

<body>
    <div class="login-container">
        <div class="right-side">
            <div class="admin-text"></div>
            <h2>Sign In</h2>
            <p>Enter your credentials to access your dashboard.</p>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="error">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<p class="success">' . $_SESSION['success'] . '</p>';
                unset($_SESSION['success']);
            }
            ?>


            <form action="auth/proses_login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" placeholder="Your Username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Your Password" name="password" required>
                </div>
                <button type="submit" class="btn-sign-in">Sign In</button>
            </form>
        </div>
    </div>
</body>

</html>