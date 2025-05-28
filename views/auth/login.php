<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Cek jika user sudah login (baik admin atau user biasa)
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (isset($_SESSION['user_id'])) {
    header("Location: download/download.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
</head>

<body>
    <div class="login-container">
        <div class="right-side">
            <div class="admin-text"></div>
            <h2>Sign In</h2>
            <p>Login gunakan NIS anda</p>

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

            <form action="proses_login.php" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="password">NIS</label>
                    <input type="password" id="password" placeholder="Your Password" name="password" required>
                </div>
                <button type="submit" class="btn-sign-in">Sign In</button>
            </form>
        </div>
    </div>

    <script>
        function validateForm(){
            let password = document.getElementById('password');

            let trimmedPassword = password.value.trim();

            passwordInput.value = trimmmedPassword;

            return true;
        }
    </script>
</body>
</html>