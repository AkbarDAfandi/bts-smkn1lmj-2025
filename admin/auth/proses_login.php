<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Prevent caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Metode request tidak valid";
    header("Location: index.php");
    exit();
}

// Validate CSRF token if implemented
/*
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Token CSRF tidak valid";
    header("Location: index.php");
    exit();
}
*/

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Basic input validation
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Username dan password harus diisi";
    header("Location: index.php");
    exit();
}

// Additional username validation
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $_SESSION['error'] = "Username hanya boleh mengandung huruf, angka, dan underscore (3-20 karakter)";
    header("Location: index.php");
    exit();
}

// Initialize login attempt tracking if not exists
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_login_attempt'] = time();
}

// Implement basic brute force protection
if ($_SESSION['login_attempts'] >= 5) {
    $wait_time = 300 - (time() - $_SESSION['last_login_attempt']);
    if ($wait_time > 0) {
        $_SESSION['error'] = "Terlalu banyak percobaan login. Silakan coba lagi dalam " . ceil($wait_time/60) . " menit";
        header("Location: index.php");
        exit();
    } else {
        // Reset attempt counter after wait period
        $_SESSION['login_attempts'] = 0;
    }
}

try {
    // Prepare SQL statement with parameterized query
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify user exists and password is correct
    if ($user) {
        // Verify password (using password_verify for hashed passwords)
        if (password_verify($password, $user['password'])) {
            // Check if account is active
            if (isset($user['is_active']) && $user['is_active'] == 0) {
                $_SESSION['error'] = "Akun ini dinonaktifkan. Silakan hubungi administrator.";
                header("Location: index.php");
                exit();
            }
            
            // Check if user has admin role
            if ($user['role'] !== 'admin') {
                $_SESSION['error'] = "Anda tidak memiliki akses sebagai admin";
                header("Location: index.php");
                exit();
            }
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Clear any existing session data
            $_SESSION = [];
            
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['name'] ?? $user['username']; // Use name if available
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['is_super_admin'] = ($user['id'] == 1); // First admin is super admin
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            // Reset login attempts on success
            $_SESSION['login_attempts'] = 0;
            
            // Set secure session cookie parameters
            $cookieParams = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                [
                    'lifetime' => 86400, // 1 day
                    'path' => '/',
                    'domain' => $cookieParams['domain'],
                    'secure' => isset($_SERVER['HTTPS']), // Secure if HTTPS
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
            
            // Log successful login (optional)
            error_log("Admin login successful: " . $user['username'] . " - IP: " . $_SERVER['REMOTE_ADDR']);
            
            // Redirect to dashboard
            header("Location: ../views/dashboard.php");
            exit();
        } else {
            // Increment failed login attempts
            $_SESSION['login_attempts']++;
            $_SESSION['last_login_attempt'] = time();
            
            // Generic error message (don't reveal which part was wrong)
            $_SESSION['error'] = "Username atau password salah";
            error_log("Failed login attempt for username: " . $username . " - IP: " . $_SERVER['REMOTE_ADDR']);
            
            header("Location: index.php");
            exit();
        }
    } else {
        // Username not found
        $_SESSION['login_attempts']++;
        $_SESSION['last_login_attempt'] = time();
        
        // Generic error message
        $_SESSION['error'] = "Username atau password salah";
        error_log("Failed login attempt for non-existent username: " . $username . " - IP: " . $_SERVER['REMOTE_ADDR']);
        
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    // Log the detailed error
    error_log("Database error during login: " . $e->getMessage() . " - IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Generic error message for user
    $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    header("Location: index.php");
    exit();
}