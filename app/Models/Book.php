<?php
class Book {
 private $pdo;
 
 public function __construct($pdo) {
     $this->pdo = $pdo;
     // Aktifkan mode error exception
     $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 }
 
 public function create($judul, $penerbit, $category_id, $cover_path, $content_path) {
     try {
         $stmt = $this->pdo->prepare("INSERT INTO books (judul, penerbit, category_id, cover_path, content_path) 
                                     VALUES (?, ?, ?, ?, ?)");
         return $stmt->execute([
             htmlspecialchars($judul),
             htmlspecialchars($penerbit),
             intval($category_id),
             $cover_path,
             "content/$content_path"
         ]);
     } catch (PDOException $e) {
         error_log("Error creating book: " . $e->getMessage());
         return false;
     }
 }
 
 public function getAll() {
     try {
         $stmt = $this->pdo->prepare("SELECT books.*, categories.name as kategori_name
                                     FROM books
                                     LEFT JOIN categories ON books.category_id = categories.id");
         $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     } catch (PDOException $e) {
         error_log("Error getting all books: " . $e->getMessage());
         return [];
     }
 }
 
 public function getById($id) {
     try {
         $stmt = $this->pdo->prepare("SELECT books.*, categories.name as kategori_name
                                     FROM books
                                     LEFT JOIN categories ON books.category_id = categories.id
                                     WHERE books.id = ?");
         $stmt->execute([$id]);
         return $stmt->fetch(PDO::FETCH_ASSOC);
     } catch (PDOException $e) {
         error_log("Error getting book by ID: " . $e->getMessage());
         return null;
     }
 }
}
?>