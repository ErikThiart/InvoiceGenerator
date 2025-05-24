<?php
// Clients page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

// Get clients with additional statistics
$stmt = $pdo->query('
    SELECT c.*, 
           COUNT(DISTINCT i.id) as invoice_count,
           COALESCE(SUM(i.total), 0) as total_billed,
           COALESCE(SUM(p.amount), 0) as total_paid
    FROM clients c 
    LEFT JOIN invoices i ON c.id = i.client_id 
    LEFT JOIN payments p ON i.id = p.invoice_id
    GROUP BY c.id 
    ORDER BY c.name ASC
');
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get client statistics for cards
$total_clients = count($clients);
$active_clients = count(array_filter($clients, function($c) { return $c['invoice_count'] > 0; }));
$total_revenue = array_sum(array_column($clients, 'total_billed'));

render_header('Clients - Invoice Generator', 'clients');
?>

<?php render_page_header('Clients', 'Manage your client database', [
    '<a href="index.php?page=create_client" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Client
    </a>'
]); ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon clients">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($total_clients) ?></div>
            <div class="stat-label">Total Clients</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($active_clients) ?></div>
            <div class="stat-label">Active Clients</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?= number_format($total_revenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $total_clients > 0 ? number_format(($active_clients / $total_clients) * 100, 1) : 0 ?>%</div>
            <div class="stat-label">Client Activity</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> Client Directory</h3>
        <div class="card-actions">
            <button class="btn btn-outline" onclick="exportTable('clientsTable')">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    <div class="card-content">
        <table id="clientsTable" class="display datatable-modern">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Company</th>
                    <th>Business Stats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= $client['id'] ?></td>
                    <td>
                        <div class="client-info">
                            <div class="client-name"><?= htmlspecialchars($client['name']) ?></div>
                            <div class="client-meta">
                                <?php if ($client['invoice_count'] > 0): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">New</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="contact-info">
                            <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($client['email']) ?></div>
                            <?php if ($client['phone']): ?>
                                <div><i class="fas fa-phone"></i> <?= htmlspecialchars($client['phone']) ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($client['company'] ?: 'Individual') ?></td>
                    <td>
                        <div class="stats-mini">
                            <div><strong><?= $client['invoice_count'] ?></strong> invoices</div>
                            <div class="text-success">$<?= number_format($client['total_billed'], 2) ?> billed</div>
                            <div class="text-primary">$<?= number_format($client['total_paid'], 2) ?> paid</div>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="index.php?page=view_client&id=<?= $client['id'] ?>" 
                               class="btn btn-sm btn-outline" 
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="index.php?page=create_invoice&client_id=<?= $client['id'] ?>" 
                               class="btn btn-sm btn-primary" 
                               data-tooltip="Create Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>
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
    $('#clientsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']], // Sort by client name
        columnDefs: [
            { targets: [5], orderable: false } // Disable sorting on Actions column
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
</script>

<?php render_footer(); ?>
