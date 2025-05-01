<?php

session_start();
require_once __DIR__ . '/../../../config.php';
require_once 'function_year.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = $_POST['year'] ?? '';
    
    if (empty($year)) {
        $error = "Tahun tidak boleh kosong";
    } elseif (!is_numeric($year) || $year < 2000 || $year > 2100) {
        $error = "Tahun harus antara 2000 dan 2100";
    } else {
        if (addAcademicYear($pdo, $year)) {
            $_SESSION['success'] = "Tahun akademik $year berhasil ditambahkan";
            header("Location: empty.php");
            exit();
        } else {
            $error = "Tahun akademik $year sudah ada";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Year</title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/tahun/add_new_year.css">
  
</head>

<body>
    
<?php include '../../views/layout/sidebar.php' ?>

    <div class="main-content">
        <div class="header">
            <h2 class="greeting">Add New Year</h2>
        </div>

        <div class="container-wrapper">
            <div class="new-year-container">
                <h2 style="margin-bottom: 30px;">Add New Academic Year</h2>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <input type="number" id="year" name="year" min="2000" max="2100" required>
                        <?php if (isset($error)): ?>
                            <div class="error-message"><?= $error ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-submit">Add Year</button>
                    <button type="button" class="btn-cancel" onclick="window.location.href='empty.php'">Cancel</button>
                </form>
            </div>
        </div>

    </div>
</body>

</html>