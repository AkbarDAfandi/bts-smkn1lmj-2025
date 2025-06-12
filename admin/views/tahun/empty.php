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

// Ambil data tahun dari database dengan cover dan sambutan
$yearsData = getYearsWithDetails($pdo);

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

    <style>
        .year-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0;
            height: 100%;
        }

        .year-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .year-card-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .cover-container {
            position: relative;
            padding: 15px 15px 5px 15px;
        }

        .cover-image {
            width: 100%;
            height: 180px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            background: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cover-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .cover-image:hover img {
            transform: scale(1.05);
        }

        .cover-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .cover-overlay i {
            color: white;
            font-size: 1.5rem;
        }

        .cover-image:hover .cover-overlay {
            opacity: 1;
        }

        .no-cover {
            background: #f8f9fc;
            border: 2px dashed #e3e6f0;
        }

        .year-icon {
            font-size: 3rem;
            color: #4e73df;
        }

        .year-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .year-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 15px 0;
            text-align: center;
        }

        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px;
            background: #f8f9fc;
            border-radius: 6px;
        }

        .info-label {
            color: #4e73df;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-text {
            font-size: 0.9rem;
            color: #333;
        }

        .info-text.no-data {
            color: #6c757d;
            font-style: italic;
        }

        .cover-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            margin-top: 8px;
            background: #f8f9fc;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .btn-sambutan {
            background: none;
            border: 1px solid #4e73df;
            color: #4e73df;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-sambutan:hover {
            background: #4e73df;
            color: white;
        }

        .card-footer {
            margin-top: auto;
            padding-top: 15px;
            text-align: center;
        }

        .btn-view {
            background: #4e73df;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .btn-view:hover {
            background: #2e59d9;
            transform: translateX(5px);
        }

        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease;
        }

        .year-card:hover .delete-btn {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>

<body>
    <?php include '../../views/layout/sidebar.php' ?>
    <div class="main-content">
        <div class="header">
            <h2 class="greeting"><i class="fas fa-calendar"></i> Tahun Akademik</h2>
        </div>

        <div class="year-selection-container">
            <div class="title-container">
                <div class="title-wrapper">
                    <h2><i class="fas fa-info-circle"></i> Pilih Tahun Akademik</h2>
                    <p class="subtitle">Silakan pilih tahun akademik untuk melihat buku tahunan siswa</p>
                </div>
                <button class="btn-new-year" onclick="window.location.href='add_new_year.php'">
                    <i class="fas fa-plus"></i>
                    Tambah Tahun
                </button>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-content">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= $_SESSION['error'] ?></span>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <div class="alert-content">
                        <i class="fas fa-check-circle"></i>
                        <span><?= $_SESSION['success'] ?></span>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="year-grid">
                <?php if (empty($yearsData)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>Belum ada tahun akademik yang ditambahkan</p>

                    </div>
                <?php else: ?>
                    <?php foreach ($yearsData as $yearData): ?>
                        <div class="year-card">
                            <button class="delete-btn" onclick="event.stopPropagation(); confirmDelete(<?= $yearData['tahun'] ?>)" title="Hapus tahun <?= $yearData['tahun'] ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <div class="year-card-content" onclick="window.location.href='../books/year_books.php?tahun=<?= $yearData['tahun'] ?>'">
                                <div class="cover-container">
                                    <?php if (!empty($yearData['cover_path'])): ?>
                                        <div class="cover-image">
                                            <img src="/bts-smkn1lmj-2025/admin/<?= $yearData['cover_path'] ?>" alt="Cover <?= $yearData['tahun'] ?>">
                                            <div class="cover-overlay">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        </div>
                                        <div class="cover-info">
                                            <span class="info-label"><i class="fas fa-image"></i> Cover</span>
                                            <span class="info-text">Tersedia</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="cover-image no-cover">
                                            <i class="fas fa-calendar-alt year-icon"></i>
                                        </div>
                                        <div class="cover-info">
                                            <span class="info-label"><i class="fas fa-image"></i> Cover</span>
                                            <span class="info-text no-data">Belum ada</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="year-info">
                                    <h3 class="year-text"><?= $yearData['tahun'] ?></h3>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="fas fa-comment-alt"></i> Sambutan:
                                        </span>
                                        <?php if (!empty($yearData['sambutan'])): ?>
                                            <button class="btn-sambutan" onclick="event.stopPropagation(); showSambutan(<?= $yearData['tahun'] ?>, `<?= htmlspecialchars($yearData['sambutan'], ENT_QUOTES) ?>`)">
                                                <i class="fas fa-eye"></i> Lihat
                                            </button>
                                        <?php else: ?>
                                            <span class="info-text no-data">Belum ada</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn-view" title="Lihat detail">
                                            <i class="fas fa-arrow-right"></i> Lihat Detail
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Sambutan -->
    <div id="sambutanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-comment-alt"></i> Sambutan Tahun <span id="modalYear"></span></h3>
                <button class="modal-close" onclick="closeSambutanModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="sambutanText"></div>
            </div>
        </div>
    </div>

    <script src="/bts-smkn1lmj-2025/admin/public/js/sidebar.js"></script>
    <script>
        function confirmDelete(year) {
            if (confirm(`Apakah Anda yakin ingin menghapus tahun ${year}?`)) {
                window.location.href = `empty.php?delete=${year}`;
            }
        }

        function showSambutan(year, sambutan) {
            const modal = document.getElementById('sambutanModal');
            const modalYear = document.getElementById('modalYear');
            const sambutanText = document.getElementById('sambutanText');

            modalYear.textContent = year;

            // Convert URLs to clickable links and handle YouTube links
            const formattedSambutan = sambutan.replace(/(https?:\/\/[^\s]+)/g, function(url) {
                if (url.includes('youtube.com') || url.includes('youtu.be')) {
                    const videoId = getYouTubeVideoId(url);
                    if (videoId) {
                        return `<div class="youtube-embed">
                            <iframe width="100%" height="315" 
                                src="https://www.youtube.com/embed/${videoId}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                            </iframe>
                        </div>`;
                    }
                }
                return `<a href="${url}" target="_blank">${url}</a>`;
            });

            sambutanText.innerHTML = formattedSambutan;
            modal.style.display = "block";
        }

        function getYouTubeVideoId(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : false;
        }

        function closeSambutanModal() {
            const modal = document.getElementById('sambutanModal');
            modal.style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('sambutanModal');
            if (event.target == modal) {
                modal.style.display = "none";
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