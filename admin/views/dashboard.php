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
    
    // Get book distribution by category for the chart
    $categoryStmt = $pdo->query("
        SELECT c.name, COUNT(b.id) as book_count 
        FROM categories c
        LEFT JOIN books b ON c.id = b.category_id 
        GROUP BY c.id, c.name
    ");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error if needed
    $totalBooks = 0;
    $totalCategories = 0;
    $totalYears = 0;
    $categories = [];
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
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .header {
            margin-bottom: 30px;
        }

        .greeting {
            font-size: 24px;
            color: #333;
        }

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: white;
        }

        .stat-icon.blue {
            background: #4285F4;
        }

        .stat-icon.green {
            background: #34A853;
        }

        .stat-icon.orange {
            background: #FBBC05;
        }

        .stat-icon.purple {
            background: #9B59B6;
        }

        .stat-icon.red {
            background: #EA4335;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .stat-info p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        /* Charts */
        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            margin-bottom: 20px;
        }

        .chart-header h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        /* Recent Books */
        .recent-books {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .book-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .book-item:last-child {
            border-bottom: none;
        }

        .book-icon {
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #4285F4;
        }

        .book-info {
            flex: 1;
        }

        .book-info h4 {
            margin: 0 0 5px;
            font-size: 15px;
            color: #333;
        }

        .book-info p {
            margin: 0 0 3px;
            font-size: 13px;
            color: #666;
        }

        .book-date {
            font-size: 12px;
            color: #999;
        }

        /* Year Stats */
        .year-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .year-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .year-info {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .year-bar {
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .year-fill {
            height: 100%;
            background: #4285F4;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* Category Distribution */
        .location-chart {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .progress-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .progress-bar {
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                grid-template-columns: 1fr;
            }
            
            .greeting {
                font-size: 20px;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .stat-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .book-item {
                flex-direction: column;
                text-align: center;
                padding: 15px 0;
            }
            
            .book-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
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

    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    mainContent.classList.toggle('sidebar-active');
                });
            }
        });
    </script>
</body>

</html>