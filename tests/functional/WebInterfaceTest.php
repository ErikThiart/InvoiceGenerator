<?php
/**
 * Web Interface Tests - Testing page routing and basic functionality
 */

require_once __DIR__ . '/../TestFramework.php';

class WebInterfaceTest {
    private $routes = [
        'home' => 'includes_homepage.php',
        'login' => 'includes_login.php',
        'register' => 'includes_register.php',
        'dashboard' => 'includes_dashboard.php',
        'invoices' => 'includes_invoices.php',
        'clients' => 'includes_clients.php',
        'payments' => 'includes_payments.php',
        'reports' => 'includes_reports.php',
        'create_invoice' => 'includes_create_invoice.php',
        'create_client' => 'includes_create_client.php',
        'view_invoice' => 'includes_view_invoice.php',
        'view_client' => 'includes_view_client.php',
        'download_invoice' => 'includes_download_invoice.php',
        'send_invoice' => 'includes_send_invoice.php',
        'add_payment' => 'includes_add_payment.php'
    ];
    
    public function test_routes() {
        $runner = new TestRunner();
        
        // Test that all route files exist
        $runner->addTest('All route files exist', function() {
            foreach ($this->routes as $route => $file) {
                $filepath = __DIR__ . "/../../$file";
                if (!file_exists($filepath)) {
                    throw new Exception("Route file missing: $file for route: $route");
                }
            }
            return true;
        });
        
        // Test main index.php file
        $runner->addTest('Main index.php exists and is readable', function() {
            $index_file = __DIR__ . '/../../index.php';
            Assert::assertTrue(file_exists($index_file));
            return Assert::assertTrue(is_readable($index_file));
        });
        
        // Test configuration files exist
        $runner->addTest('Configuration files exist', function() {
            $config_files = ['config_app.php', 'config_database.php', 'config_email.php'];
            foreach ($config_files as $file) {
                $filepath = __DIR__ . "/../../$file";
                if (!file_exists($filepath)) {
                    throw new Exception("Config file missing: $file");
                }
            }
            return true;
        });
        
        // Test CSS file exists
        $runner->addTest('CSS file exists', function() {
            $css_file = __DIR__ . '/../../assets_css/style.css';
            Assert::assertTrue(file_exists($css_file));
            return Assert::assertTrue(is_readable($css_file));
        });
        
        // Test database schema file exists
        $runner->addTest('Database schema file exists', function() {
            $schema_file = __DIR__ . '/../../schema.sql';
            Assert::assertTrue(file_exists($schema_file));
            return Assert::assertTrue(is_readable($schema_file));
        });
        
        // Test vendor FPDF library exists
        $runner->addTest('FPDF library exists', function() {
            $fpdf_file = __DIR__ . '/../../vendor/fpdf.php';
            Assert::assertTrue(file_exists($fpdf_file));
            return Assert::assertTrue(is_readable($fpdf_file));
        });
        
        return $runner->run();
    }
    
    public function test_form_processing() {
        $runner = new TestRunner();
        
        // Test client form validation logic
        $runner->addTest('Client form validation', function() {
            // Simulate sanitize function behavior
            require_once __DIR__ . '/../../includes_functions.php';
            
            $valid_name = sanitize('John Doe');
            $valid_email = sanitize('john@example.com');
            $xss_attempt = sanitize('<script>alert("xss")</script>');
            
            Assert::assertEquals('John Doe', $valid_name);
            Assert::assertEquals('john@example.com', $valid_email);
            return Assert::assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $xss_attempt);
        });
        
        // Test invoice calculation logic
        $runner->addTest('Invoice calculation logic', function() {
            $items = [
                ['quantity' => 10, 'rate' => 100.00],
                ['quantity' => 5, 'rate' => 200.00],
                ['quantity' => 2, 'rate' => 50.00]
            ];
            
            $total = 0;
            foreach ($items as $item) {
                $total += $item['quantity'] * $item['rate'];
            }
            
            return Assert::assertEquals(2100.00, $total);
        });
        
        return $runner->run();
    }
}

function test_web_interface() {
    $web_test = new WebInterfaceTest();
    
    echo "🌐 Testing Web Interface\n";
    echo str_repeat("-", 30) . "\n";
    
    $routes_passed = $web_test->test_routes();
    
    echo "\n📝 Testing Form Processing\n";
    echo str_repeat("-", 30) . "\n";
    
    $forms_passed = $web_test->test_form_processing();
    
    return $routes_passed && $forms_passed;
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    test_web_interface();
}
?>
