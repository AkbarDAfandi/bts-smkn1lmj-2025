<?php
class Book {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Create new book
    public function create($judul, $penerbit, $category_id, $cover_path, $content_path) {
        $stmt = $this->pdo->prepare("INSERT INTO books (judul, penerbit, category_id, cover_path, content_path) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$judul, $penerbit, $category_id, $cover_path, $content_path]);
    }

    // Get all books
    public function getAll() {
        $stmt = $this->pdo->query("SELECT books.*, categories.name as category_name 
                                 FROM books 
                                 JOIN categories ON books.category_id = categories.id");
        return $stmt->fetchAll();
    }

    // Get single book by ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT books.*, categories.name as category_name 
                                   FROM books 
                                   JOIN categories ON books.category_id = categories.id 
                                   WHERE books.id = ?");
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