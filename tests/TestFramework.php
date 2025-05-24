<?php
/**
 * Simple PHP Test Framework for Invoice Generator
 * Provides basic testing functionality without external dependencies
 */

class TestRunner {
    private $tests = [];
    private $passed = 0;
    private $failed = 0;
    private $errors = [];
    
    public function addTest($name, $callable) {
        $this->tests[$name] = $callable;
    }
    
    public function run() {
        echo "🧪 Running Invoice Generator Tests\n";
        echo str_repeat("=", 50) . "\n\n";
        
        foreach ($this->tests as $name => $test) {
            echo "Testing: $name ... ";
            try {
                $result = $test();
                if ($result === true) {
                    echo "✅ PASS\n";
                    $this->passed++;
                } else {
                    echo "❌ FAIL\n";
                    $this->failed++;
                    $this->errors[] = "$name: " . ($result ?: 'Test returned false');
                }
            } catch (Exception $e) {
                echo "❌ ERROR\n";
                $this->failed++;
                $this->errors[] = "$name: " . $e->getMessage();
            }
        }
        
        return $this->printSummary();
    }
    
    private function printSummary() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 Test Summary:\n";
        echo "✅ Passed: {$this->passed}\n";
        echo "❌ Failed: {$this->failed}\n";
        echo "📈 Total:  " . ($this->passed + $this->failed) . "\n";
        
        if (!empty($this->errors)) {
            echo "\n❌ Errors:\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
        }
        
        echo "\n";
        return $this->failed === 0;
    }
}

class Assert {
    public static function assertEquals($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            $msg = $message ?: "Expected '$expected', got '$actual'";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertTrue($condition, $message = '') {
        if (!$condition) {
            $msg = $message ?: "Expected true, got false";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertFalse($condition, $message = '') {
        if ($condition) {
            $msg = $message ?: "Expected false, got true";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertNotNull($value, $message = '') {
        if ($value === null) {
            $msg = $message ?: "Expected non-null value";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertNull($value, $message = '') {
        if ($value !== null) {
            $msg = $message ?: "Expected null value";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertContains($needle, $haystack, $message = '') {
        if (!in_array($needle, $haystack)) {
            $msg = $message ?: "Array does not contain expected value";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertArrayHasKey($key, $array, $message = '') {
        if (!array_key_exists($key, $array)) {
            $msg = $message ?: "Array does not contain expected key '$key'";
            throw new Exception($msg);
        }
        return true;
    }
    
    public static function assertGreaterThan($expected, $actual, $message = '') {
        if ($actual <= $expected) {
            $msg = $message ?: "Expected value greater than $expected, got $actual";
            throw new Exception($msg);
        }
        return true;
    }
}

class TestDatabase {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            // Create in-memory test database
            self::$pdo = new PDO('sqlite::memory:');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::createSchema();
        }
        return self::$pdo;
    }
    
    private static function createSchema() {
        // Enable foreign key constraints for SQLite
        self::$pdo->exec("PRAGMA foreign_keys = ON");
        $schema = file_get_contents(__DIR__ . '/../schema.sql');
        self::$pdo->exec($schema);
    }
    
    public static function seedTestData() {
        $pdo = self::getConnection();
        
        // Create test user
        $pdo->exec("INSERT INTO users (email, password) VALUES ('test@example.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "')");
        
        // Create test clients
        $pdo->exec("INSERT INTO clients (name, email, phone, company) VALUES 
            ('John Doe', 'john@example.com', '555-1234', 'Acme Corp'),
            ('Jane Smith', 'jane@example.com', '555-5678', 'Tech Solutions')");
        
        // Create test invoices
        $pdo->exec("INSERT INTO invoices (client_id, created_at, status, total) VALUES 
            (1, '2025-05-01', 'Draft', 1000.00),
            (2, '2025-05-15', 'Sent', 2500.00)");
        
        // Create test invoice items
        $pdo->exec("INSERT INTO invoice_items (invoice_id, description, quantity, rate, total) VALUES 
            (1, 'Web Development', 10, 100.00, 1000.00),
            (2, 'Consulting', 5, 200.00, 1000.00),
            (2, 'Support', 15, 100.00, 1500.00)");
        
        // Create test payments
        $pdo->exec("INSERT INTO payments (invoice_id, amount, payment_date, method, reference) VALUES 
            (2, 1000.00, '2025-05-20', 'Bank Transfer', 'REF123')");
    }
    
    public static function clearData() {
        $pdo = self::getConnection();
        // Temporarily disable foreign key constraints for cleanup
        $pdo->exec("PRAGMA foreign_keys = OFF");
        $pdo->exec("DELETE FROM payments");
        $pdo->exec("DELETE FROM invoice_items");
        $pdo->exec("DELETE FROM invoices");
        $pdo->exec("DELETE FROM clients");
        $pdo->exec("DELETE FROM users");
        // Reset auto-increment counters
        $pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ('users', 'clients', 'invoices', 'invoice_items', 'payments')");
        // Re-enable foreign key constraints
        $pdo->exec("PRAGMA foreign_keys = ON");
    }
}
?>
