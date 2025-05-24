<?php
// Download Invoice as PDF (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes_pdf_generator.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT i.*, c.name as client_name FROM invoices i JOIN clients c ON i.client_id = c.id WHERE i.id = ?');
$stmt->execute([$id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
$items = [];
$client = [];
if ($invoice) {
    $item_stmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
    $item_stmt->execute([$id]);
    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    $client_stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
    $client_stmt->execute([$invoice['client_id']]);
    $client = $client_stmt->fetch(PDO::FETCH_ASSOC);
    $pdf = generate_invoice_pdf($invoice, $items, $client);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoice_' . $id . '.pdf"');
    $pdf->Output('I', 'invoice_' . $id . '.pdf');
    exit;
} else {
    $msg = 'Invoice not found.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Invoice - Invoice Generator</title>
    <link rel="stylesheet" href="assets_css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Download Invoice</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="index.php?page=invoices">Invoices</a>
            <a href="index.php?page=clients">Clients</a>
            <a href="index.php?page=dashboard">Dashboard</a>
        </nav>
    </header>
    <div class="container">
        <h2>Status</h2>
        <p><?= isset($msg) ? htmlspecialchars($msg) : '' ?></p>
        <p><a href="index.php?page=invoices">Back to Invoices</a></p>
    </div>
    <footer>
        <small>&copy; <?php echo date('Y'); ?> Invoice Generator</small>
    </footer>
</body>
</html>
