<?php
require_once __DIR__ . '/../../config.php';
session_start();

if (!isset($_GET['id']) || !isset($_GET['year'])) {
     header('Location: ' . $year . '/');
    exit;
}

$year = (int)$_GET['year'];

try {
    // Get academic year ID
    $stmt = $pdo->prepare("SELECT id FROM tahun_akademik WHERE tahun = ?");
    $stmt->execute([$year]);
    $tahun_akademik_id = $stmt->fetchColumn();
    
    if (!$tahun_akademik_id) {
        die("Tahun akademik tidak valid");
    }

    // Get book details with category name
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name 
                          FROM books b
                          JOIN categories c ON b.category_id = c.id
                          WHERE b.id = ? AND b.tahun_akademik_id = ?");
    $stmt->execute([$_GET['id'], $tahun_akademik_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        header('Location: ' . $year . '/');
        exit;
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle download request
if (isset($_GET['download'])) {
    if (!empty($book['content_path'])) {
        $filePath = __DIR__ . '/../../admin/public/uploads/' . $book['content_path'];
        
        if (file_exists($filePath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($book['content_path']) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
    }
    header('Location: ' . $year . '/');
    exit;
}

// Determine if this is a teacher book
$isTeacherBook = ($book['category_id'] == 2);
?>
<script>
// Function to detect mobile devices
function isMobileDevice() {
    return (
        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
        (window.innerWidth <= 800 && window.innerHeight <= 900)
    );
}

// Function to get current URL parameters
function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        id: params.get('id'),
        year: params.get('year')
    };
}

// Check if it's a mobile device and redirect
if (isMobileDevice()) {
    const params = getUrlParams();
    const currentPath = window.location.pathname;
    
    // Only redirect if we're not already on the mobile page
    if (!currentPath.includes('detail-mobile.php')) {
        window.location.href = `detail-mobile.php?id=${params.id}&year=${params.year}`;
    }
}
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($book['judul']) ?> - Buku Tahunan <?= $year ?></title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/detail.css">
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-content">
            <a href="">
                <img src="../../public/assets/img/logosmk.png" alt="Logo SMK"> 
                <p class="header-title">Buku Tahunan Siswa - <?= $year ?></p>
            </a>
        </div>
    </header>
    
     <!-- MAIN CONTENT -->
    <main class="container">
        <div class="book-header">
            <img src="../../admin/public/uploads/<?= $book['cover_path'] ?>" 
                 alt="<?= htmlspecialchars($book['judul']) ?>" 
                 class="book-cover"
                 onerror="this.src='../../public/assets/img/default-book.png'">
            
            <div>
                <h1>
                    <?= htmlspecialchars($book['judul']) ?>
                    <?php if($isTeacherBook): ?>
                        <span class="teacher-badge">Guru</span>
                    <?php endif; ?>
                </h1>
                <p><strong>Kategori:</strong> <?= htmlspecialchars($book['category_name']) ?></p>
                <p><strong>Tahun:</strong> <?= $year ?></p>
                <?php if(!empty($book['penerbit'])): ?>
                    <p><strong>Penerbit:</strong> <?= htmlspecialchars($book['penerbit']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($book['content_path'])): ?>
                    <a href="?id=<?= $_GET['id'] ?>&year=<?= $year ?>&download=1" 
                       class="download-btn">
                       <i class='bx bx-download'></i> Download Buku
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($book['content_path'])): ?>
            <div class="pdf-viewer-container">
                <iframe src="../../admin/public/uploads/<?= $book['content_path'] ?>#toolbar=0" 
                        class="pdf-viewer">
                    Browser Anda tidak mendukung PDF. Silakan download <a href="?id=<?= $_GET['id'] ?>&year=<?= $year ?>&download=1">di sini</a>.
                </iframe>
            </div>
        <?php else: ?>
            <div class="alert">
                <i class='bx bx-error'></i> File buku tidak tersedia
            </div>
        <?php endif; ?>
        
        <a href="/bts-smkn1lmj-2025/views/years/years.php?tahun=<?= $year ?>" class="btn back-btn">
            <i class='bx bx-arrow-back'></i> Kembali ke Katalog <?= $year ?>
        </a>
    </main>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <h2 class="footer-title">BUKU TAHUNAN SISWA SMKN 1 LUMAJANG</h2>
                <div class="footer-social">
                    <a href="https://www.instagram.com/smkn1lumajang?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                    <a href="https://web.facebook.com/people/SMKN-1-Lumajang/100071251050566/" aria-label="Facebook"><i class='bx bxl-facebook'></i></a>
                    <a href="https://www.tiktok.com/@smkn1lumajang?_t=8knQnkBiAXB&_r=1" aria-label="Tiktok"><i class='bx bxl-tiktok'></i></a>
                    <a href="https://www.youtube.com/@smkn1lumajangtv797" aria-label="Youtube"><i class='bx bxl-youtube'></i></a>
                    <a href="https://t.me/info_ppdb_smkn1lumajang_2024" aria-label="Spotify"><i class='bx bxl-telegram'></i></a>
                </div>
            </div>

            <div class="footer-info">
                <p class="copyright">© 2024 smkn1lmj. Buku Tahunan Siswa SMK Negeri 1 Lumajang</p>
                <p class="credits">Desain Oleh Jurnalistik SMK Negeri 1 Lumajang | Rekayasa Perangkat Lunak Gen-12</p>
            </div>
        </div>
    </footer>
</body>
</html>