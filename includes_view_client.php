<?php
// View Client page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('
    SELECT c.*, 
           COUNT(i.id) as total_invoices,
           COALESCE(SUM(i.total), 0) as total_billed,
           COALESCE(SUM(p.amount), 0) as total_paid
    FROM clients c 
    LEFT JOIN invoices i ON c.id = i.client_id
    LEFT JOIN payments p ON i.id = p.invoice_id
    WHERE c.id = ?
    GROUP BY c.id
');
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Get client's recent invoices
$invoices = [];
if ($client) {
    $inv_stmt = $pdo->prepare('
        SELECT i.*, COALESCE(SUM(p.amount), 0) as paid_amount
        FROM invoices i 
        LEFT JOIN payments p ON i.id = p.invoice_id
        WHERE i.client_id = ?
        GROUP BY i.id
        ORDER BY i.created_at DESC
        LIMIT 10
    ');
    $inv_stmt->execute([$id]);
    $invoices = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!$client) {
    render_header('Client Not Found - Invoice Generator', 'clients');
    render_page_header('Client Not Found', 'The requested client could not be found');
    ?>
    <div class="text-center py-5">
        <i class="fas fa-user fa-4x text-muted mb-3"></i>
        <h4>Client Not Found</h4>
        <p class="text-muted">The client you're looking for doesn't exist or has been deleted.</p>
        <a href="index.php?page=clients" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Clients
        </a>
    </div>
    <?php
    render_footer();
    return;
}

render_header(htmlspecialchars($client['name']) . ' - Invoice Generator', 'clients');
render_page_header(
    htmlspecialchars($client['name']), 
    'Client details and invoice history',
    [
        [
            'text' => 'Create Invoice',
            'url' => 'index.php?page=create_invoice&client_id=' . $client['id'],
            'class' => 'btn-primary',
            'icon' => 'fas fa-plus'
        ],
        [
            'text' => 'Edit Client',
            'url' => 'index.php?page=edit_client&id=' . $client['id'],
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-edit'
        ]
    ]
);
?>

<div class="row">
    <!-- Client Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Client Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Name</label>
                    <div class="fw-bold"><?= htmlspecialchars($client['name']) ?></div>
                </div>
                
                <?php if ($client['email']): ?>
                <div class="mb-3">
                    <label class="form-label text-muted">Email</label>
                    <div>
                        <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="text-decoration-none">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($client['email']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($client['phone']): ?>
                <div class="mb-3">
                    <label class="form-label text-muted">Phone</label>
                    <div>
                        <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="text-decoration-none">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($client['phone']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($client['company']): ?>
                <div class="mb-3">
                    <label class="form-label text-muted">Company</label>
                    <div class="fw-bold"><?= htmlspecialchars($client['company']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($client['address']): ?>
                <div class="mb-3">
                    <label class="form-label text-muted">Address</label>
                    <div><?= nl2br(htmlspecialchars($client['address'])) ?></div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Client Since</label>
                    <div><?= date('M j, Y', strtotime($client['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Client Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1"><?= $client['total_invoices'] ?></h4>
                            <small class="text-muted">Invoices</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">$<?= number_format($client['total_billed'], 2) ?></h4>
                        <small class="text-muted">Total Billed</small>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Total Paid:</span>
                    <strong class="text-success">$<?= number_format($client['total_paid'], 2) ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Outstanding:</span>
                    <strong class="text-warning">$<?= number_format($client['total_billed'] - $client['total_paid'], 2) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-file-invoice"></i> Recent Invoices</h5>
                <a href="index.php?page=create_invoice&client_id=<?= $client['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Invoice
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($invoices)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <h5>No Invoices Yet</h5>
                        <p class="text-muted">This client doesn't have any invoices yet.</p>
                        <a href="index.php?page=create_invoice&client_id=<?= $client['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Invoice
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($invoice['created_at'])) ?></td>
                                    <td><?php render_status_badge($invoice['status']); ?></td>
                                    <td class="text-end">$<?= number_format($invoice['total'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($invoice['paid_amount'], 2) ?></td>
                                    <td class="text-end">
                                        <?php 
                                        $balance = $invoice['total'] - $invoice['paid_amount'];
                                        $text_class = $balance > 0 ? 'text-danger' : 'text-success';
                                        ?>
                                        <span class="<?= $text_class ?>">
                                            $<?= number_format($balance, 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-links">
                                            <a href="index.php?page=view_invoice&id=<?= $invoice['id'] ?>" 
                                               class="action-link" 
                                               data-bs-toggle="tooltip" 
                                               title="View Invoice">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=download_invoice&id=<?= $invoice['id'] ?>" 
                                               class="action-link" 
                                               data-bs-toggle="tooltip" 
                                               title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($invoices) >= 10): ?>
                    <div class="text-center mt-3">
                        <a href="index.php?page=invoices&client_id=<?= $client['id'] ?>" class="btn btn-outline-primary">
                            View All Invoices
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4">
    <a href="index.php?page=clients" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Clients
    </a>
</div>

<?php render_footer(); ?>
