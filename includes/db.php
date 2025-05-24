<?php
// Database connection using SQLite
$dbPath = __DIR__ . '/../invoice_generator.sqlite';
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
