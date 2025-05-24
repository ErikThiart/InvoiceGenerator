<?php
// View Invoice page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('
    SELECT i.*, c.name as client_name, c.email as client_email, c.address as client_address,
           COALESCE(SUM(p.amount), 0) as paid_amount
    FROM invoices i 
    LEFT JOIN clients c ON i.client_id = c.id 
    LEFT JOIN payments p ON i.id = p.invoice_id
    WHERE i.id = ?
    GROUP BY i.id
');
$stmt->execute([$id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

$items = [];
if ($invoice) {
    $item_stmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
    $item_stmt->execute([$id]);
    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!$invoice) {
    render_header('Invoice Not Found - Invoice Generator', 'invoices');
    render_page_header('Invoice Not Found', 'The requested invoice could not be found');
    ?>
    <div class="text-center py-5">
        <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
        <h4>Invoice Not Found</h4>
        <p class="text-muted">The invoice you're looking for doesn't exist or has been deleted.</p>
        <a href="index.php?page=invoices" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Invoices
        </a>
    </div>
    <?php
    render_footer();
    return;
}

$balance = $invoice['total'] - $invoice['paid_amount'];
$due_date = date('M j, Y', strtotime($invoice['created_at'] . ' +30 days'));
$is_overdue = strtotime($invoice['created_at'] . ' +30 days') < time() && $invoice['status'] !== 'Paid';

render_header('Invoice #' . str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) . ' - Invoice Generator', 'invoices');
render_page_header(
    'Invoice #' . str_pad($invoice['id'], 4, '0', STR_PAD_LEFT), 
    'Invoice details and line items',
    [
        [
            'text' => 'Download PDF',
            'url' => 'index.php?page=download_invoice&id=' . $invoice['id'],
            'class' => 'btn-primary',
            'icon' => 'fas fa-download'
        ],
        [
            'text' => 'Send Email',
            'url' => 'index.php?page=send_invoice&id=' . $invoice['id'],
            'class' => 'btn-success',
            'icon' => 'fas fa-envelope'
        ],
        [
            'text' => 'Record Payment',
            'url' => 'index.php?page=add_payment&invoice_id=' . $invoice['id'],
            'class' => 'btn-warning',
            'icon' => 'fas fa-dollar-sign',
            'show' => $balance > 0
        ]
    ]
);
?>

<div class="row">
    <!-- Invoice Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-file-invoice"></i> Invoice Details</h5>
                <?php render_status_badge($invoice['status']); ?>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Bill To:</h6>
                        <strong><?= htmlspecialchars($invoice['client_name']) ?></strong><br>
                        <?php if ($invoice['client_email']): ?>
                            <small class="text-muted">
                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($invoice['client_email']) ?>
                            </small><br>
                        <?php endif; ?>
                        <?php if ($invoice['client_address']): ?>
                            <small class="text-muted"><?= nl2br(htmlspecialchars($invoice['client_address'])) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted">Invoice Information:</h6>
                        <strong>Date:</strong> <?= date('M j, Y', strtotime($invoice['created_at'])) ?><br>
                        <strong>Due Date:</strong> 
                        <span class="<?= $is_overdue ? 'text-danger' : '' ?>">
                            <?= $due_date ?>
                        </span><br>
                        <?php if ($is_overdue && $balance > 0): ?>
                            <small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> This invoice is overdue
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Invoice Items Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td class="text-center"><?= number_format($item['quantity'], 2) ?></td>
                                <td class="text-end">$<?= number_format($item['rate'], 2) ?></td>
                                <td class="text-end">$<?= number_format($item['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Summary -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calculator"></i> Invoice Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong>$<?= number_format($invoice['total'], 2) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Paid Amount:</span>
                    <span class="text-success">$<?= number_format($invoice['paid_amount'], 2) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span><strong>Balance Due:</strong></span>
                    <strong class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                        $<?= number_format($balance, 2) ?>
                    </strong>
                </div>
                
                <?php if ($balance > 0): ?>
                <div class="mt-3">
                    <a href="index.php?page=add_payment&invoice_id=<?= $invoice['id'] ?>" 
                       class="btn btn-success btn-sm w-100">
                        <i class="fas fa-dollar-sign"></i> Record Payment
                    </a>
                </div>
                <?php else: ?>
                <div class="mt-3 text-center">
                    <span class="badge bg-success">
                        <i class="fas fa-check"></i> Fully Paid
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="fas fa-tools"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?page=download_invoice&id=<?= $invoice['id'] ?>" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                    <a href="index.php?page=send_invoice&id=<?= $invoice['id'] ?>" 
                       class="btn btn-outline-success btn-sm">
                        <i class="fas fa-envelope"></i> Send via Email
                    </a>
                    <hr>
                    <a href="index.php?page=invoices" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
