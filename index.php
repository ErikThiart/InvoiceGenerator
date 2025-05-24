<?php
// index.php - Main entry point for Invoice Generator

// Load configuration files
define('CONFIG_PATH', __DIR__ . '/');
$config = [
    'app' => require CONFIG_PATH . 'config_app.php',
    'db' => require CONFIG_PATH . 'config_database.php',
    'email' => require CONFIG_PATH . 'config_email.php',
];

// Simple router (expand as needed)
$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'login':
        include __DIR__ . '/includes_login.php';
        break;
    case 'register':
        include __DIR__ . '/includes_register.php';
        break;
    case 'logout':
        require_once __DIR__ . '/includes_auth.php';
        require_once __DIR__ . '/includes_functions.php';
        
        // Ensure session is started before logout
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        logout();
        redirect('index.php?logged_out=1');
        break;
    case 'dashboard':
        include __DIR__ . '/includes_dashboard.php';
        break;
    case 'invoices':
        include __DIR__ . '/includes_invoices.php';
        break;
    case 'clients':
        include __DIR__ . '/includes_clients.php';
        break;
    case 'payments':
        include __DIR__ . '/includes_payments.php';
        break;
    case 'reports':
        include __DIR__ . '/includes_reports.php';
        break;
    case 'create_invoice':
        include __DIR__ . '/includes_create_invoice.php';
        break;
    case 'create_client':
        include __DIR__ . '/includes_create_client.php';
        break;
    case 'view_invoice':
        include __DIR__ . '/includes_view_invoice.php';
        break;
    case 'view_client':
        include __DIR__ . '/includes_view_client.php';
        break;
    case 'download_invoice':
        include __DIR__ . '/includes_download_invoice.php';
        break;
    case 'send_invoice':
        include __DIR__ . '/includes_send_invoice.php';
        break;
    case 'add_payment':
        include __DIR__ . '/includes_add_payment.php';
        break;
    case 'home':
        include __DIR__ . '/includes_homepage.php';
        break;
    default:
        include __DIR__ . '/includes_dashboard.php';
        break;
}

// Make sure there's NO additional content after this point
// Remove any HTML, dashboard code, or other content that might be below
?>
