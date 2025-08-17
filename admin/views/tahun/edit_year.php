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

// Tangani GET request untuk menampilkan form edit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID tahun akademik tidak valid.";
    header("Location: empty.php");
    exit();
}

$yearId = $_GET['id'];
$yearData = getAcademicYearById($pdo, $yearId);

if (!$yearData) {
    $_SESSION['error'] = "Data tahun akademik tidak ditemukan.";
    header("Location: empty.php");
    exit();
}

// Tangani POST request untuk menyimpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newYear = $_POST['tahun'];
    $newSambutan = $_POST['sambutan'];
    $newCoverPath = $yearData['cover_path']; // Default to existing cover path

    // Tangani upload file cover baru
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/tahun/';
        $fileName = basename($_FILES['cover']['name']);
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'cover_' . $newYear . '_' . time() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $newFileName;

        // Pindahkan file yang diupload
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $uploadFile)) {
            // Hapus cover lama jika ada
            if ($yearData['cover_path'] && file_exists(__DIR__ . '/../../' . $yearData['cover_path'])) {
                unlink(__DIR__ . '/../../' . $yearData['cover_path']);
            }
            $newCoverPath = 'public/uploads/tahun/' . $newFileName;
        } else {
            $_SESSION['error'] = "Gagal mengunggah file cover.";
            header("Location: edit_year.php?id=" . $yearId);
            exit();
        }
    }

    if (updateAcademicYear($pdo, $yearId, $newYear, $newCoverPath, $newSambutan)) {
        $_SESSION['success'] = "Tahun akademik berhasil diperbarui.";
        header("Location: empty.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui tahun akademik.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tahun Akademik <?= $yearData['tahun'] ?></title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/tahun/edit_year.css">
</head>
<body>
    <?php include '../../views/layout/sidebar.php' ?>
    <div class="main-content">
        <div class="header">
            <h2 class="greeting"><i class="fas fa-edit"></i> Edit Tahun Akademik</h2>
        </div>

        <div class="form-container">
            <div class="form-card">
                <h3>Form Edit Tahun Akademik</h3>

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

                <form action="edit_year.php?id=<?= $yearId ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="tahun">Tahun Akademik</label>
                        <input type="text" name="tahun" id="tahun" value="<?= htmlspecialchars($yearData['tahun']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cover">Cover Buku (biarkan kosong jika tidak ingin mengubah)</label>
                        <?php if ($yearData['cover_path']): ?>
                            <div class="current-cover">
                                <img src="/bts-smkn1lmj-2025/admin/<?= htmlspecialchars($yearData['cover_path']) ?>" alt="Current Cover" width="100">
                                <span>Cover saat ini</span>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover" id="cover" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="sambutan">Sambutan Kepala Sekolah (opsional)</label>
                        <textarea name="sambutan" id="sambutan" rows="5"><?= htmlspecialchars($yearData['sambutan']) ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        <a href="empty.php" class="btn-cancel"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="/bts-smkn1lmj-2025/admin/public/js/sidebar.js"></script>
</body>
</html>