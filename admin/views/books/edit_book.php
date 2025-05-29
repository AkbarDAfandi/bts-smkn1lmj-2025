<?php
session_start();
require_once __DIR__ . '../../../../config.php';
require_once __DIR__ . '../../../../app/models/Book.php';
require_once __DIR__ . '../../../../app/models/Category.php';
require_once __DIR__ . '../../tahun/function_year.php';

// Check session and role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: ../../../index.php");
    exit();
}

$bookModel = new Book($pdo);
$categoryModel = new Category($pdo);

// Get book ID and year from URL
$bookId = $_GET['id'] ?? null;
$selectedYear = $_GET['tahun'] ?? null;

if (!$bookId || !$selectedYear) {
    $_SESSION['error'] = "Parameter tidak valid";
    header("Location: year_books.php?tahun=$selectedYear");
    exit();
}

// Get book data
$book = $bookModel->getById($bookId);
if (!$book) {
    $_SESSION['error'] = "Buku tidak ditemukan";
    header("Location: year_books.php?tahun=$selectedYear");
    exit();
}

// Get all categories for dropdown
$categories = $categoryModel->getAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $judul = $_POST['judul'];
        $penerbit = $_POST['penerbit'];
        $category_id = $_POST['category_id'];
        
        // Initialize file paths as null (won't update if no new file)
        $cover_path = null;
        $content_path = null;

        // Process cover upload if provided
        if (!empty($_FILES['cover']['name'])) {
            $coverFile = $_FILES['cover'];
            $coverExtension = pathinfo($coverFile['name'], PATHINFO_EXTENSION);
            $cover_path = 'cover/book_' . $bookId . '_' . time() . '.' . $coverExtension;

            // Validate image file
            $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($coverExtension), $validExtensions)) {
                throw new Exception("Format cover tidak valid. Gunakan JPG, PNG, atau GIF");
            }

            if (move_uploaded_file($coverFile['tmp_name'], '../../../../public/uploads/' . $cover_path)) {
                // Delete old cover if exists
                if ($book['cover_path'] && file_exists('../../../../public/uploads/' . $book['cover_path'])) {
                    unlink('../../../../public/uploads/' . $book['cover_path']);
                }
            } else {
                throw new Exception("Gagal mengupload cover buku");
            }
        }

        // Process content upload if provided
        if (!empty($_FILES['content']['name'])) {
            $contentFile = $_FILES['content'];
            $contentExtension = pathinfo($contentFile['name'], PATHINFO_EXTENSION);
            $content_path = 'content/book_' . $bookId . '_' . time() . '.' . $contentExtension;

            // Validate document file
            $validExtensions = ['pdf'];
            if (!in_array(strtolower($contentExtension), $validExtensions)) {
                throw new Exception("Format file tidak valid. Gunakan PDF");
            }

            if (move_uploaded_file($contentFile['tmp_name'], '../../../../public/uploads/' . $content_path)) {
                // Delete old content if exists
                if ($book['content_path'] && file_exists('../../../../public/uploads/' . $book['content_path'])) {
                    unlink('../../../../public/uploads/' . $book['content_path']);
                }
            } else {
                throw new Exception("Gagal mengupload file buku");
            }
        }

        // Update book using model method
        $success = $bookModel->update(
            $bookId,
            $judul,
            $penerbit,
            $category_id,
            $book['tahun_akademik_id'], // Tetap gunakan tahun akademik yang sama
            $cover_path,
            $content_path
        );

        if ($success) {
            $_SESSION['success'] = "Buku berhasil diperbarui";
            header("Location: year_books.php?tahun=$selectedYear");
            exit();
        } else {
            throw new Exception("Gagal memperbarui buku");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku Tahun <?= htmlspecialchars($selectedYear) ?></title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/books/add_book.css">
    <link rel="stylesheet" href="../../public/css/books/edit.css">
</head>
<body>
    <?php include '../../views/layout/sidebar.php' ?>


    <div class="main-content">
        <div class="header">
            <h2 class="greeting">Edit Buku Tahun <?= htmlspecialchars($selectedYear) ?></h2>
        </div>

        <div class="sticky-action-header">
            <div class="button-container">
                <button class="btn-back modern-back" onclick="window.location.href='year_books.php?tahun=<?= $selectedYear ?>'">
                    <i class="fas fa-chevron-left"></i>
                    <span>Back</span>
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="judul">Judul Buku</label>
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($book['judul']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="penerbit">Penerbit</label>
                    <input type="text" id="penerbit" name="penerbit" value="<?= htmlspecialchars($book['penerbit']) ?>">
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category['id'] == $book['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cover">Cover Buku</label>
                    <input type="file" id="cover" name="cover" accept="image/*">
                    <?php if ($book['cover_path']): ?>
                        <div class="file-preview">
                            <p>Cover Saat Ini:</p>
                            <img src="../../public/uploads/<?= htmlspecialchars($book['cover_path']) ?>" alt="Current Cover">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="content">File Buku (PDF)</label>
                    <input type="file" id="content" name="content" accept=".pdf">
                    <?php if ($book['content_path']): ?>
                        <div class="file-preview">
                            <p>File Saat Ini: <?= htmlspecialchars(basename($book['content_path'])) ?></p>
                            <a href="../../../public/uploads/<?= htmlspecialchars($book['content_path']) ?>" target="_blank" class="btn-view">
                                <i class="fas fa-eye"></i> Lihat File
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</body>
</html>