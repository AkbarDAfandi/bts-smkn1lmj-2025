<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../app/models/Book.php';
require_once __DIR__ . '/../../../../app/models/Category.php';

// Check session and role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
 $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
 header("Location: ../../../index.php");
 exit();
}

$bookModel = new Book($pdo);
$categoryModel = new Category($pdo);

// Get book ID from URL
$bookId = $_GET['id'] ?? null;

if (!$bookId) {
 $_SESSION['error'] = "Buku tidak ditemukan";
 header("Location: duaempat.php");
 exit();
}

// Get book data
$book = $bookModel->getById($bookId);
if (!$book) {
 $_SESSION['error'] = "Buku tidak ditemukan";
 header("Location: duaempat.php");
 exit();
}

// Get all categories for dropdown
$categories = $categoryModel->getAll();

// Get all academic years for dropdown
$academicYears = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun DESC")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 try {
  $judul = $_POST['judul'];
  $penerbit = $_POST['penerbit'];
  $category_id = $_POST['category_id'];
  $tahun_akademik_id = $_POST['tahun_akademik_id'];

  // Initialize file paths as null (won't update if no new file)
  $cover_path = null;
  $content_path = null;

  // Process cover upload if provided
  if (!empty($_FILES['cover']['name'])) {
   $coverFile = $_FILES['cover'];
   $coverExtension = pathinfo($coverFile['name'], PATHINFO_EXTENSION);
   $cover_path = 'covers/book_' . $bookId . '_' . time() . '.' . $coverExtension;

   // Validate image file
   $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
   if (!in_array(strtolower($coverExtension), $validExtensions)) {
    throw new Exception("Format cover tidak valid. Gunakan JPG, PNG, atau GIF");
   }

   if (move_uploaded_file($coverFile['tmp_name'], '../../../public/uploads/' . $cover_path)) {
    // Delete old cover if exists
    if ($book['cover_path'] && file_exists('../../../public/uploads/' . $book['cover_path'])) {
     unlink('../../../public/uploads/' . $book['cover_path']);
    }
   } else {
    throw new Exception("Gagal mengupload cover buku");
   }
  }

  // Process content upload if provided
  if (!empty($_FILES['content']['name'])) {
   $contentFile = $_FILES['content'];
   $contentExtension = pathinfo($contentFile['name'], PATHINFO_EXTENSION);
   $content_path = 'contents/book_' . $bookId . '_' . time() . '.' . $contentExtension;

   // Validate document file
   $validExtensions = ['pdf', 'doc', 'docx'];
   if (!in_array(strtolower($contentExtension), $validExtensions)) {
    throw new Exception("Format file tidak valid. Gunakan PDF, DOC, atau DOCX");
   }

   if (move_uploaded_file($contentFile['tmp_name'], '../../../public/uploads/' . $content_path)) {
    // Delete old content if exists
    if ($book['content_path'] && file_exists('../../../public/uploads/' . $book['content_path'])) {
     unlink('../../../public/uploads/' . $book['content_path']);
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
   $tahun_akademik_id,
   $cover_path,
   $content_path
  );

  if ($success) {
   $_SESSION['success'] = "Buku berhasil diperbarui";
   header("Location: duaempat.php");
   exit();
  } else {
   throw new Exception("Gagal memperbarui buku");
  }
 } catch (Exception $e) {
  $_SESSION['error'] = $e->getMessage();
 }
}

// ... [rest of your HTML remains the same]
?>

<!DOCTYPE html>
<html lang="en">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Edit Buku</title>
 <link rel="stylesheet" href="../../../public/css/all.min.css">
 <link rel="stylesheet" href="../../../public/css/sidebar.css">
 <link rel="stylesheet" href="../../../public/css/dashboard.css">
 <style>
  .form-container {
   max-width: 800px;
   margin: 0 auto;
   padding: 2rem;
   background: white;
   border-radius: 8px;
   box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .form-group {
   margin-bottom: 1.5rem;
  }

  label {
   display: block;
   margin-bottom: 0.5rem;
   font-weight: 500;
  }

  input,
  select,
  textarea {
   width: 100%;
   padding: 0.75rem;
   border: 1px solid #ddd;
   border-radius: 4px;
   font-size: 1rem;
  }

  .file-preview {
   margin-top: 1rem;
  }

  .file-preview img {
   max-width: 200px;
   max-height: 200px;
   margin-right: 1rem;
   margin-bottom: 1rem;
   border: 1px solid #eee;
  }

  .btn-submit {
   background-color: #3b82f6;
   color: white;
   padding: 0.75rem 1.5rem;
   border: none;
   border-radius: 4px;
   cursor: pointer;
   font-size: 1rem;
   transition: background-color 0.2s;
  }

  .btn-submit:hover {
   background-color: #2563eb;
  }
 </style>
</head>

<body>
 <?php include '../../../views/layout/sidebar.php' ?>

 <div class="main-content">
  <div class="header">
   <h2 class="greeting">Edit Buku</h2>
   <div class="user-info">
    <div class="search-bar">
     <i class="fas fa-search"></i>
     <input type="text" placeholder="Search...">
    </div>
    <i class="far fa-bell"></i>
   </div>
  </div>

  <div class="sticky-action-header">
   <div class="button-container">
    <button class="btn-back modern-back" onclick="window.history.back()">
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
     <label for="tahun_akademik_id">Tahun Akademik</label>
     <select id="tahun_akademik_id" name="tahun_akademik_id" required>
      <?php foreach ($academicYears as $year): ?>
       <option value="<?= $year['id'] ?>" <?= $year['id'] == $book['tahun_akademik_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($year['tahun']) ?>
       </option>
      <?php endforeach; ?>
     </select>
    </div>

    <div class="form-group">
     <label for="cover">Cover Buku</label>
     <input type="file" id="cover" name="cover" accept="image/*">
     <?php if ($book['cover_path']): ?>
      <div class="file-preview">
       <p>Current Cover:</p>
       <img src="../../../public/uploads/<?= htmlspecialchars($book['cover_path']) ?>" alt="Current Cover">
      </div>
     <?php endif; ?>
    </div>

    <div class="form-group">
     <label for="content">File Buku</label>
     <input type="file" id="content" name="content" accept=".pdf,.doc,.docx">
     <?php if ($book['content_path']): ?>
      <div class="file-preview">
       <p>Current File: <?= htmlspecialchars(basename($book['content_path'])) ?></p>
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