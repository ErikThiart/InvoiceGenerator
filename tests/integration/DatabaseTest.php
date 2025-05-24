<?php
/**
 * Integration Tests for Database Operations
 */

require_once __DIR__ . '/../TestFramework.php';

function test_database_operations() {
    $runner = new TestRunner();
    
    // Setup test database
    TestDatabase::clearData();
    TestDatabase::seedTestData();
    $pdo = TestDatabase::getConnection();
    
    // Test user operations
    $runner->addTest('User creation and retrieval', function() use ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        return Assert::assertEquals(1, $count);
    });
    
    $runner->addTest('User login credentials', function() use ($pdo) {
        $stmt = $pdo->prepare("SELECT email, password FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Assert::assertEquals('test@example.com', $user['email']);
        return Assert::assertTrue(password_verify('password123', $user['password']));
    });
    
    // Test client operations
    $runner->addTest('Client creation', function() use ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
        $count = $stmt->fetchColumn();
        return Assert::assertEquals(2, $count);
    });
    
    $runner->addTest('Client data integrity', function() use ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
        $stmt->execute(['john@example.com']);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Assert::assertEquals('John Doe', $client['name']);
        Assert::assertEquals('555-1234', $client['phone']);
        return Assert::assertEquals('Acme Corp', $client['company']);
    });
    
    // Test invoice operations
    $runner->addTest('Invoice creation', function() use ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM invoices");
        $count = $stmt->fetchColumn();
        return Assert::assertEquals(2, $count);
    });
    
    $runner->addTest('Invoice-Client relationship', function() use ($pdo) {
        $stmt = $pdo->prepare("SELECT i.*, c.name as client_name FROM invoices i JOIN clients c ON i.client_id = c.id WHERE i.id = ?");
        $stmt->execute([1]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Assert::assertEquals(1000.00, $invoice['total']);
        return Assert::assertEquals('John Doe', $invoice['client_name']);
    });
    
    // Test invoice items
    $runner->addTest('Invoice items creation', function() use ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM invoice_items");
        $count = $stmt->fetchColumn();
        return Assert::assertEquals(3, $count);
    });
    
    $runner->addTest('Invoice items calculation', function() use ($pdo) {
        $stmt = $pdo->prepare("SELECT SUM(total) as invoice_total FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([2]);
        $total = $stmt->fetchColumn();
        return Assert::assertEquals(2500.00, $total);
    });
    
    // Test payments
    $runner->addTest('Payment recording', function() use ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM payments");
        $count = $stmt->fetchColumn();
        return Assert::assertEquals(1, $count);
    });
    
    $runner->addTest('Payment data integrity', function() use ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE invoice_id = ?");
        $stmt->execute([2]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Assert::assertEquals(1000.00, $payment['amount']);
        Assert::assertEquals('Bank Transfer', $payment['method']);
        return Assert::assertEquals('REF123', $payment['reference']);
    });
    
    // Test foreign key constraints
    $runner->addTest('Foreign key constraints work', function() use ($pdo) {
        try {
            // Try to insert invoice with non-existent client
            $stmt = $pdo->prepare("INSERT INTO invoices (client_id, created_at, status, total) VALUES (?, ?, ?, ?)");
            $stmt->execute([999, '2025-05-24', 'Draft', 100.00]);
            return false; // Should not reach here
        } catch (PDOException $e) {
            return Assert::assertTrue(true); // Foreign key constraint worked
        }
    });
    
    return $runner->run();
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    test_database_operations();
}
?>
