<?php
session_start();
require_once  'config.php';

// Get current year
$currentYear = date('Y');

// Get years data from database and sort by year in descending order (newest first)
try {
    $stmt = $pdo->query("SELECT tahun, cover_path FROM tahun_akademik ORDER BY tahun DESC");
    $yearsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the newest year from the data
    $newestYear = !empty($yearsData) ? $yearsData[0]['tahun'] : $currentYear;
} catch (PDOException $e) {
    error_log("Error fetching years: " . $e->getMessage());
    $yearsData = [];
    $newestYear = $currentYear;
}

// Reverse the array back to ascending order for display
$yearsData = array_reverse($yearsData);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="icon" href="public/assets/img/logosmk.png" type="image/x-icon">
    <title>Buku Tahunan Siswa</title>
    <style>
        .card.newest-year {
            transform: scale(1.1);
            box-shadow: 0 0 30px rgba(74, 107, 175, 0.6);
            z-index: 10;
            position: relative;
        }

        .card.newest-year::after {
            content: 'Terbaru';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #4a6baf;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .no-cover-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fc;
            border: 2px dashed #e3e6f0;
            color: #4a6baf;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header>
        <p>Buku</p>
        <span>Tahunan Siswa</span>
    </header>

    <section class="swiper mySwiper">
        <div class="swiper-wrapper">
            <?php foreach ($yearsData as $yearData): ?>
                <div class="card swiper-slide <?= $newestYear == $yearData['tahun'] ? 'newest-year' : '' ?>">
                    <div class="card_image">
                        <a href="views/years/years.php?tahun=<?= $yearData['tahun'] ?>">
                            <?php if (!empty($yearData['cover_path'])): ?>
                                <img src="admin/<?= $yearData['cover_path'] ?>" alt="Buku Tahunan <?= $yearData['tahun'] ?>">
                            <?php else: ?>
                                <div class="no-cover-placeholder">
                                    <?= $yearData['tahun'] ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="title-footer">
                <h2>SMK NEGERI 1 LUMAJANG</h2>
            </div>
            <div class="footer-content">
                <p>Jl. H. O.S. Cokroaminoto No.161, Tompokersan, Kec. Lumajang, Kabupaten Lumajang, Jawa Timur 67316</p>
            </div>
            <div class="footer-copyright">
                <p>© <?= date('Y') ?> SMK Negeri 1 Lumajang</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        // Detect Safari
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        const isFirefox = /firefox/i.test(navigator.userAgent);

        if (isSafari || isFirefox) {
            // Remove Swiper entirely for Safari
            document.querySelector('.mySwiper').classList.add('simple-carousel');

            // Add simple CSS carousel styles
            const style = document.createElement('style');
            style.textContent = `
                .simple-carousel {
                    display: flex;
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                }
                .simple-carousel .swiper-wrapper {
                    display: flex;
                }
                .simple-carousel .swiper-slide {
                    scroll-snap-align: start;
                    flex: 0 0 auto;
                }
            `;
            document.head.appendChild(style);

            // Auto scroll to newest year for Safari/Firefox
            const newestYearElement = document.querySelector('.newest-year');
            if (newestYearElement) {
                setTimeout(() => {
                    newestYearElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'center'
                    });
                }, 100);
            }
        } else {
            // Get newest year index for initial slide
            const years = <?= json_encode(array_column($yearsData, 'tahun')) ?>;
            const newestYearIndex = years.indexOf(<?= $newestYear ?>);
            const initialSlide = newestYearIndex !== -1 ? newestYearIndex : 0;

            // Initialize Swiper
            var swiper = new Swiper(".mySwiper", {
                loop: false,
                loopAdditionalSlides: 2,
                effect: "coverflow",
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: "auto",
                initialSlide: initialSlide,
                coverflowEffect: {
                    rotate: 0,
                    stretch: 0,
                    depth: 300,
                    modifier: 1,
                    slideShadows: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                },
            });
        }
    </script>
</body>

</html>