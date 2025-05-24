<?php
// Simple test script to verify logout functionality
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes_functions.php';

echo "Testing logout functionality...\n";

// Test 1: Check if session starts properly
echo "1. Starting session...\n";
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "   ✓ Session is active\n";
} else {
    echo "   ✗ Session is not active\n";
}

// Test 2: Simulate login
echo "2. Simulating login...\n";
$_SESSION['user_id'] = 123;
if (is_logged_in()) {
    echo "   ✓ User is logged in (user_id: " . $_SESSION['user_id'] . ")\n";
} else {
    echo "   ✗ User login failed\n";
}

// Test 3: Test logout
echo "3. Testing logout...\n";
logout();
if (!is_logged_in()) {
    echo "   ✓ User successfully logged out\n";
} else {
    echo "   ✗ Logout failed - user still logged in\n";
}

// Test 4: Check session variables
echo "4. Checking session variables...\n";
if (empty($_SESSION)) {
    echo "   ✓ Session variables cleared\n";
} else {
    echo "   ✗ Session variables still exist: " . print_r($_SESSION, true) . "\n";
}

echo "\nTest completed!\n";
?>
