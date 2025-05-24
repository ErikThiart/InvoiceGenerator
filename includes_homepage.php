<?php
require_once __DIR__ . '/includes_auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generator</title>
    <link rel="stylesheet" href="assets_css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .main-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .main-nav a {
            color: #4f8cff;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.08rem;
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            transition: background 0.18s, color 0.18s;
        }
        .main-nav a:hover, .main-nav a.active {
            background: #e8f0fe;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <header>
        <h1>Invoice Generator</h1>
        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="index.php?page=invoices">Invoices</a>
            <a href="index.php?page=clients">Clients</a>
            <a href="index.php?page=payments">Payments</a>
            <a href="index.php?page=reports">Reports</a>
            <?php if (is_logged_in()): ?>
                <a href="index.php?page=logout">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login">Login</a>
                <a href="index.php?page=register">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <?php if (isset($_GET['logged_out']) && $_GET['logged_out'] == '1'): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                ✓ You have been successfully logged out.
            </div>
        <?php endif; ?>
        <h2>Welcome to Invoice Generator</h2>
        <p>A professional PHP-based invoice generation and management system for small businesses and freelancers.</p>
    </div>
    <footer>
        <small>&copy; <?php echo date('Y'); ?> Invoice Generator</small>
    </footer>
</body>
</html>
