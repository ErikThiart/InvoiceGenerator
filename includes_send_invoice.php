<?php
// Send Invoice page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes_functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT i.*, c.email as client_email, c.name as client_name FROM invoices i JOIN clients c ON i.client_id = c.id WHERE i.id = ?');
$stmt->execute([$id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
$items = [];
if ($invoice) {
    $item_stmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
    $item_stmt->execute([$id]);
    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    // Compose email
    $to = $invoice['client_email'];
    $subject = 'Invoice #' . $invoice['id'] . ' from Invoice Generator';
    $body = "Dear {$invoice['client_name']},\n\nPlease find your invoice attached.\n\n";
    $body .= "Invoice Total: {$invoice['total']}\nStatus: {$invoice['status']}\n";
    $body .= "Thank you.";
    // Send email (no attachment in this placeholder)
    $sent = send_invoice_email($to, $subject, $body);
    $msg = $sent ? 'Invoice email sent.' : 'Failed to send email.';
} else {
    $msg = 'Invoice not found.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Invoice - Invoice Generator</title>
    <link rel="stylesheet" href="assets_css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Send Invoice</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="index.php?page=invoices">Invoices</a>
            <a href="index.php?page=clients">Clients</a>
            <a href="index.php?page=dashboard">Dashboard</a>
        </nav>
    </header>
    <div class="container">
        <h2>Status</h2>
        <p><?= htmlspecialchars($msg) ?></p>
        <p><a href="index.php?page=invoices">Back to Invoices</a></p>
    </div>
    <footer>
        <small>&copy; <?php echo date('Y'); ?> Invoice Generator</small>
    </footer>
</body>
</html>
