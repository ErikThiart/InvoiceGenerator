<?php
/**
 * Unit Tests for Utility Functions
 */

require_once __DIR__ . '/../TestFramework.php';
require_once __DIR__ . '/../../includes_functions.php';

function test_utility_functions() {
    $runner = new TestRunner();
    
    // Test sanitize function
    $runner->addTest('sanitize - normal string', function() {
        $result = sanitize('Hello World');
        return Assert::assertEquals('Hello World', $result);
    });
    
    $runner->addTest('sanitize - HTML characters', function() {
        $result = sanitize('<script>alert("xss")</script>');
        return Assert::assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $result);
    });
    
    $runner->addTest('sanitize - with whitespace', function() {
        $result = sanitize('  trimmed  ');
        return Assert::assertEquals('trimmed', $result);
    });
    
    $runner->addTest('sanitize - non-string input', function() {
        $result = sanitize(123);
        return Assert::assertEquals('', $result);
    });
    
    $runner->addTest('sanitize - null input', function() {
        $result = sanitize(null);
        return Assert::assertEquals('', $result);
    });
    
    $runner->addTest('sanitize - array input', function() {
        $result = sanitize(['test']);
        return Assert::assertEquals('', $result);
    });
    
    return $runner->run();
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    test_utility_functions();
}
?>
