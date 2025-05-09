<?php
session_start();
require_once __DIR__ . '/../config.php';

// Cek session USER, bukan admin
if (!isset($_SESSION['user_id'])) {
 $_SESSION['error'] = "Anda harus login sebagai user terlebih dahulu";
 header("Location: " . BASE_URL . "views/auth/login.php");
 exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Document</title>
</head>

<body>
 <h1>
  <a href="<?php echo BASE_URL; ?>views/auth/logout.php">Logout</a>
  HALLO DOWNLOAD
 </h1>
</body>

</html>