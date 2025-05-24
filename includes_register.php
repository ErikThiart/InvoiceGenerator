<?php
// User registration page
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes_functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($email && $password && $confirm) {
        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)')->execute([$email, $hash]);
                redirect('index.php?page=login');
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Invoice Generator</title>
    <link rel="stylesheet" href="assets_css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Register</h1>
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
        <?php if ($error): ?><p style="color:red;"> <?= $error ?> </p><?php endif; ?>
        <form method="post">
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Password: <input type="password" name="password" required></label><br>
            <label>Confirm Password: <input type="password" name="confirm" required></label><br>
            <button type="submit">Register</button>
        </form>
        <p><a href="index.php?page=login">Login</a></p>
    </div>
    <footer>
        <small>&copy; <?php echo date('Y'); ?> Invoice Generator</small>
    </footer>
</body>
</html>
