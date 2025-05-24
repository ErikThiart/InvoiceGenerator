<?php
// Payments page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

// Get payments with detailed information
$stmt = $pdo->query('
    SELECT p.*, 
           i.invoice_number,
           i.total as invoice_total,
           c.name as client_name,
           c.company as client_company
    FROM payments p 
    LEFT JOIN invoices i ON p.invoice_id = i.id 
    LEFT JOIN clients c ON i.client_id = c.id
    ORDER BY p.payment_date DESC
');
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment statistics
$total_payments = count($payments);
$total_amount = array_sum(array_column($payments, 'amount'));
$this_month_payments = array_filter($payments, function($p) {
    return date('Y-m', strtotime($p['payment_date'])) === date('Y-m');
});
$this_month_total = array_sum(array_column($this_month_payments, 'amount'));

render_header('Payments - Invoice Generator', 'payments');
?>

<?php render_page_header('Payments', 'Track and manage payment records', [
    '<a href="index.php?page=add_payment" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Payment
    </a>'
]); ?>

<!-- Payment Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-credit-card"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($total_payments) ?></div>
            <div class="stat-label">Total Payments</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?= number_format($total_amount, 2) ?></div>
            <div class="stat-label">Total Amount</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= count($this_month_payments) ?></div>
            <div class="stat-label">This Month</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?= number_format($this_month_total, 2) ?></div>
            <div class="stat-label">Monthly Revenue</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-credit-card"></i> Payment History</h3>
        <div class="card-actions">
            <button class="btn btn-outline" onclick="exportTable('paymentsTable')">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    <div class="card-content">
        <table id="paymentsTable" class="display datatable-modern">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Invoice</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= $payment['id'] ?></td>
                    <td>
                        <div class="invoice-ref">
                            <?php if ($payment['invoice_number']): ?>
                                <a href="index.php?page=view_invoice&id=<?= $payment['invoice_id'] ?>" class="link-primary">
                                    #<?= htmlspecialchars($payment['invoice_number']) ?>
                                </a>
                                <div class="text-small text-muted">
                                    Total: $<?= number_format($payment['invoice_total'], 2) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Invoice #<?= $payment['invoice_id'] ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="client-info">
                            <?php if ($payment['client_name']): ?>
                                <div class="client-name"><?= htmlspecialchars($payment['client_name']) ?></div>
                                <?php if ($payment['client_company']): ?>
                                    <div class="text-small text-muted"><?= htmlspecialchars($payment['client_company']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Unknown Client</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="amount-display success">
                            $<?= number_format($payment['amount'], 2) ?>
                        </span>
                    </td>
                    <td>
                        <div class="date-display">
                            <?= date('M j, Y', strtotime($payment['payment_date'])) ?>
                            <div class="text-small text-muted">
                                <?= date('g:i A', strtotime($payment['payment_date'])) ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $method_icons = [
                            'credit_card' => 'fas fa-credit-card',
                            'bank_transfer' => 'fas fa-university',
                            'cash' => 'fas fa-money-bill',
                            'check' => 'fas fa-money-check',
                            'paypal' => 'fab fa-paypal',
                            'other' => 'fas fa-question-circle'
                        ];
                        $method = strtolower(str_replace(' ', '_', $payment['method'] ?? 'other'));
                        $icon = $method_icons[$method] ?? $method_icons['other'];
                        ?>
                        <span class="payment-method">
                            <i class="<?= $icon ?>"></i>
                            <?= htmlspecialchars($payment['method'] ?? 'Unknown') ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($payment['reference']): ?>
                            <code class="reference-code"><?= htmlspecialchars($payment['reference']) ?></code>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($payment['invoice_id']): ?>
                                <a href="index.php?page=view_invoice&id=<?= $payment['invoice_id'] ?>" 
                                   class="btn btn-sm btn-outline" 
                                   data-tooltip="View Invoice">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="confirmDeletePayment(<?= $payment['id'] ?>)"
                                    data-tooltip="Delete Payment">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#paymentsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[4, 'desc']], // Sort by payment date, newest first
        columnDefs: [
            { targets: [7], orderable: false }, // Disable sorting on Actions column
            { targets: [3], type: 'num-fmt' } // Enable proper numeric sorting for amount
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-secondary btn-sm'
            }
        ]
    });
    
    // Initialize tooltips
    initTooltips();
});

function confirmDeletePayment(paymentId) {
    if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
        window.location.href = 'index.php?page=delete_payment&id=' + paymentId;
    }
}
</script>

<?php render_footer(); ?>
