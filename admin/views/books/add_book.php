<?php
session_start();
require_once __DIR__ . '../../../../config.php';
require_once __DIR__ . '/../../../app/Models/Book.php';
require_once __DIR__ . '../../tahun/function_year.php';

// Check admin session and role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: index.php");
    exit();
}

$bookModel = new Book($pdo);

// Ambil tahun dari parameter URL
$selectedYear = isset($_GET['tahun']) ? $_GET['tahun'] : null;

// Validasi tahun
if (!$selectedYear || !is_numeric($selectedYear)) {
    $_SESSION['error'] = 'Tahun akademik tidak valid';
    header('Location: ../empty.php');
    exit();
}

// Ambil daftar kategori dari database
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Dapatkan ID tahun akademik
$tahunAkademikId = getAcademicYearId($pdo, $selectedYear);
if (!$tahunAkademikId) {
    $_SESSION['error'] = 'Tahun akademik tidak ditemukan';
    header('Location: ../empty.php');
    exit();
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $penerbit = $_POST['penerbit'];
    $kategori_id = $_POST['category_id'];

    // Handle file uploads
    $uploadDirCover = __DIR__ . '/../../public/uploads/cover/';
    $uploadDirContent = __DIR__ . '/../../public/uploads/content/';

    // Buat direktori jika belum ada
    if (!file_exists($uploadDirCover)) {
        mkdir($uploadDirCover, 0777, true);
    }
    if (!file_exists($uploadDirContent)) {
        mkdir($uploadDirContent, 0777, true);
    }

    // Validasi ukuran file cover (maks 5MB)
    $coverPath = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['cover']['size'] > 5 * 1024 * 1024) { // 5MB
            $_SESSION['error'] = "Ukuran file cover melebihi batas maksimal 5MB";
            header("Location: add_book.php?tahun=" . $selectedYear);
            exit();
        }

        $coverExt = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $allowedCoverExts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($coverExt), $allowedCoverExts)) {
            $_SESSION['error'] = "Format file cover tidak valid. Gunakan JPG, JPEG, PNG, atau GIF";
            header("Location: add_book.php?tahun=" . $selectedYear);
            exit();
        }

        $coverFilename = uniqid('cover_') . '.' . $coverExt;
        $coverPath = 'cover/' . $coverFilename;
        if (!move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDirCover . $coverFilename)) {
            $_SESSION['error'] = "Gagal mengupload file cover";
            header("Location: add_book.php?tahun=" . $selectedYear);
            exit();
        }
    }

    // Validasi ukuran file content (maks 10MB)
    $contentPath = null;
    if (isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['content_file']['size'] > 10 * 1024 * 1024) { // 10MB
            // Hapus cover yang sudah diupload jika ada
            if ($coverPath && file_exists($uploadDirCover . basename($coverPath))) {
                unlink($uploadDirCover . basename($coverPath));
            }
            $_SESSION['error'] = "Ukuran file konten melebihi batas maksimal 10MB";
            header("Location: add_book.php?tahun=" . $selectedYear);
            exit();
        }

        $fileType = mime_content_type($_FILES['content_file']['tmp_name']);
        if ($fileType !== 'application/pdf') {
            // Hapus cover yang sudah diupload jika ada
            if ($coverPath && file_exists($uploadDirCover . basename($coverPath))) {
                unlink($uploadDirCover . basename($coverPath));
            }
            $_SESSION['error'] = "File konten harus berupa PDF";
            header("Location: add_book.php?tahun=" . $selectedYear);
            exit();
        }

        $contentExt = pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION);
        $contentFilename = uniqid('content_') . '.' . $contentExt;
        $contentPath = 'content/' . $contentFilename;
        if (!move_uploaded_file($_FILES['content_file']['tmp_name'], $uploadDirContent . $contentFilename)) {
            // Hapus cover yang sudah diupload jika ada
            if ($coverPath && file_exists($uploadDirCover . basename($coverPath))) {
                unlink($uploadDirCover . basename($coverPath));
            }
            $_SESSION['error'] = "Gagal mengupload file konten";
            header("Location: add_book.php?tahun=" . $selectedYear);
            exit();
        }
    }

    // Validasi data
    if (empty($judul)) {
        $_SESSION['error'] = "Judul buku harus diisi";
        header("Location: add_book.php?tahun=" . $selectedYear);
        exit();
    }

    // Simpan ke database
    if ($bookModel->create($judul, $penerbit, $kategori_id, $tahunAkademikId, $coverPath, $contentPath)) {
        $_SESSION['success'] = "Buku berhasil ditambahkan";
        header("Location: year_books.php?tahun=" . $selectedYear);
        exit();
    } else {
        // Hapus file yang sudah diupload jika gagal menyimpan ke database
        if ($coverPath && file_exists($uploadDirCover . basename($coverPath))) {
            unlink($uploadDirCover . basename($coverPath));
        }
        if ($contentPath && file_exists($uploadDirContent . basename($contentPath))) {
            unlink($uploadDirContent . basename($contentPath));
        }
        $_SESSION['error'] = "Gagal menambahkan buku";
        header("Location: add_book.php?tahun=" . $selectedYear);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku Tahun <?= htmlspecialchars($selectedYear) ?></title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/books/add_book.css">
    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
            display: none;
        }
        .file-upload-input:invalid ~ .file-upload-label {
            border-color: #dc3545;
        }
        .file-upload-input:invalid ~ .error-message {
            display: block;
        }
    </style>
</head>
<body>
    <?php include '../../views/layout/sidebar.php' ?>

    <div class="main-content">
        <div class="header">
            <h2 class="greeting">
                Tambah Buku Baru Tahun <?= htmlspecialchars($selectedYear) ?>
            </h2>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-book"></i> Informasi Buku</h2>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'] ?>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <input type="hidden" name="tahun_akademik_id" value="<?= $tahunAkademikId ?>">

                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="judul">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" id="judul" name="judul" class="form-control" required placeholder="Masukkan judul buku">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="penerbit">Penerbit</label>
                        <input type="text" id="penerbit" name="penerbit" class="form-control" placeholder="Masukkan nama penerbit">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="category_id">Kategori <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Cover Buku</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="cover" name="cover" class="file-upload-input" accept="image/jpeg,image/png,image/gif" data-max-size="5242880">
                        <label for="cover" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Seret & jatuhkan file cover di sini atau klik untuk memilih</span>
                            <small class="text-muted">Format: JPG, PNG, GIF (Maks. 5MB)</small>
                        </label>
                    </div>
                    <div class="preview-container" id="cover-preview"></div>
                    <small class="error-message" id="cover-error"></small>
                </div>

                <div class="content-uploads">
                    <h3><i class="fas fa-file-pdf"></i> Konten Buku (PDF)</h3>
                    <div class="file-upload-wrapper">
                        <input type="file" id="content-file" name="content_file" class="file-upload-input" accept="application/pdf" data-max-size="10485760">
                        <label for="content-file" class="file-upload-label">
                            <i class="fas fa-file-pdf"></i>
                            <span>Seret & jatuhkan file PDF di sini atau klik untuk memilih</span>
                            <small class="text-muted">Format: PDF (Maks. 2MB)</small>
                        </label>
                    </div>
                    <div class="content-file-list" id="content-file-list"></div>
                    <small class="error-message" id="content-error"></small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='year_books.php?tahun=<?= $selectedYear ?>'">Batal</button>
                    <button type="submit" class="btn btn-submit">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan preview file
        function handleFilePreview(inputElement, previewContainer, errorElement, maxSize) {
            const file = inputElement.files[0];
            
            // Validasi ukuran file
            if (file.size > maxSize) {
                errorElement.textContent = `Ukuran file melebihi batas maksimal ${formatFileSize(maxSize)}`;
                errorElement.style.display = 'block';
                inputElement.value = '';
                previewContainer.innerHTML = '';
                return;
            } else {
                errorElement.style.display = 'none';
            }

            // Tampilkan preview
            previewContainer.innerHTML = '';
            
            if (inputElement.accept.includes('image')) {
                // Preview untuk gambar
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.onclick = function() {
                        previewContainer.innerHTML = '';
                        inputElement.value = '';
                    };
                    
                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            } else {
                // Preview untuk PDF
                const fileItem = document.createElement('div');
                fileItem.className = 'content-file-item';
                
                const icon = document.createElement('i');
                icon.className = 'fas fa-file-pdf';
                
                const fileName = document.createElement('span');
                fileName.textContent = file.name;
                
                const fileSize = document.createElement('span');
                fileSize.className = 'file-size';
                fileSize.textContent = formatFileSize(file.size);
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = function() {
                    previewContainer.innerHTML = '';
                    inputElement.value = '';
                };
                
                fileItem.appendChild(icon);
                fileItem.appendChild(fileName);
                fileItem.appendChild(fileSize);
                fileItem.appendChild(removeBtn);
                previewContainer.appendChild(fileItem);
            }
        }

        // Format ukuran file
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Event listener untuk cover
        document.getElementById('cover').addEventListener('change', function(e) {
            const maxSize = parseInt(this.getAttribute('data-max-size'));
            handleFilePreview(
                this, 
                document.getElementById('cover-preview'), 
                document.getElementById('cover-error'), 
                maxSize
            );
        });

        // Event listener untuk content file
        document.getElementById('content-file').addEventListener('change', function(e) {
            const maxSize = parseInt(this.getAttribute('data-max-size'));
            handleFilePreview(
                this, 
                document.getElementById('content-file-list'), 
                document.getElementById('content-error'), 
                maxSize
            );
        });

        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validasi judul
            const judul = document.getElementById('judul').value.trim();
            if (!judul) {
                isValid = false;
                document.getElementById('judul').classList.add('is-invalid');
            } else {
                document.getElementById('judul').classList.remove('is-invalid');
            }
            
            // Validasi kategori
            const category = document.getElementById('category_id').value;
            if (!category) {
                isValid = false;
                document.getElementById('category_id').classList.add('is-invalid');
            } else {
                document.getElementById('category_id').classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi!');
            }
        });
    </script>
</body>
</html>