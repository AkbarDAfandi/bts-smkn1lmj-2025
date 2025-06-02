<?php
require_once __DIR__ . '/../../config.php';
require_once  __DIR__ . '/../../admin/views/tahun/function_year.php';

session_start();

try {
    // Get selected year from URL parameter
    $selectedYear = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

    // Get tahun_akademik_id for the selected year
    $stmtYear = $pdo->prepare("SELECT id, sambutan FROM tahun_akademik WHERE tahun = ?");
    $stmtYear->execute([$selectedYear]);
    $yearData = $stmtYear->fetch(PDO::FETCH_ASSOC);

    if (!$yearData) {
        die("Tahun akademik tidak ditemukan");
    }

    $tahunAkademikId = $yearData['id'];

    // Ambil semua kategori dari database
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Ambil data buku berdasarkan kategori dan tahun akademik
    $booksByCategory = [];
    foreach ($categories as $id => $name) {
        if ($id != 2 && $id != 5) { // Skip kategori guru (2) dan osis (5)
            $stmt = $pdo->prepare("SELECT * FROM books WHERE category_id = ? AND tahun_akademik_id = ?");
            $stmt->execute([$id, $tahunAkademikId]);
            $books = $stmt->fetchAll();

            if (!empty($books)) {
                $booksByCategory[$id] = [
                    'name' => $name,
                    'books' => $books
                ];
            }
        }
    }

    // Ambil data khusus guru (kategori 2)
    $stmt = $pdo->prepare("SELECT * FROM books WHERE category_id = 2 AND tahun_akademik_id = ?");
    $stmt->execute([$tahunAkademikId]);
    $teacherBooks = $stmt->fetchAll();

    // Ambil data khusus osis (kategori 5)
    $stmt = $pdo->prepare("SELECT * FROM books WHERE category_id = 5 AND tahun_akademik_id = ?");
    $stmt->execute([$tahunAkademikId]);
    $osisBooks = $stmt->fetchAll();

    // Extract YouTube IDs from current year's sambutan
    $youtubeIds = [];
    if ($yearData && $yearData['sambutan']) {
        preg_match_all('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?([a-zA-Z0-9_-]{11})/', $yearData['sambutan'], $matches);
        if (!empty($matches[1])) {
            $youtubeIds = $matches[1];
        } else {
            $youtubeIds[] = 'IUqF6cAKR6Q';
        }
    } else {
        $youtubeIds[] = 'IUqF6cAKR6Q';
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$academicYears = getAllAcademicYears($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tahunan Siswa - <?= htmlspecialchars($selectedYear) ?></title>
    <link rel="stylesheet" href="../../public/css/years.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="/bts-smkn1lmj-2025/public/assets/img/logosmk.png" type="image/x-icon">
</head>

<body>
    <header class="header">
        <div class="header-content">
            <a href="/bts-smkn1lmj-2025/">
                <img src="/bts-smkn1lmj-2025/public/assets/img/logosmk.png" alt="Logo SMK">
                <p class="header-title">Buku Tahunan Siswa - <?= htmlspecialchars($selectedYear) ?></p>
            </a>
        </div>
        <a href="/bts-smkn1lmj-2025/views/auth/login.php">
            <button class="download-button">Download</button>
        </a>
    </header>

    <main>
        <section class="content">
            <div class="video-container">
                <div class="custom-video-frame">
                    <img src="/bts-smkn1lmj-2025/public/assets/img/border.png" class="frame-image" alt="Video Frame">
                    <div class="video-carousel">
                        <?php foreach ($youtubeIds as $index => $youtubeId): ?>
                            <div class="video-slide <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                                <div class="youtube-video">
                                    <iframe src="https://www.youtube.com/embed/<?= $youtubeId ?>?enablejsapi=1"
                                        title="YouTube video player"
                                        frameborder="0"
                                        id="youtube-player-<?= $index ?>"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($youtubeIds) > 1): ?>
                        <div class="carousel-controls">
                            <button class="carousel-prev" onclick="moveSlide(-1)">
                                <i class='bx bx-chevron-left'></i>
                            </button>
                            <button class="carousel-next" onclick="moveSlide(1)">
                                <i class='bx bx-chevron-right'></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAMPILAN KHUSUS GURU DAN OSIS DI LAYOUT CONTAINER -->
            <div class="layout-container">
                <!-- Bagian Guru -->
                <?php if (!empty($teacherBooks)): ?>
                    <?php foreach ($teacherBooks as $book): ?>
                        <div class="button-card">
                            <a href="detail.php?id=<?= $book['id'] ?>&year=<?= $selectedYear ?>" class="card-link">
                                <img src="/bts-smkn1lmj-2025/admin/public/uploads/<?= $book['cover_path'] ?>"
                                    alt="<?= htmlspecialchars($book['judul']) ?>"
                                    class="card-image"
                                    onerror="this.src='/bts-smkn1lmj-2025/public/assets/img/buttonimage.png'">
                                <span class="card-overlay"><?= htmlspecialchars($book['judul']) ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Bagian OSIS -->
                <?php if (!empty($osisBooks)): ?>
                    <?php foreach ($osisBooks as $book): ?>
                        <div class="button-card-osis">
                            <a href="detail.php?id=<?= $book['id'] ?>&year=<?= $selectedYear ?>" class="card-link">
                                <img src="/bts-smkn1lmj-2025/admin/public/uploads/<?= $book['cover_path'] ?>"
                                    alt="<?= htmlspecialchars($book['judul']) ?>"
                                    class="card-image"
                                    onerror="this.src='/bts-smkn1lmj-2025/public/assets/img/osis-buttoncard.png'">
                                <span class="card-overlay"><?= htmlspecialchars($book['judul']) ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- TAMPILAN UNTUK SEMUA KATEGORI KECUALI GURU DAN OSIS -->
        <?php if (!empty($booksByCategory)): ?>
            <?php foreach ($booksByCategory as $category): ?>
                <section class="content-book">
                    <p><?= htmlspecialchars($category['name']) ?></p>
                    <div class="buku-kelas">
                        <?php foreach ($category['books'] as $book): ?>
                            <div class="buku-kelas-card">
                                <img src="/bts-smkn1lmj-2025/admin/public/uploads/<?= $book['cover_path'] ?>"
                                    alt="<?= htmlspecialchars($book['judul']) ?>"
                                    onerror="this.src='/bts-smkn1lmj-2025/public/assets/buku-perkelas/osis55.png'">
                                <h1><?= htmlspecialchars($book['judul']) ?></h1>
                                <h2>Oleh <?= htmlspecialchars($book['penerbit'] ?? 'SMKN 1 Lumajang') ?></h2>
                                <hr style="height:0.05em; border-width:0; background-color:black; margin-bottom:10px">
                                <?php
                                $isMobile = preg_match("/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i", $_SERVER['HTTP_USER_AGENT']);
                                ?>
                                <a href="<?= $isMobile ? 'detail-mobile.php' : 'detail.php' ?>?id=<?= $book['id'] ?>&year=<?= $selectedYear ?>">
                                    <button>Lihat selengkapnya</button>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <section class="content-book">
                <p>Tidak ada data buku tersedia untuk tahun <?= htmlspecialchars($selectedYear) ?></p>
            </section>
        <?php endif; ?>
    </main>

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

    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.video-slide');
        const totalSlides = slides.length;
        let players = [];
        let touchStartX = 0;
        let touchEndX = 0;

        // Initialize YouTube API
        function onYouTubeIframeAPIReady() {
            // Create YouTube players for each video
            slides.forEach((slide, index) => {
                const iframe = slide.querySelector('iframe');
                players[index] = new YT.Player(`youtube-player-${index}`, {
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });
            });
        }

        function onPlayerReady(event) {
            // When first video is ready, play it
            if (event.target === players[0]) {
                event.target.playVideo();
            }
        }

        function onPlayerStateChange(event) {
            // When a video starts playing, pause all other videos
            if (event.data == YT.PlayerState.PLAYING) {
                players.forEach((player, index) => {
                    if (player && player !== event.target) {
                        player.pauseVideo();
                    }
                });
            }
        }

        // Touch events for mobile swipe
        document.querySelector('.video-container').addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, false);

        document.querySelector('.video-container').addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, false);

        function handleSwipe() {
            const swipeThreshold = 50; // minimum distance for swipe
            const swipeDistance = touchEndX - touchStartX;

            if (Math.abs(swipeDistance) > swipeThreshold) {
                if (swipeDistance > 0) {
                    // Swipe right - go to previous
                    moveSlide(-1);
                } else {
                    // Swipe left - go to next
                    moveSlide(1);
                }
            }
        }

        function moveSlide(direction) {
            // Pause current video
            if (players[currentSlide]) {
                players[currentSlide].pauseVideo();
            }

            // Remove active class from current slide
            slides[currentSlide].classList.remove('active');

            // Calculate new slide index
            currentSlide = (currentSlide + direction + totalSlides) % totalSlides;

            // Add active class to new slide
            slides[currentSlide].classList.add('active');

            // Play the new video after a short delay to allow transition
            setTimeout(() => {
                if (players[currentSlide]) {
                    players[currentSlide].playVideo();
                }
            }, 500);
        }
    </script>
</body>

</html>