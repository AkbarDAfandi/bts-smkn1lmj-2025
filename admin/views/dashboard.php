<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: admin/index.php");
    exit();
}

// Get statistics from database
try {
    // Total books count
    $stmt = $pdo->query("SELECT COUNT(*) as total_books FROM books");
    $totalBooks = $stmt->fetch(PDO::FETCH_ASSOC)['total_books'];

    // Total categories count
    $stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM categories");
    $totalCategories = $stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

    // Total academic years count
    $stmt = $pdo->query("SELECT COUNT(*) as total_years FROM tahun_akademik");
    $totalYears = $stmt->fetch(PDO::FETCH_ASSOC)['total_years'];
} catch (PDOException $e) {
    // Handle error if needed
    $totalBooks = 0;
    $totalCategories = 0;
    $totalYears = 0;
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../public/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <style>
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 300;
            src: url('../public/fonts/poppins/Poppins-Light.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            src: url('../public/fonts/poppins/Poppins-Regular.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 500;
            src: url('../public/fonts/poppins/Poppins-Medium.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 600;
            src: url('../public/fonts/poppins/Poppins-SemiBold.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700;
            src: url('../public/fonts/poppins/Poppins-Bold.ttf') format('truetype');
        }

        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>

    <?php include '../views/layout/sidebar.php' ?>

    <div class="main-content">
        <div class="header">
            <h2 class="greeting">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</h2>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalBooks ?></h3>
                    <p>Total Buku</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalCategories ?></h3>
                    <p>Kategori Buku</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalYears ?></h3>
                    <p>Tahun Akademik</p>
                </div>
            </div>

        </div>

        <div class="chart">
            <div class="chart-header">

            </div>
            <div class="location-chart">
                <?php
                // Get book distribution by category
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $colors = ['blue', 'green', 'orange', 'purple', 'red'];
                $i = 0;
                foreach ($categories as $category):
                    $percentage = $totalBooks > 0 ? round(($category['book_count'] / $totalBooks) * 100) : 0;
                ?>
                    <div class="progress-item">
                        <div class="progress-info">
                            <span><?= htmlspecialchars($category['name']) ?></span>
                            <span><?= $category['book_count'] ?></span>
                        </div>

                    </div>
                <?php

                endforeach;
                ?>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart">
                <div class="chart-header">
                    <h3>Buku Terbaru</h3>
                </div>
                <div class="recent-books">
                    <?php
                    // Get recent books
                    $stmt = $pdo->query("
                        SELECT b.judul, c.name as category_name, b.created_at 
                        FROM books b
                        JOIN categories c ON b.category_id = c.id
                        ORDER BY b.created_at DESC 
                        LIMIT 5
                    ");
                    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($books)) {
                        echo "<p>Belum ada buku yang ditambahkan</p>";
                    } else {
                        foreach ($books as $book):
                    ?>
                            <div class="book-item">
                                <div class="book-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="book-info">
                                    <h4><?= htmlspecialchars($book['judul']) ?></h4>
                                    <p>Kategori: <?= htmlspecialchars($book['category_name']) ?></p>
                                    <span class="book-date">
                                        Ditambahkan: <?= date('d M Y', strtotime($book['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                    <?php
                        endforeach;
                    }
                    ?>
                </div>
            </div>

            <div class="chart">
                <div class="chart-header">
                    <h3>Buku per Tahun Akademik</h3>
                </div>
                <div class="year-stats">
                    <?php
                    // Get books by academic year
                    // Change this query:
                    $stmt = $pdo->query("
    SELECT t.tahun, COUNT(b.id) as book_count 
    FROM tahun_akademik t
    LEFT JOIN books b ON t.id = b.tahun_akademik_id 
    GROUP BY t.id, t.tahun 
    ORDER BY t.tahun DESC
");
                    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($years)) {
                        echo "<p>Belum ada data tahun akademik</p>";
                    } else {
                        foreach ($years as $year):
                    ?>
                            <div class="year-item">
                                <div class="year-info">
                                    <span>Tahun <?= htmlspecialchars($year['tahun']) ?></span>
                                    <span><?= $year['book_count'] ?> buku</span>
                                </div>
                                <div class="year-bar">
                                    <div class="year-fill" style="width: <?= $totalBooks > 0 ? round(($year['book_count'] / $totalBooks) * 100) : 0 ?>%"></div>
                                </div>
                            </div>
                    <?php
                        endforeach;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>