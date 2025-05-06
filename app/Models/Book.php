<?php
class Book {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Create new book
    public function create($judul, $penerbit, $category_id, $tahun_akademik_id, $cover_path, $content_path) {
        $stmt = $this->pdo->prepare("
            INSERT INTO books 
            (judul, penerbit, category_id, tahun_akademik_id, cover_path, content_path) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$judul, $penerbit, $category_id, $tahun_akademik_id, $cover_path, $content_path]);
    }

     // Get books by year
     public function getBooksByAcademicYear($tahun) {
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
    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT b.*, c.name AS category_name, t.tahun
            FROM books b
            JOIN categories c ON b.category_id = c.id
            JOIN tahun_akademik t ON b.tahun_akademik_id = t.id
        ");
        return $stmt->fetchAll();
    }

    // Get single book by ID
    public function getById($id) {
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



    // Update book
    // public function update($id, $judul, $penerbit, $kategori_id, $cover_path = null, $content_path = null) {
    //     if ($cover_path && $content_path) {
    //         $stmt = $this->pdo->prepare("UPDATE books SET judul = ?, penerbit = ?, kategori_id = ?, cover_path = ?, content_path = ? WHERE id = ?");
    //         return $stmt->execute([$judul, $penerbit, $kategori_id, $cover_path, $content_path, $id]);
    //     } elseif ($cover_path) {
    //         $stmt = $this->pdo->prepare("UPDATE books SET judul = ?, penerbit = ?, kategori_id = ?, cover_path = ? WHERE id = ?");
    //         return $stmt->execute([$judul, $penerbit, $kategori_id, $cover_path, $id]);
    //     } elseif ($content_path) {
    //         $stmt = $this->pdo->prepare("UPDATE books SET judul = ?, penerbit = ?, kategori_id = ?, content_path = ? WHERE id = ?");
    //         return $stmt->execute([$judul, $penerbit, $kategori_id, $content_path, $id]);
    //     } else {
    //         $stmt = $this->pdo->prepare("UPDATE books SET judul = ?, penerbit = ?, kategori_id = ? WHERE id = ?");
    //         return $stmt->execute([$judul, $penerbit, $kategori_id, $id]);
    //     }
    // }

    // Delete book
    // public function delete($id) {
    //     $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = ?");
    //     return $stmt->execute([$id]);
    // }
}
?>