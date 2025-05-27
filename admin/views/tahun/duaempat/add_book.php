<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../app/models/Book.php';
require_once __DIR__ . '/../function_year.php';

// Check admin session and role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: index.php");
    exit();
}

$bookModel = new Book($pdo);

// Ambil daftar kategori dari database
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$tahunAkademik = $pdo->query("SELECT * FROM tahun_akademik ORDER BY tahun DESC")->fetchAll();

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $penerbit = $_POST['penerbit'];
    $kategori_id = $_POST['category_id'];
    $tahun_akademik_id = $_POST['tahun_akademik_id'];

    // Handle file uploads
    $uploadDirCover = __DIR__ . '/../../../public/uploads/cover/';
    $uploadDirContent = __DIR__ . '/../../../public/uploads/content/';

    // Upload cover
    $coverPath = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $coverExt = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $coverFilename = uniqid('cover_') . '.' . $coverExt;
        $coverPath = 'cover/' . $coverFilename;
        move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDirCover . $coverFilename);
    }

    // Upload content (PDF)
    $contentPath = null;
    if (isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
        $fileType = mime_content_type($_FILES['content_file']['tmp_name']);
        if ($fileType !== 'application/pdf') {
            $_SESSION['error'] = "File konten harus berupa PDF";
            header("Location: add_book.php");
            exit();
        }

        $contentExt = pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION);
        $contentFilename = uniqid('content_') . '.' . $contentExt;
        $contentPath = 'content/' . $contentFilename;
        move_uploaded_file($_FILES['content_file']['tmp_name'], $uploadDirContent . $contentFilename);
    }

    // Simpan ke database
    if ($bookModel->create($judul, $penerbit, $kategori_id, $tahun_akademik_id, $coverPath, $contentPath)) {
        $_SESSION['success'] = "Buku berhasil ditambahkan";
        header("Location: duaempat.php?tahun=2024");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan buku";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku Baru</title>
    <link rel="stylesheet" href="../../../public/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/books/duaempat/add_book.css">
</head>

<body>
    <?php include '../../../views/layout/sidebar.php' ?>

    <div class="main-content">
        <div class="header">
            <h2 class="greeting">
                Tambah Buku Baru
            </h2>
    
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-book"></i> Informasi Buku</h2>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="judul">Judul Buku</label>
                        <input type="text" id="judul" name="judul" class="form-control" required placeholder="Masukkan judul buku">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="penerbit">Penerbit</label>
                        <input type="text" id="penerbit" name="penerbit" class="form-control" placeholder="Masukkan nama penerbit">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="tahun_akademik_id">Tahun Akademik</label>
                        <select id="tahun_akademik_id" name="tahun_akademik_id" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            <?php foreach ($tahunAkademik as $tahun): ?>
                                <option value="<?= $tahun['id'] ?>"><?= htmlspecialchars($tahun['tahun']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label>Cover Buku</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="cover" name="cover" class="file-upload-input" accept="image/*">
                        <label for="cover" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Seret & jatuhkan file cover di sini atau klik untuk memilih</span>
                            <small class="text-muted">Format: JPG, PNG (Maks. 5MB)</small>
                        </label>
                    </div>
                    <div class="preview-container" id="cover-preview"></div>
                </div>

                <div class="content-uploads">
                    <h3><i class="fas fa-file-pdf"></i> Konten Buku (PDF)</h3>
                    <p>Unggah file PDF untuk konten buku (Maks. 10MB)</p>

                    <div class="file-upload-wrapper">
                        <input type="file" id="content-file" name="content_file" class="file-upload-input" accept="application/pdf">
                        <label for="content-file" class="file-upload-label">
                            <i class="fas fa-file-pdf"></i>
                            <span>Seret & jatuhkan file PDF di sini atau klik untuk memilih</span>
                            <small class="text-muted">Format: PDF (Maks. 10MB)</small>
                        </label>
                    </div>

                    <div class="content-file-list" id="content-file-list"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="window.history.back()">Batal</button>
                    <button type="submit" class="btn btn-submit">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show/hide new year input
        document.getElementById('tahun').addEventListener('change', function() {
            const newYearGroup = document.getElementById('newYearGroup');
            newYearGroup.style.display = this.value === 'new' ? 'block' : 'none';

            if (this.value !== 'new') {
                document.getElementById('new_year').value = '';
            }
        });

        // Cover preview
        document.getElementById('cover').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('cover-preview');
            previewContainer.innerHTML = '';

            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();

                reader.onload = function(event) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';

                    const img = document.createElement('img');
                    img.src = event.target.result;

                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.onclick = function() {
                        previewContainer.innerHTML = '';
                        document.getElementById('cover').value = '';
                    };

                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    previewContainer.appendChild(previewItem);
                };

                reader.readAsDataURL(file);
            }
        });

        // Content file display
        document.getElementById('content-file').addEventListener('change', function(e) {
            const fileList = document.getElementById('content-file-list');
            fileList.innerHTML = '';

            if (e.target.files.length > 0) {
                const file = e.target.files[0];
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
                    fileList.innerHTML = '';
                    document.getElementById('content-file').value = '';
                };

                fileItem.appendChild(icon);
                fileItem.appendChild(fileName);
                fileItem.appendChild(fileSize);
                fileItem.appendChild(removeBtn);
                fileList.appendChild(fileItem);
            }
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]);
        }
    </script>
</body>

</html>