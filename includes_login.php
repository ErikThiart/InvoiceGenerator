<?php
// User registration and login page
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes_functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            login($user['id']);
            redirect('index.php');
        } else {
            $error = 'Invalid credentials.';
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
    <title>Login - Invoice Generator</title>
    <link rel="stylesheet" href="assets_css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Login</h1>
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
            <button type="submit">Login</button>
        </form>
        <p><a href="index.php?page=register">Register</a></p>
    </div>
    <footer>
        <small>&copy; <?php echo date('Y'); ?> Invoice Generator</small>
    </footer>
</body>
</html>
