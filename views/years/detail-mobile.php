<?php
require_once __DIR__ . '/../../config.php';
session_start();

if (!isset($_GET['id']) || !isset($_GET['year'])) {
    header('Location: ');
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
        header('Location: /bts-smkn1lmj-2025/views/years/' . $year . '/');
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
            header('Content-Disposition: inline; filename="' . basename($book['content_path']) . '"');
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
            window.location.href = `../../detail-mobile.php?id=${params.id}&year=${params.year}`;
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
    <!-- PDF.js from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <style>
        /* Additional styles for PDF viewer */
        .pdf-viewer-container {
            width: 100%;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            height: 80vh;
            max-height: 800px;
        }

        #pdf-container {
            width: 100%;
            height: 100%;
            text-align: center;
            background: #f5f5f5;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            position: relative;
            overflow: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            padding: 20px;
        }

        #pdf-viewer {
            max-width: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            touch-action: pan-x pan-y pinch-zoom;
        }

        .pdf-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .control-group {
            display: flex;
            gap: 10px;
        }

        .pdf-controls button {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #4a6baf;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 22px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .pdf-controls button:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .pdf-controls button:hover:after {
            opacity: 1;
        }

        .pdf-controls button:active {
            transform: scale(0.95);
        }

        .pdf-controls button:hover {
            background: #3a5a9f;
            box-shadow: 0 4px 12px rgba(74, 107, 175, 0.3);
        }

        #prev-page,
        #next-page {
            width: auto;
            padding: 12px 20px;
        }

        .pdf-controls button:disabled {
            background: #cccccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        .page-info {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #555;
            background: #fff;
            padding: 8px 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        #zoom-level {
            display: inline-block;
            min-width: 60px;
            text-align: center;
            background: #fff;
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: bold;
            color: #4a6baf;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .pdf-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid #eee;
        }

        .page-navigation,
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 480px) {
            .pdf-controls {
                flex-wrap: wrap;
            }

            .pdf-controls button {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .pdf-toolbar {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-content">
            <a href="../../index.php">
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

            <div class="book-info">
                <h1>
                    <?= htmlspecialchars($book['judul']) ?>
                    <?php if ($isTeacherBook): ?>
                        <span class="teacher-badge">Guru</span>
                    <?php endif; ?>
                </h1>
                <div class="book-details">
                    <p><strong>Kategori:</strong> <?= htmlspecialchars($book['category_name']) ?></p>
                    <p><strong>Tahun:</strong> <?= $year ?></p>
                    <?php if (!empty($book['penerbit'])): ?>
                        <p><strong>Penerbit:</strong> <?= htmlspecialchars($book['penerbit']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($book['content_path'])): ?>
                    <div class="action-buttons">
                        <a href="?id=<?= $_GET['id'] ?>&year=<?= $year ?>&download=1" class="btn download-btn">
                            <i class='bx bx-download'></i> Download Buku
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($book['content_path'])): ?>
            <div class="pdf-viewer-container">
                <div class="pdf-toolbar">
                    <div class="page-navigation">
                        <button id="prev-page" class="btn" disabled>
                            <i class='bx bx-chevron-left'></i>
                        </button>
                        <span class="page-info">
                            Halaman <span id="page-num">1</span> dari <span id="page-count">0</span>
                        </span>
                        <button id="next-page" class="btn" disabled>
                            <i class='bx bx-chevron-right'></i>
                        </button>
                    </div>
                    <div class="zoom-controls">
                        <button id="zoom-out" class="btn">
                            <i class='bx bx-minus'></i>
                        </button>
                        <span id="zoom-level">100%</span>
                        <button id="zoom-in" class="btn">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>

                <div id="pdf-container">
                    <div class="loading">
                        <i class='bx bx-loader-alt bx-spin'></i>
                        <span>Memuat dokumen PDF...</span>
                    </div>
                    <canvas id="pdf-viewer"></canvas>
                </div>

                <div class="pdf-controls">
                    <div class="control-group">
                        <button id="prev-page" class="btn nav-btn" title="Halaman Sebelumnya">
                            <i class='bx bx-chevron-left'></i>
                        </button>
                        <div class="page-info">
                            <span id="page-num">1</span> / <span id="page-count">0</span>
                        </div>
                        <button id="next-page" class="btn nav-btn" title="Halaman Berikutnya">
                            <i class='bx bx-chevron-right'></i>
                        </button>
                    </div>

                    <div class="control-group">
                        <button id="zoom-out" class="btn" title="Perkecil">
                            <i class='bx bx-zoom-out'></i>
                        </button>
                        <span id="zoom-level">100%</span>
                        <button id="zoom-in" class="btn" title="Perbesar">
                            <i class='bx bx-zoom-in'></i>
                        </button>
                    </div>

                    <div class="control-group">
                        <button id="move-left" class="btn nav-btn" title="Geser Kiri">
                            <i class='bx bx-left-arrow-alt'></i>
                        </button>
                        <button id="move-right" class="btn nav-btn" title="Geser Kanan">
                            <i class='bx bx-right-arrow-alt'></i>
                        </button>
                    </div>

                    <div class="control-group">
                        <button id="move-up" class="btn nav-btn" title="Geser Atas">
                            <i class='bx bx-up-arrow-alt'></i>
                        </button>
                        <button id="move-down" class="btn nav-btn" title="Geser Bawah">
                            <i class='bx bx-down-arrow-alt'></i>
                        </button>
                    </div>

                    <button id="reset-view" class="btn nav-btn" title="Reset Tampilan">
                        <i class='bx bx-reset'></i>
                    </button>
                </div>
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
                <p class="copyright">© 2023 smkn1lmj. Buku Tahunan Siswa SMK Negeri 1 Lumajang</p>
                <p class="credits">Desain Oleh Jurnalistik SMK Negeri 1 Lumajang | Rekayasa Perangkat Lunak Gen-12</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

        // PDF variables
        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 0.6,
            maxScale = 5.0,
            minScale = 0.1,
            defaultScale = 0.6,
            currentX = 0,
            currentY = 0;

        const url = '../../admin/public/uploads/<?= $book['content_path'] ?>';
        const container = document.getElementById('pdf-container');
        const canvas = document.getElementById('pdf-viewer');
        const ctx = canvas.getContext('2d');

        // Get control elements
        const pageNumElement = document.getElementById('page-num');
        const pageCountElement = document.getElementById('page-count');
        const prevPageButton = document.getElementById('prev-page');
        const nextPageButton = document.getElementById('next-page');

        // Calculate optimal scale for device
        function calculateScale(viewport) {
            const containerWidth = container.clientWidth - 40;
            const containerHeight = container.clientHeight - 40;

            // Calculate scales to fit width and height
            const scaleX = containerWidth / viewport.width;
            const scaleY = containerHeight / viewport.height;

            // Use scale to fit width, but make it 30% of original default (lower than before)
            defaultScale = Math.max(scaleX * 0.3, 0.6);

            // On mobile, ensure text is still readable
            if (window.innerWidth <= 768) {
                defaultScale = Math.max(defaultScale, 0.6);
            }

            // Use current scale if it exists, otherwise use default
            return scale || defaultScale;
        }

        // Render PDF page
        function renderPage(num) {
            pageRendering = true;

            // Remove loading message
            const loadingElement = document.querySelector('.loading');
            if (loadingElement) loadingElement.remove();

            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({
                    scale: 1.0
                });
                scale = calculateScale(viewport);
                const scaledViewport = page.getViewport({
                    scale: scale
                });

                // Set canvas dimensions
                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;

                // Render PDF page
                const renderContext = {
                    canvasContext: ctx,
                    viewport: scaledViewport
                };

                const renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            // Update page counter
            pageNumElement.textContent = num;

            // Enable/disable buttons
            prevPageButton.disabled = (num <= 1);
            nextPageButton.disabled = (num >= pdfDoc.numPages);
        }

        // Queue page render
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        // Previous page
        function onPrevPage() {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        }

        // Next page
        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        // Load PDF document
        pdfjsLib.getDocument({
            url: url,
            cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/cmaps/',
            cMapPacked: true
        }).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            pageCountElement.textContent = pdfDoc.numPages;

            // Initial render
            renderPage(pageNum);

            // Handle window resize
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    if (pdfDoc) queueRenderPage(pageNum);
                }, 200);
            });
        }).catch(function(error) {
            // Show error message
            container.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #d32f2f;">
                    <p>Gagal memuat dokumen PDF.</p>
                    <p>Silakan coba download file-nya.</p>
                </div>
            `;
            console.error('PDF error:', error);
        });

        // Zoom functions
        function zoomIn() {
            if (scale < maxScale) {
                scale = Math.min(maxScale, scale + 0.25);
                queueRenderPage(pageNum);
                updateZoomLevel();
            }
        }

        function zoomOut() {
            if (scale > minScale) {
                scale = Math.max(minScale, scale - 0.25);
                queueRenderPage(pageNum);
                updateZoomLevel();
            }
        }

        // Button events
        prevPageButton.addEventListener('click', onPrevPage);
        nextPageButton.addEventListener('click', onNextPage);
        document.getElementById('zoom-in').addEventListener('click', zoomIn);
        document.getElementById('zoom-out').addEventListener('click', zoomOut);

        // Update the zoom level display
        function updateZoomLevel() {
            let zoomPercentage = Math.round(scale * 100 / defaultScale);
            // If scale is at minimum, show as 0%
            if (scale <= minScale) zoomPercentage = 0;
            document.getElementById('zoom-level').textContent = `${zoomPercentage}%`;

            // Add visual feedback
            const zoomLevelElement = document.getElementById('zoom-level');
            zoomLevelElement.style.transform = 'scale(1.1)';
            setTimeout(() => {
                zoomLevelElement.style.transform = 'scale(1)';
            }, 200);
        }

        // Pinch zoom events
        container.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                startDistance = Math.hypot(
                    e.touches[0].pageX - e.touches[1].pageX,
                    e.touches[0].pageY - e.touches[1].pageY
                );
                initialPinchDistance = startDistance;
            }
        }, {
            passive: false
        });

        container.addEventListener('touchmove', function(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                const currentDistance = Math.hypot(
                    e.touches[0].pageX - e.touches[1].pageX,
                    e.touches[0].pageY - e.touches[1].pageY
                );

                if (startDistance) {
                    const pinchRatio = currentDistance / initialPinchDistance;
                    const newScale = Math.min(Math.max(minScale, scale * pinchRatio), maxScale);

                    if (Math.abs(newScale - scale) > 0.1) {
                        scale = newScale;
                        queueRenderPage(pageNum);
                        initialPinchDistance = currentDistance;
                    }
                }
            }
        }, {
            passive: false
        });

        container.addEventListener('touchend', function(e) {
            startDistance = null;
            initialPinchDistance = null;
        });

        // PDF Navigation and Controls
        const moveStep = 50;

        function moveView(direction) {
            const container = document.getElementById('pdf-container');
            const step = moveStep * scale;

            switch (direction) {
                case 'left':
                    container.scrollLeft -= step;
                    break;
                case 'right':
                    container.scrollLeft += step;
                    break;
                case 'up':
                    container.scrollTop -= step;
                    break;
                case 'down':
                    container.scrollTop += step;
                    break;
            }
        }

        function resetView() {
            scale = defaultScale;
            const container = document.getElementById('pdf-container');
            container.scrollLeft = 0;
            container.scrollTop = 0;
            queueRenderPage(pageNum);
            updateZoomLevel();
        }

        // Add navigation button event listeners
        document.getElementById('move-left').addEventListener('click', () => moveView('left'));
        document.getElementById('move-right').addEventListener('click', () => moveView('right'));
        document.getElementById('move-up').addEventListener('click', () => moveView('up'));
        document.getElementById('move-down').addEventListener('click', () => moveView('down'));
        document.getElementById('reset-view').addEventListener('click', resetView);

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            switch (e.key) {
                case 'ArrowLeft':
                    moveView('left');
                    break;
                case 'ArrowRight':
                    moveView('right');
                    break;
                case 'ArrowUp':
                    moveView('up');
                    break;
                case 'ArrowDown':
                    moveView('down');
                    break;
            }
        });

        // Disable double-tap zoom on buttons
        const buttons = document.querySelectorAll('button, a');
        buttons.forEach(button => {
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            button.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    </script>
</body>

</html>