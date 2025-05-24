<?php
// Invoices page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

// Calculate invoice statistics
$totalInvoices = $pdo->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
$paidInvoices = $pdo->query('SELECT COUNT(*) FROM invoices WHERE status = "Paid"')->fetchColumn();
$pendingInvoices = $pdo->query('SELECT COUNT(*) FROM invoices WHERE status IN ("Draft", "Sent")')->fetchColumn();
$overdueInvoices = $pdo->query('SELECT COUNT(*) FROM invoices WHERE status = "Overdue"')->fetchColumn();

$stmt = $pdo->query('
    SELECT i.*, c.name as client_name, c.email as client_email,
           COALESCE(SUM(p.amount), 0) as paid_amount
    FROM invoices i 
    LEFT JOIN clients c ON i.client_id = c.id 
    LEFT JOIN payments p ON i.id = p.invoice_id
    GROUP BY i.id
    ORDER BY i.created_at DESC
');
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

render_header('Invoices - Invoice Generator', 'invoices');
render_page_header(
    'Invoices', 
    'Manage and track all your invoices',
    [
        [
            'text' => 'Create Invoice',
            'url' => 'index.php?page=create_invoice',
            'class' => 'btn-primary',
            'icon' => 'fas fa-plus'
        ]
    ]
);
?>

<!-- Invoices Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="stat-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; text-align: center;">
        <div style="color: #3b82f6; font-size: 2rem; margin-bottom: 0.5rem;">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div style="font-size: 2rem; font-weight: bold; color: #1e293b; margin-bottom: 0.25rem;">
            <?= number_format($totalInvoices) ?>
        </div>
        <div style="color: #64748b; font-size: 0.875rem;">Total Invoices</div>
    </div>
    
    <div class="stat-card" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1.5rem; text-align: center;">
        <div style="color: #22c55e; font-size: 2rem; margin-bottom: 0.5rem;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div style="font-size: 2rem; font-weight: bold; color: #1e293b; margin-bottom: 0.25rem;">
            <?= number_format($paidInvoices) ?>
        </div>
        <div style="color: #64748b; font-size: 0.875rem;">Paid</div>
    </div>
    
    <div class="stat-card" style="background: #fffbeb; border: 1px solid #fed7aa; border-radius: 8px; padding: 1.5rem; text-align: center;">
        <div style="color: #f59e0b; font-size: 2rem; margin-bottom: 0.5rem;">
            <i class="fas fa-clock"></i>
        </div>
        <div style="font-size: 2rem; font-weight: bold; color: #1e293b; margin-bottom: 0.25rem;">
            <?= number_format($pendingInvoices) ?>
        </div>
        <div style="color: #64748b; font-size: 0.875rem;">Pending</div>
    </div>
    
    <div class="stat-card" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1.5rem; text-align: center;">
        <div style="color: #ef4444; font-size: 2rem; margin-bottom: 0.5rem;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div style="font-size: 2rem; font-weight: bold; color: #1e293b; margin-bottom: 0.25rem;">
            <?= number_format($overdueInvoices) ?>
        </div>
        <div style="color: #64748b; font-size: 0.875rem;">Overdue</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($invoices)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <h4>No Invoices Yet</h4>
                <p class="text-muted">Create your first invoice to get started.</p>
                <a href="index.php?page=create_invoice" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Invoice
                </a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table id="invoicesTable" class="table data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>
                                <strong>#<?= str_pad($inv['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($inv['client_name'] ?? 'Unknown Client') ?></strong>
                                    <?php if ($inv['client_email']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($inv['client_email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                            <td>
                                <?php 
                                $due_date = date('M j, Y', strtotime($inv['created_at'] . ' +30 days'));
                                $is_overdue = strtotime($inv['created_at'] . ' +30 days') < time() && $inv['status'] !== 'Paid';
                                echo $is_overdue ? '<span class="text-danger">' . $due_date . '</span>' : $due_date;
                                ?>
                            </td>
                            <td><?php render_status_badge($inv['status']); ?></td>
                            <td><strong>$<?= number_format($inv['total'], 2) ?></strong></td>
                            <td>$<?= number_format($inv['paid_amount'], 2) ?></td>
                            <td>
                                <?php 
                                $balance = $inv['total'] - $inv['paid_amount'];
                                $text_class = $balance > 0 ? 'text-danger' : 'text-success';
                                ?>
                                <span class="<?= $text_class ?>">
                                    <strong>$<?= number_format($balance, 2) ?></strong>
                                </span>
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="index.php?page=view_invoice&id=<?= $inv['id'] ?>" 
                                       class="action-link" 
                                       data-bs-toggle="tooltip" 
                                       title="View Invoice">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?page=download_invoice&id=<?= $inv['id'] ?>" 
                                       class="action-link" 
                                       data-bs-toggle="tooltip" 
                                       title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="index.php?page=send_invoice&id=<?= $inv['id'] ?>" 
                                       class="action-link" 
                                       data-bs-toggle="tooltip" 
                                       title="Send Email">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <?php if ($inv['status'] !== 'Paid'): ?>
                                    <a href="index.php?page=add_payment&invoice_id=<?= $inv['id'] ?>" 
                                       class="action-link text-success" 
                                       data-bs-toggle="tooltip" 
                                       title="Record Payment">
                                        <i class="fas fa-dollar-sign"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enhanced DataTable configuration for invoices
    if ($.fn.DataTable && $('#invoicesTable').length) {
        $('#invoicesTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: [4, 8], orderable: false }, // Status and Actions columns
                { targets: [5, 6, 7], className: 'text-right' } // Amount columns
            ],
            // Use simpler DOM structure that works reliably
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    className: 'btn btn-success btn-sm',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    title: 'Invoices Report - ' + new Date().toLocaleDateString(),
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7] // Exclude actions column
                    }
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger btn-sm',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    title: 'Invoices Report',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7] // Exclude actions column
                    }
                },
                {
                    extend: 'print',
                    className: 'btn btn-secondary btn-sm',
                    text: '<i class="fas fa-print"></i> Print',
                    title: 'Invoices Report',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7] // Exclude actions column
                    }
                }
            ],
            language: {
                search: 'Search invoices:',
                lengthMenu: 'Show _MENU_ entries per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ invoices',
                infoEmpty: 'No invoices found',
                infoFiltered: '(filtered from _MAX_ total invoices)',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            drawCallback: function() {
                // Initialize tooltips after table draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            initComplete: function() {
                // Style the controls after DataTable initializes
                $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search invoices...');
                $('.dataTables_length select').addClass('form-control');
                
                // Wrap controls in a flex container
                $('.dataTables_wrapper').prepend('<div class="datatables-controls-wrapper"></div>');
                $('.datatables-controls-wrapper').append($('.dataTables_length'));
                $('.datatables-controls-wrapper').append($('.dt-buttons'));
                $('.datatables-controls-wrapper').append($('.dataTables_filter'));
            }
        });
    }
});
</script>

<?php render_footer(); ?>