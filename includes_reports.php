<?php
// Reports page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

// Enhanced reporting queries
$totalInvoices = $pdo->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
$totalRevenue = $pdo->query('SELECT COALESCE(SUM(total), 0) FROM invoices')->fetchColumn();
$totalPayments = $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM payments')->fetchColumn();
$pendingAmount = $totalRevenue - $totalPayments;

// Monthly revenue data for chart
$monthlyData = $pdo->query('
    SELECT 
        strftime("%Y-%m", payment_date) as month,
        SUM(amount) as total
    FROM payments 
    WHERE payment_date >= datetime("now", "-12 months")
    GROUP BY strftime("%Y-%m", payment_date)
    ORDER BY month
')->fetchAll(PDO::FETCH_ASSOC);

// Status breakdown
$statusStats = $pdo->query('
    SELECT status, COUNT(*) as count, SUM(total) as amount
    FROM invoices 
    GROUP BY status
')->fetchAll(PDO::FETCH_ASSOC);

// Top clients by revenue
$topClients = $pdo->query('
    SELECT c.name, c.company, SUM(i.total) as total_billed
    FROM clients c
    JOIN invoices i ON c.id = i.client_id
    GROUP BY c.id
    ORDER BY total_billed DESC
    LIMIT 10
')->fetchAll(PDO::FETCH_ASSOC);

render_header('Reports & Analytics - Invoice Generator', 'reports');
?>

<?php render_page_header('Reports & Analytics', 'Business insights and performance metrics'); ?>

<!-- Key Performance Indicators -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalInvoices) ?></div>
            <div class="stat-label">Total Invoices</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-credit-card"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?= number_format($totalPayments, 2) ?></div>
            <div class="stat-label">Total Collected</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon <?= $pendingAmount > 0 ? 'warning' : 'success' ?>">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?= number_format($pendingAmount, 2) ?></div>
            <div class="stat-label">Outstanding</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Revenue Chart -->
    <div class="card chart-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Revenue Trend (Last 12 Months)</h3>
        </div>
        <div class="card-content">
            <canvas id="revenueChart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Invoice Status Breakdown -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Invoice Status</h3>
        </div>
        <div class="card-content">
            <div class="status-breakdown">
                <?php foreach ($statusStats as $status): ?>
                    <div class="status-item">
                        <div class="status-info">
                            <?= render_status_badge($status['status']) ?>
                            <span class="status-details">
                                <?= $status['count'] ?> invoices
                                <span class="text-muted">($<?= number_format($status['amount'], 2) ?>)</span>
                            </span>
                        </div>
                        <div class="status-bar">
                            <div class="status-progress" 
                                 style="width: <?= $totalInvoices > 0 ? ($status['count'] / $totalInvoices) * 100 : 0 ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Clients Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-trophy"></i> Top Clients by Revenue</h3>
    </div>
    <div class="card-content">
        <table id="topClientsTable" class="display datatable-modern">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Client</th>
                    <th>Company</th>
                    <th>Total Revenue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($topClients as $client): ?>
                <tr>
                    <td>
                        <div class="rank-badge">
                            <?php if ($rank <= 3): ?>
                                <span class="medal medal-<?= $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : 'bronze') ?>">
                                    <?= $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : '🥉') ?>
                                </span>
                            <?php else: ?>
                                <span class="rank-number"><?= $rank ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="client-name"><?= htmlspecialchars($client['name']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($client['company'] ?: '—') ?></td>
                    <td>
                        <span class="amount-display success">
                            $<?= number_format($client['total_billed'], 2) ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?page=clients&search=<?= urlencode($client['name']) ?>" 
                           class="btn btn-sm btn-outline">
                            <i class="fas fa-external-link-alt"></i> View Client
                        </a>
                    </td>
                </tr>
                <?php $rank++; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detailed Reports Section -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-table"></i> All Invoices Overview</h3>
        <div class="card-actions">
            <button class="btn btn-outline" onclick="exportTable('allInvoicesTable')">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    <div class="card-content">
        <table id="allInvoicesTable" class="display datatable-modern">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $allInvoices = $pdo->query('
                    SELECT i.*, c.name as client_name 
                    FROM invoices i 
                    LEFT JOIN clients c ON i.client_id = c.id 
                    ORDER BY i.created_at DESC
                ')->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allInvoices as $invoice): 
                ?>
                <tr>
                    <td>
                        <a href="index.php?page=view_invoice&id=<?= $invoice['id'] ?>" class="link-primary">
                            #<?= htmlspecialchars($invoice['invoice_number'] ?? $invoice['id']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($invoice['client_name'] ?? 'Unknown') ?></td>
                    <td><?= date('M j, Y', strtotime($invoice['created_at'])) ?></td>
                    <td>
                        <?php if ($invoice['due_date']): ?>
                            <?= date('M j, Y', strtotime($invoice['due_date'])) ?>
                            <?php if (strtotime($invoice['due_date']) < time() && $invoice['status'] !== 'paid'): ?>
                                <span class="badge badge-danger">Overdue</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= render_status_badge($invoice['status']) ?></td>
                    <td>
                        <span class="amount-display">
                            $<?= number_format($invoice['total'], 2) ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?page=view_invoice&id=<?= $invoice['id'] ?>" 
                           class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const monthlyLabels = <?= json_encode(array_column($monthlyData, 'month')) ?>;
    const monthlyAmounts = <?= json_encode(array_column($monthlyData, 'total')) ?>;
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels.map(month => {
                const date = new Date(month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Revenue',
                data: monthlyAmounts,
                borderColor: '#4f8cff',
                backgroundColor: 'rgba(79, 140, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Initialize DataTables
    $('#topClientsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[3, 'desc']], // Sort by revenue
        columnDefs: [
            { targets: [4], orderable: false }
        ]
    });
    
    $('#allInvoicesTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'desc']], // Sort by date
        columnDefs: [
            { targets: [6], orderable: false }
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
            }
        ]
    });
});
</script>

<?php render_footer(); ?>
