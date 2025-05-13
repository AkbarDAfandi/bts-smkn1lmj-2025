<?php
class Book
{
 private $pdo;

 public function __construct($pdo)
 {
  $this->pdo = $pdo;
 }

 // Create new book
 public function create($judul, $penerbit, $category_id, $tahun_akademik_id, $cover_path, $content_path)
 {
  $stmt = $this->pdo->prepare("
            INSERT INTO books 
            (judul, penerbit, category_id, tahun_akademik_id, cover_path, content_path) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
  return $stmt->execute([$judul, $penerbit, $category_id, $tahun_akademik_id, $cover_path, $content_path]);
 }

 // Get books by year
 public function getBooksByAcademicYear($tahun)
 {
  $stmt = $this->pdo->prepare("
            SELECT b.*, c.name AS category_name, t.tahun
            FROM books b
            JOIN categories c ON b.category_id = c.id
            JOIN tahun_akademik t ON b.tahun_akademik_id = t.id
            WHERE t.tahun = ?
        ");
  $stmt->execute([$tahun]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
 }

 // Get all books
 public function getAll()
 {
  $stmt = $this->pdo->query("
            SELECT b.*, c.name AS category_name, t.tahun
            FROM books b
            JOIN categories c ON b.category_id = c.id
            JOIN tahun_akademik t ON b.tahun_akademik_id = t.id
        ");
  return $stmt->fetchAll();
 }

 // Get single book by ID
 public function getById($id)
 {
  $stmt = $this->pdo->prepare("
            SELECT b.*, c.name AS category_name, t.tahun
            FROM books b
            JOIN categories c ON b.category_id = c.id
            JOIN tahun_akademik t ON b.tahun_akademik_id = t.id
            WHERE b.id = ?
        ");
  $stmt->execute([$id]);
  return $stmt->fetch();
 }

 // category book
 public function getBooksByCategoryAndYear($categoryId, $year)
 {
  $stmt = $this->pdo->prepare("SELECT b.* FROM books b 
                                WHERE b.category_id = ? AND b.tahun_akademik_id = ?
                                ORDER BY b.judul ASC");
  $stmt->execute([$categoryId, $year]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
 }


 // edit book
 public function update($id, $judul, $penerbit, $category_id, $tahun_akademik_id, $cover_path = null, $content_path = null)
 {
  $sql = "UPDATE books SET 
                judul = :judul, 
                penerbit = :penerbit, 
                category_id = :category_id, 
                tahun_akademik_id = :tahun_akademik_id, 
                updated_at = CURRENT_TIMESTAMP";

  // Add cover path to query if provided
  if ($cover_path !== null) {
   $sql .= ", cover_path = :cover_path";
  }

  // Add content path to query if provided
  if ($content_path !== null) {
   $sql .= ", content_path = :content_path";
  }

  $sql .= " WHERE id = :id";

  $stmt = $this->pdo->prepare($sql);

  // Bind parameters
  $params = [
   ':judul' => $judul,
   ':penerbit' => $penerbit,
   ':category_id' => $category_id,
   ':tahun_akademik_id' => $tahun_akademik_id,
   ':id' => $id
  ];

  if ($cover_path !== null) {
   $params[':cover_path'] = $cover_path;
  }

  if ($content_path !== null) {
   $params[':content_path'] = $content_path;
  }

  return $stmt->execute($params);
 }
// ... [kode sebelumnya tetap sama]

// Delete book by ID
public function delete($id)
{
    try {
        // Validasi input lebih ketat
        if (!is_numeric($id) || $id <= 0 || !filter_var($id, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("ID buku tidak valid");
        }

        // Mulai transaksi
        $this->pdo->beginTransaction();

        // Dapatkan data buku
        $book = $this->getById($id);
        if (!$book) {
            throw new RuntimeException("Buku tidak ditemukan");
        }

        // Simpan tahun akademik untuk redirect
        $tahun_akademik = $book['tahun'];

        // Hapus dari database
        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = ?");
        if (!$stmt->execute([$id])) {
            throw new RuntimeException("Gagal menghapus data buku dari database");
        }

        // Hapus file terkait jika ada
        $filesDeleted = true;
        if (!empty($book['cover_path']) && file_exists($book['cover_path'])) {
            if (!@unlink($book['cover_path'])) {
                $filesDeleted = false;
                error_log("Gagal menghapus cover: " . $book['cover_path']);
            }
        }
        
        if (!empty($book['content_path']) && file_exists($book['content_path'])) {
            if (!@unlink($book['content_path'])) {
                $filesDeleted = false;
                error_log("Gagal menghapus konten: " . $book['content_path']);
            }
        }

        // Commit transaksi
        $this->pdo->commit();

        return [
            'success' => true,
            'tahun_akademik' => $tahun_akademik,
            'files_deleted' => $filesDeleted
        ];
    } catch (Exception $e) {
        $this->pdo->rollBack();
        error_log("Error deleting book: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
}