<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once 'function_year.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: index.php");
    exit();
}

// Ambil data tahun dari database
$availableYears = getAvailableYears($pdo);
sort($availableYears);

// Tangani request delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $yearToDelete = $_GET['delete'];

    if (deleteAcademicYear($pdo, $yearToDelete)) {
        $_SESSION['success'] = "Tahun akademik $yearToDelete berhasil dihapus";
        header("Location: empty.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menghapus tahun akademik";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Year Selection</title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/tahun/empty.css">

</head>

<body>
    <?php include '../../views/layout/sidebar.php' ?>
    <div class="main-content">
        <div class="header">
            <h2 class="greeting">Select Year</h2>
        </div>

        <div class="year-selection-container">
            <div class="title-container">
                <h2>Please select a year to view student annual books</h2>
                <button class="btn-new-year" onclick="window.location.href='add_new_year.php'">
                    <i class="fa fa-plus"></i>
                    New Year
                </button>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <span><?= $_SESSION['error'] ?></span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <span><?= $_SESSION['success'] ?></span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="year-grid">
                <?php foreach ($availableYears as $year): ?>
                    <div class="year-card">
                        <button class="delete-btn" onclick="event.stopPropagation(); confirmDelete(<?= $year ?>)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="year-card-content" onclick="window.location.href='<?= getYearPagePath($year) ?>?tahun=<?= $year ?>'">
                            <h3><?= $year ?></h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(year) {
            if (confirm(`Apakah Anda yakin ingin menghapus tahun ${year}?`)) {
                window.location.href = `empty.php?delete=${year}`;
            }
        }

        // Auto close alert setelah 5 detik
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>