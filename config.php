    <?php

    $host = 'localhost';
    $dbname = 'buku-tahunan-siswa';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }


    /**
 * Mengekstrak ID video dari URL YouTube
 * Contoh: 
 * Input: "https://www.youtube.com/watch?v=ABCD1234"
 * Output: "ABCD1234"
 */
function extractYoutubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

/**
 * Mencegah SQL Injection
 */
function cleanInput($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, htmlspecialchars(strip_tags(trim($data))));
}

// ==============================================
// SETTING DEFAULT
// ==============================================
date_default_timezone_set('Asia/Jakarta'); // Atur timezone
error_reporting(E_ALL ^ E_WARNING); // Hide warning (opsional)

    ?>