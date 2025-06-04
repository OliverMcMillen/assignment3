<?php
$host = 'localhost';
$dbname = 'cosc436db';
$user = 'root';
$pass = '';
// Attempt to create a new PDO (PHP Data Objects) instance to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
