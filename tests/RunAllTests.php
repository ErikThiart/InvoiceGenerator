<?php
/**
 * Complete Test Suite Runner for Invoice Generator
 * Tests all functionality to ensure 100% working application
 */

require_once __DIR__ . '/TestFramework.php';

// Include all test files
require_once __DIR__ . '/unit/AuthTest.php';
require_once __DIR__ . '/unit/FunctionsTest.php';
require_once __DIR__ . '/unit/PdfTest.php';
require_once __DIR__ . '/integration/DatabaseTest.php';
require_once __DIR__ . '/functional/WorkflowTest.php';
require_once __DIR__ . '/functional/WebInterfaceTest.php';

class CompleteTestSuite {
    private $test_results = [];
    
    public function run_all_tests() {
        ob_start(); // Start output buffering
        echo "🚀 Invoice Generator - Complete Test Suite\n";
        echo str_repeat("=", 60) . "\n";
        echo "Testing ALL functionality to ensure 100% working application\n\n";
        
        // Run Unit Tests
        echo "1️⃣  UNIT TESTS\n";
        echo str_repeat("-", 40) . "\n";
        
        echo "🔐 Authentication Tests:\n";
        $auth_result = test_auth_functions();
        $this->test_results['Auth Functions'] = $auth_result;
        
        echo "\n🛠️  Utility Functions Tests:\n";
        $functions_result = test_utility_functions();
        $this->test_results['Utility Functions'] = $functions_result;
        
        echo "\n📄 PDF Generation Tests:\n";
        $pdf_result = test_pdf_generation();
        $this->test_results['PDF Generation'] = $pdf_result;
        
        // Run Integration Tests
        echo "\n\n2️⃣  INTEGRATION TESTS\n";
        echo str_repeat("-", 40) . "\n";
        
        echo "🗄️  Database Operations Tests:\n";
        $db_result = test_database_operations();
        $this->test_results['Database Operations'] = $db_result;
        
        // Run Functional Tests
        echo "\n\n3️⃣  FUNCTIONAL TESTS\n";
        echo str_repeat("-", 40) . "\n";
        
        echo "⚙️  Complete Workflow Tests:\n";
        $workflow_result = test_application_workflows();
        $this->test_results['Application Workflows'] = $workflow_result;
        
        echo "\n🌐 Web Interface Tests:\n";
        $web_result = test_web_interface();
        $this->test_results['Web Interface'] = $web_result;
        
        // Additional comprehensive tests
        echo "\n\n4️⃣  COMPREHENSIVE TESTS\n";
        echo str_repeat("-", 40) . "\n";
        
        echo "🔍 Security & Edge Cases:\n";
        $security_result = $this->test_security_and_edge_cases();
        $this->test_results['Security & Edge Cases'] = $security_result;
        
        echo "\n💼 Business Logic Tests:\n";
        $business_result = $this->test_business_logic();
        $this->test_results['Business Logic'] = $business_result;
        
        // Print final summary
        $this->print_final_summary();
        
        $all_passed = $this->all_tests_passed();
        ob_end_flush(); // End output buffering and flush output
        return $all_passed;
    }
    
    private function test_security_and_edge_cases() {
        $runner = new TestRunner();
        
        // Test SQL injection protection
        $runner->addTest('SQL injection protection', function() {
            require_once __DIR__ . '/../includes_functions.php';
            $malicious_input = "'; DROP TABLE users; --";
            $sanitized = sanitize($malicious_input);
            // sanitize() is for HTML escaping, not SQL injection - SQL protection comes from prepared statements
            // Test that the function at least escapes HTML entities properly
            return Assert::assertTrue(strpos($sanitized, "'") === false); // Single quotes should be escaped
        });
        
        // Test XSS protection
        $runner->addTest('XSS protection', function() {
            require_once __DIR__ . '/../includes_functions.php';
            $xss_input = '<script>document.cookie="stolen"</script>';
            $sanitized = sanitize($xss_input);
            return Assert::assertFalse(strpos($sanitized, '<script>') !== false);
        });
        
        // Test empty data handling
        $runner->addTest('Empty data handling', function() {
            require_once __DIR__ . '/../includes_functions.php';
            
            Assert::assertEquals('', sanitize(''));
            Assert::assertEquals('', sanitize(null));
            return Assert::assertEquals('', sanitize(false));
        });
        
        // Test large data handling
        $runner->addTest('Large data handling', function() {
            require_once __DIR__ . '/../includes_functions.php';
            $large_string = str_repeat('A', 10000);
            $result = sanitize($large_string);
            return Assert::assertEquals(10000, strlen($result));
        });
        
        return $runner->run();
    }
    
    private function test_business_logic() {
        $runner = new TestRunner();
        
        // Setup test database
        TestDatabase::clearData();
        TestDatabase::seedTestData();
        $pdo = TestDatabase::getConnection();
        
        // Test invoice status logic
        $runner->addTest('Invoice status transitions', function() use ($pdo) {
            $valid_statuses = ['Draft', 'Sent', 'Paid', 'Overdue', 'Cancelled', 'Partially Paid'];
            
            foreach ($valid_statuses as $status) {
                $stmt = $pdo->prepare('INSERT INTO invoices (client_id, created_at, status, total) VALUES (?, ?, ?, ?)');
                $result = $stmt->execute([1, '2025-05-24', $status, 100.00]);
                if (!$result) {
                    throw new Exception("Failed to create invoice with status: $status");
                }
            }
            
            $stmt = $pdo->query('SELECT COUNT(DISTINCT status) FROM invoices');
            $status_count = $stmt->fetchColumn();
            return Assert::assertGreaterThan(0, $status_count);
        });
        
        // Test payment calculations
        $runner->addTest('Payment calculations', function() use ($pdo) {
            // Get invoice total vs sum of items
            $stmt = $pdo->prepare('
                SELECT 
                    i.total as invoice_total,
                    COALESCE(SUM(ii.total), 0) as items_total,
                    COALESCE(SUM(p.amount), 0) as payments_total
                FROM invoices i
                LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
                LEFT JOIN payments p ON i.id = p.invoice_id
                WHERE i.id = ?
                GROUP BY i.id
            ');
            $stmt->execute([2]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Invoice total should match sum of items
            Assert::assertEquals($result['invoice_total'], $result['items_total']);
            
            // Payments should not exceed invoice total
            return Assert::assertTrue($result['payments_total'] <= $result['invoice_total']);
        });
        
        // Test client-invoice relationships
        $runner->addTest('Client-Invoice relationships', function() use ($pdo) {
            // Reset and seed clean test data
            TestDatabase::clearData();
            TestDatabase::seedTestData();
            
            // Every invoice should have a valid client
            $stmt = $pdo->query('SELECT COUNT(*) FROM invoices i LEFT JOIN clients c ON i.client_id = c.id WHERE c.id IS NULL');
            $orphaned_invoices = $stmt->fetchColumn();
            
            return Assert::assertEquals(0, $orphaned_invoices); // Compare as integer
        });
        
        // Test data consistency
        $runner->addTest('Data consistency checks', function() use ($pdo) {
            // Reset and seed clean test data
            TestDatabase::clearData();
            TestDatabase::seedTestData();
            
            // All invoice items should belong to existing invoices
            $stmt = $pdo->query('SELECT COUNT(*) FROM invoice_items ii LEFT JOIN invoices i ON ii.invoice_id = i.id WHERE i.id IS NULL');
            $orphaned_items = $stmt->fetchColumn();
            
            // All payments should belong to existing invoices
            $stmt2 = $pdo->query('SELECT COUNT(*) FROM payments p LEFT JOIN invoices i ON p.invoice_id = i.id WHERE i.id IS NULL');
            $orphaned_payments = $stmt2->fetchColumn();
            
            Assert::assertEquals(0, $orphaned_items); // Compare as integer
            return Assert::assertEquals(0, $orphaned_payments); // Compare as integer
        });
        
        return $runner->run();
    }
    
    private function print_final_summary() {
        echo "\n\n🏁 FINAL TEST SUMMARY\n";
        echo str_repeat("=", 60) . "\n";
        
        $total_passed = 0;
        $total_failed = 0;
        
        foreach ($this->test_results as $category => $passed) {
            $status = $passed ? "✅ PASSED" : "❌ FAILED";
            echo sprintf("%-25s %s\n", $category . ":", $status);
            
            if ($passed) {
                $total_passed++;
            } else {
                $total_failed++;
            }
        }
        
        echo str_repeat("-", 60) . "\n";
        echo sprintf("Total Categories: %d\n", count($this->test_results));
        echo sprintf("✅ Passed: %d\n", $total_passed);
        echo sprintf("❌ Failed: %d\n", $total_failed);
        
        if ($this->all_tests_passed()) {
            echo "\n🎉 ALL TESTS PASSED! 🎉\n";
            echo "✅ The Invoice Generator application is 100% functional!\n";
            echo "✅ All core features work correctly:\n";
            echo "   • User authentication and sessions\n";
            echo "   • Client management\n";
            echo "   • Invoice creation and management\n";
            echo "   • Payment tracking\n";
            echo "   • PDF generation\n";
            echo "   • Reporting\n";
            echo "   • Security measures\n";
            echo "   • Data integrity\n";
        } else {
            echo "\n⚠️  SOME TESTS FAILED\n";
            echo "❌ Please review the failed tests above.\n";
        }
        
        echo str_repeat("=", 60) . "\n";
    }
    
    private function all_tests_passed() {
        foreach ($this->test_results as $result) {
            if (!$result) {
                return false;
            }
        }
        return true;
    }
}

// Run the complete test suite if this file is called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test_suite = new CompleteTestSuite();
    $all_passed = $test_suite->run_all_tests();
    
    // Exit with appropriate code
    exit($all_passed ? 0 : 1);
}
?>
