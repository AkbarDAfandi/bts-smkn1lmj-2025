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

// Initialize error variable
$error = '';
$success_message = '';

// Get and clear any success message from session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = $_POST['year'] ?? '';
    $sambutan = $_POST['sambutan'] ?? '';

    // Validasi tahun
    if (empty($year)) {
        $error = "Tahun tidak boleh kosong";
    } elseif (!is_numeric($year) || $year < 2000 || $year > 2100) {
        $error = "Tahun harus antara 2000 dan 2100";
    }

    // Validasi file upload
    if (isset($_FILES['cover_path']) && $_FILES['cover_path']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['cover_path']['name'];
        $filesize = $_FILES['cover_path']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "File harus berformat JPG atau PNG";
        }

        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        if ($filesize > 2 * 1024 * 1024) {
            $error = "Ukuran file tidak boleh lebih dari 2MB";
        }
    } else {
        $error = "Cover image harus diupload";
    }

    if (empty($error)) {
        // Generate unique filename
        $new_filename = uniqid() . '.' . $ext;
        $upload_dir = __DIR__ . '/../../public/uploads/tahun/';
        $upload_path = $upload_dir . $new_filename;

        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['cover_path']['tmp_name'], $upload_path)) {
            // Store relative path in database - Fix the path to match the actual upload location
            $db_path = 'public/uploads/tahun/' . $new_filename;

            // Make sure sambutan is not empty before adding
            $sambutan = trim($sambutan); // Remove any whitespace

            if (addAcademicYear($pdo, $year, $db_path, $sambutan)) {
                $_SESSION['success'] = "Tahun akademik $year berhasil ditambahkan";
                header("Location: empty.php");
                exit();
            } else {
                $error = "Tahun akademik $year sudah ada";
                // Delete uploaded file if database insert fails
                unlink($upload_path);
            }
        } else {
            $error = "Gagal mengupload file";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Year | BTS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/bts-smkn1lmj-2025/admin/public/css/sidebar.css">
    <link rel="stylesheet" href="/bts-smkn1lmj-2025/admin/public/css/dashboard.css">
    <link rel="stylesheet" href="/bts-smkn1lmj-2025/admin/public/css/tahun/add_new_year.css">
    <style>
        .image-preview {
            display: none;
            margin-top: 10px;
            position: relative;
            width: fit-content;
        }

        .image-preview img {
            max-width: 150px;
            max-height: 100px;
            border: 2px solid #e3e6f0;
            border-radius: 4px;
            object-fit: contain;
        }

        .preview-remove {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74a3b;
            color: white;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .preview-remove:hover {
            background: #c5301c;
        }

        /* Match file input style with other form inputs */
        .file-input-wrapper {
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            background-color: #fff;
        }

        .file-input-wrapper input[type="file"]:focus {
            outline: none;
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .file-requirements {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #6c757d;
        }

        /* Hide the default file input button */
        .file-input-wrapper input[type="file"]::-webkit-file-upload-button {
            display: none;
        }

        .file-input-wrapper input[type="file"]::file-selector-button {
            display: none;
        }

        /* Add custom placeholder text */
        .file-input-wrapper input[type="file"]::before {
            content: 'Pilih file';
            display: inline-block;
            background: #4e73df;
            color: #fff;
            padding: 5px 15px;
            border-radius: 3px;
            margin-right: 10px;
            cursor: pointer;
        }

        .file-input-wrapper input[type="file"]:hover::before {
            background: #3a5bc7;
        }
    </style>
</head>

<body>
    <?php include '../../views/layout/sidebar.php' ?>

    <div class="main-content">
        <div class="header">
            <h2 class="greeting"><i class="fas fa-calendar-plus"></i> Tambah Tahun Akademik Baru</h2>
        </div>

        <div class="container-wrapper">
            <div class="new-year-container">
                <h2><i class="fas fa-info-circle"></i> Form Tambah Tahun Akademik</h2>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <div class="alert-content">
                            <i class="fas fa-check-circle"></i>
                            <span><?= $success_message ?></span>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <div class="alert-content">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= $error ?></span>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="year"><i class="fas fa-calendar-alt"></i> Tahun Akademik:</label>
                        <input type="number" id="year" name="year" min="2000" max="2100" placeholder="Contoh: 2025" required>
                    </div>

                    <div class="form-group">
                        <label for="cover_path"><i class="fas fa-image"></i> Cover Image:</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="cover_path" name="cover_path" accept=".jpg,.jpeg,.png" required onchange="previewImage(this)">
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <img src="#" alt="Preview" id="preview">
                            <button type="button" class="preview-remove" onclick="removePreview()" title="Hapus">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="file-requirements">
                            <i class="fas fa-info-circle"></i> Format yang diizinkan: JPG, PNG | Ukuran maksimal: 2MB
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sambutan"><i class="fas fa-quote-left"></i> Sambutan:</label>
                        <textarea id="sambutan" name="sambutan" placeholder="Masukkan teks sambutan dan link YouTube (jika ada) di sini..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-plus-circle"></i> Tambah Tahun
                        </button>
                        <button type="button" class="btn-cancel" onclick="window.location.href='empty.php'">
                            <i class="fas fa-times-circle"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/bts-smkn1lmj-2025/admin/public/js/sidebar.js"></script>
    <script>
        // Image Preview functionality
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('imagePreview');
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        function removePreview() {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('imagePreview');
            const fileInput = document.getElementById('cover_path');

            preview.src = '#';
            previewContainer.style.display = 'none';
            fileInput.value = '';
        }

        // Auto close alert setelah 5 detik
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>