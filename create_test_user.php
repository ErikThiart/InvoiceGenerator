<?php
// Script to create a test user with a valid bcrypt password in SQLite

// Ensure this script is run from the command line and not via a web browser
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

try {
    $db = new PDO('sqlite:invoice_generator.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("DELETE FROM users WHERE email = 'test@example.com'");
    $hash = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
    $stmt->execute(['test@example.com', $hash]);
    echo "Test user created successfully.\n";
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
