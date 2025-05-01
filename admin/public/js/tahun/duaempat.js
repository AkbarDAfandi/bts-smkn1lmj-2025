
// JavaScript untuk dropdown (styling) dan carousel
document.addEventListener('DOMContentLoaded', function() {
    const dropdownWrapper = document.querySelector('.dropdown-wrapper');
    const dropdownToggle = document.querySelector('.dropdown-toggle'); // Sekarang select element

    dropdownToggle.addEventListener('click', function() {
        dropdownWrapper.classList.toggle('active'); // Untuk styling jika diperlukan
    });

    document.addEventListener('click', function(event) {
        if (!dropdownWrapper.contains(event.target) && event.target !== dropdownToggle) {
            dropdownWrapper.classList.remove('active');
        }
    });

    // Inisialisasi carousel (bagian JavaScript yang terkait tampilan)
    const cardContainer = document.getElementById('cardContainer');
    function getCardWidth() {
        const firstCard = cardContainer.querySelector('.component-card');
        return firstCard ? firstCard.offsetWidth + 25 : 0;
    }

    let scrollPosition = 0;
    let cardWidth = getCardWidth();
    let arrowRight = document.querySelector('.carousel-arrow.right-arrow');
    let arrowLeft = document.querySelector('.carousel-arrow.left-arrow');

    function updateMaxScroll() {
        return Math.max(0, cardContainer.scrollWidth - cardContainer.offsetWidth);
    }

    let maxScroll = updateMaxScroll();

    function scrollRight() {
        const scrollAmount = getCardWidth() || cardContainer.offsetWidth;
        scrollPosition = Math.min(scrollPosition + scrollAmount, maxScroll);
        cardContainer.scrollTo({ left: scrollPosition, behavior: 'smooth' });
        checkArrows();
    }

    function scrollLeft() {
        const scrollAmount = getCardWidth() || cardContainer.offsetWidth;
        scrollPosition = Math.max(scrollPosition - scrollAmount, 0);
        cardContainer.scrollTo({ left: scrollPosition, behavior: 'smooth' });
        checkArrows();
    }

    function checkArrows() {
        maxScroll = updateMaxScroll();
        if (arrowLeft && arrowRight) {
            arrowLeft.style.display = scrollPosition > 0 && cardContainer.children.length > 0 ? 'block' : 'none';
            arrowRight.style.display = scrollPosition < maxScroll && cardContainer.children.length > 0 ? 'block' : 'none';
        }
    }

    if (arrowRight && arrowLeft && cardContainer) {
        arrowRight.addEventListener('click', scrollRight);
        arrowLeft.addEventListener('click', scrollLeft);

        cardContainer.addEventListener('scroll', function() {
            scrollPosition = cardContainer.scrollLeft;
            checkArrows();
        });
    }

    window.addEventListener('resize', () => {
        cardWidth = getCardWidth();
        maxScroll = updateMaxScroll();
        scrollPosition = Math.min(scrollPosition, maxScroll);
        cardContainer.scrollTo({ left: scrollPosition });
        checkArrows();
    });

    setTimeout(checkArrows, 200);
});
