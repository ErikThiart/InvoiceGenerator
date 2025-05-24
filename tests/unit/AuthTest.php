<?php
/**
 * Unit Tests for Authentication Functions
 */

require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../includes_auth.php';

function test_auth_functions() {
    $runner = new TestRunner();
    
    // Test is_logged_in function
    $runner->addTest('is_logged_in - not logged in', function() {
        unset($_SESSION['user_id']);
        return Assert::assertFalse(is_logged_in());
    });
    
    $runner->addTest('is_logged_in - logged in', function() {
        $_SESSION['user_id'] = 123;
        return Assert::assertTrue(is_logged_in());
    });
    
    // Test login function
    $runner->addTest('login function', function() {
        login(456);
        return Assert::assertEquals(456, $_SESSION['user_id']);
    });
    
    // Test logout function
    $runner->addTest('logout function', function() {
        $_SESSION['user_id'] = 789;
        $_SESSION['other_data'] = 'test';
        logout();
        return Assert::assertTrue(empty($_SESSION));
    });
    
    return $runner->run();
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    test_auth_functions();
}
?>
