<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="public/css/style.css">
    <title>Document</title>
</head>

<body>
    <header>
        <p>Buku</p>
        <span>Tahunan Siswa</span>

    </header>
    <main>
        <!-- <p>Buku</p>
        <span>Tahunan Siswa</span> -->

        <section class="swiper mySwiper">
            <div class="swiper-wrapper">

                <div class="card swiper-slide">
                    <div class="card_image">
                        <a href="views/years/2024/index.php">
                            <img src="public/assets/img/dua_empat.jpg" alt="">
                        </a>

                    </div>
                </div>

                <div class="card swiper-slide">
                    <div class="card_image">
                        <a href="views/years/2025/index.php">
                            <img src="public/assets/img/dua_lima.jpg" alt="">
                        </a>
                    </div>
                </div>

                <div class="card swiper-slide">
                    <div class="card_image">
                        <img src="public/assets/img/dua_enam.jpg" alt="">
                    </div>
                </div>

                <div class="card swiper-slide">
                    <div class="card_image">
                        <img src="public/assets/img/dua_tujuh.jpg" alt="">
                    </div>
                </div>

                <div class="card swiper-slide">
                    <div class="card_image">
                        <img src="public/assets/img/dua_delapan.jpg" alt="">
                    </div>
                </div>

            </div>
        </section>

    </main>
    <footer class="footer">
        <div class="footer-container">
            <div class="title-footer">
                <h2>SMK NEGERI 1 LUMAJANG</h2>
            </div>
            <div class="footer-content">
                <p>Jl. H. O.S. Cokroaminoto No.161, Tompokersan, Kec. Lumajang, Kabupaten Lumajang, Jawa Timur 67316</p>
            </div>
            <div class="footer-copyright">
                <p>© 2025 SMK Negeri 1 Lumajang</p>
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
        } else {
            // Initialize Swiper normally for other browsers
            var swiper = new Swiper(".mySwiper", {
                loop: false,
                loopAdditionalSlides: 2,
                effect: "coverflow",
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: "auto",
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