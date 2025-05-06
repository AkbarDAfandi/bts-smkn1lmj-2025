<?php
require_once __DIR__ . '/../../../config.php';

function getAvailableYears($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT tahun FROM tahun_akademik ORDER BY tahun DESC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function addAcademicYear($pdo, $year) {
    // Cek apakah tahun sudah ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tahun_akademik WHERE tahun = ?");
    $stmt->execute([$year]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return false; // Tahun sudah ada
    }
    
    // Tambahkan tahun baru
    $stmt = $pdo->prepare("INSERT INTO tahun_akademik (tahun) VALUES (?)");
    return $stmt->execute([$year]);
}

function deleteAcademicYear($pdo, $year) {
    // Hapus tahun akademik
    $stmt = $pdo->prepare("DELETE FROM tahun_akademik WHERE tahun = ?");
    return $stmt->execute([$year]);
}

function getYearPagePath($year) {
    $yearPages = [
        '2024' => 'duaempat/duaempat.php',
        '2025' => 'dualima/dualima.php'
    ];
    return $yearPages[$year] ?? 'duaempat/duaempat.php';
}   
?>