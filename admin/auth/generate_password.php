<?php
// Password yang ingin kamu hash
$password = 'adminbtssmkn1lmj2025';

// Buat hash-nya
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Tampilkan hasil hash
echo $hashedPassword;
?>
