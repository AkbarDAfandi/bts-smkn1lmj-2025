<?php
require_once __DIR__ . '/../../../config.php';

function getAvailableYears($pdo)
{
    $stmt = $pdo->query("SELECT DISTINCT tahun FROM tahun_akademik ORDER BY tahun DESC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function addAcademicYear($pdo, $year, $cover_path = null, $sambutan = null)
{
    // Cek apakah tahun sudah ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tahun_akademik WHERE tahun = ?");
    $stmt->execute([$year]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        return false; // Tahun sudah ada
    }

    // Tambahkan tahun baru dengan cover dan sambutan
    $stmt = $pdo->prepare("INSERT INTO tahun_akademik (tahun, cover_path, sambutan) VALUES (?, ?, ?)");
    return $stmt->execute([$year, $cover_path, $sambutan]);
}

function deleteAcademicYear($pdo, $year)
{
    // Hapus tahun akademik dan buku terkait (karena ada ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM tahun_akademik WHERE tahun = ?");
    return $stmt->execute([$year]);
}

function getAcademicYearId($pdo, $year)
{
    $stmt = $pdo->prepare("SELECT id FROM tahun_akademik WHERE tahun = ?");
    $stmt->execute([$year]);
    return $stmt->fetchColumn();
}

function getYearsWithDetails($pdo)
{
    try {
        $stmt = $pdo->query("SELECT tahun, cover_path, sambutan FROM tahun_akademik ORDER BY tahun DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching years with details: " . $e->getMessage());
        return [];
    }
}

// Fungsi baru yang diperlukan untuk years.php
function getAllAcademicYears($pdo)
{
    try {
        $stmt = $pdo->query("SELECT id, tahun, cover_path, sambutan, created_at FROM tahun_akademik ORDER BY tahun DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching all academic years: " . $e->getMessage());
        return [];
    }
}

function getAcademicYearById($pdo, $id)
{
    try {
        $stmt = $pdo->prepare("SELECT id, tahun, cover_path, sambutan FROM tahun_akademik WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching academic year by ID: " . $e->getMessage());
        return false;
    }
}

function updateAcademicYear($pdo, $id, $year, $cover_path = null, $sambutan = null)
{
    try {
        // Jika cover_path tidak diupdate, pertahankan nilai yang ada
        if ($cover_path === null) {
            $stmt = $pdo->prepare("UPDATE tahun_akademik SET tahun = ?, sambutan = ? WHERE id = ?");
            return $stmt->execute([$year, $sambutan, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE tahun_akademik SET tahun = ?, cover_path = ?, sambutan = ? WHERE id = ?");
            return $stmt->execute([$year, $cover_path, $sambutan, $id]);
        }
    } catch (PDOException $e) {
        error_log("Error updating academic year: " . $e->getMessage());
        return false;
    }
}

function deleteAcademicYearById($pdo, $id)
{
    try {
        // Dapatkan path cover untuk dihapus dari server
        $stmt = $pdo->prepare("SELECT cover_path FROM tahun_akademik WHERE id = ?");
        $stmt->execute([$id]);
        $cover_path = $stmt->fetchColumn();

        // Hapus record dari database
        $stmt = $pdo->prepare("DELETE FROM tahun_akademik WHERE id = ?");
        $result = $stmt->execute([$id]);

        // Jika berhasil dihapus dari database dan ada cover path, hapus file
        if ($result && $cover_path) {
            $file_path = __DIR__ . '/../../' . $cover_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        return $result;
    } catch (PDOException $e) {
        error_log("Error deleting academic year: " . $e->getMessage());
        return false;
    }
}