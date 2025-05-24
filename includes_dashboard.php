<?php
// Dashboard page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_login();

// Get dashboard statistics
$stats = [];

// Total clients
$stmt = $pdo->query('SELECT COUNT(*) FROM clients');
$stats['total_clients'] = $stmt->fetchColumn();

// Total invoices
$stmt = $pdo->query('SELECT COUNT(*) FROM invoices');
$stats['total_invoices'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->query('SELECT COALESCE(SUM(total), 0) FROM invoices WHERE status = "Paid"');
$stats['total_revenue'] = $stmt->fetchColumn();

// Pending invoices
$stmt = $pdo->query('SELECT COUNT(*) FROM invoices WHERE status IN ("Draft", "Sent")');
$stats['pending_invoices'] = $stmt->fetchColumn();

// Overdue invoices
$stmt = $pdo->query('SELECT COUNT(*) FROM invoices WHERE status = "Overdue"');
$stats['overdue_invoices'] = $stmt->fetchColumn();

// Recent invoices
$stmt = $pdo->query('
    SELECT i.*, c.name as client_name 
    FROM invoices i 
    LEFT JOIN clients c ON i.client_id = c.id 
    ORDER BY i.created_at DESC 
    LIMIT 5
');
$recent_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

render_header('Dashboard - Invoice Generator', 'dashboard');
render_page_header(
    'Dashboard', 
    'Welcome back! Here\'s an overview of your business.',
    [
        [
            'text' => 'Create Invoice',
            'url' => 'index.php?page=create_invoice',
            'class' => 'btn-primary',
            'icon' => 'fas fa-plus'
        ],
        [
            'text' => 'Create Client',
            'url' => 'index.php?page=create_client',
            'class' => 'btn-secondary',
            'icon' => 'fas fa-user-plus'
        ]
    ]
);

// Determine the grid class based on number of cards
$has_overdue = $stats['overdue_invoices'] > 0;
$grid_class = $has_overdue ? 'dashboard-grid five-cards' : 'dashboard-grid stats-layout';
?>

<!-- Statistics Cards -->
<div class="<?= $grid_class ?>">
    <div class="dashboard-card" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);">
        <div class="dashboard-card-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="dashboard-card-title"><?= number_format($stats['total_clients']) ?></div>
        <div class="dashboard-card-subtitle">Total Clients</div>
    </div>
    
    <div class="dashboard-card" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #0891b2 100%);">
        <div class="dashboard-card-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="dashboard-card-title"><?= number_format($stats['total_invoices']) ?></div>
        <div class="dashboard-card-subtitle">Total Invoices</div>
    </div>
    
    <div class="dashboard-card" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);">
        <div class="dashboard-card-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="dashboard-card-title">$<?= number_format($stats['total_revenue'], 2) ?></div>
        <div class="dashboard-card-subtitle">Total Revenue</div>
    </div>
    
    <div class="dashboard-card" style="background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);">
        <div class="dashboard-card-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="dashboard-card-title"><?= number_format($stats['pending_invoices']) ?></div>
        <div class="dashboard-card-subtitle">Pending Invoices</div>
    </div>
    
    <?php if ($stats['overdue_invoices'] > 0): ?>
    <div class="dashboard-card" style="background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);">
        <div class="dashboard-card-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="dashboard-card-title"><?= number_format($stats['overdue_invoices']) ?></div>
        <div class="dashboard-card-subtitle">Overdue Invoices</div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i> Quick Actions
        </h3>
    </div>
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <a href="index.php?page=create_invoice" class="dashboard-card" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); text-decoration: none; color: white;">
            <div class="dashboard-card-icon">
                <i class="fas fa-file-plus"></i>
            </div>
            <div class="dashboard-card-title">Create Invoice</div>
            <div class="dashboard-card-subtitle">Generate new invoice</div>
        </a>
        
        <a href="index.php?page=create_client" class="dashboard-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); text-decoration: none; color: white;">
            <div class="dashboard-card-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="dashboard-card-title">Add Client</div>
            <div class="dashboard-card-subtitle">New client registration</div>
        </a>
        
        <a href="index.php?page=payments" class="dashboard-card" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); text-decoration: none; color: white;">
            <div class="dashboard-card-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="dashboard-card-title">Record Payment</div>
            <div class="dashboard-card-subtitle">Track new payment</div>
        </a>
        
        <a href="index.php?page=reports" class="dashboard-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); text-decoration: none; color: white;">
            <div class="dashboard-card-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="dashboard-card-title">View Reports</div>
            <div class="dashboard-card-subtitle">Business analytics</div>
        </a>
    </div>
</div>

<!-- Recent Invoices -->
<?php if (!empty($recent_invoices)): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-history"></i> Recent Invoices
        </h3>
        <a href="index.php?page=invoices" class="btn btn-outline btn-sm">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_invoices as $invoice): ?>
                <tr>
                    <td><strong>#<?= $invoice['id'] ?></strong></td>
                    <td><?= htmlspecialchars($invoice['client_name'] ?? 'Unknown Client') ?></td>
                    <td><?= date('M j, Y', strtotime($invoice['created_at'])) ?></td>
                    <td><?php render_status_badge($invoice['status']); ?></td>
                    <td><strong>$<?= number_format($invoice['total'], 2) ?></strong></td>
                    <td class="action-links">
                        <a href="index.php?page=view_invoice&id=<?= $invoice['id'] ?>" class="action-link">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Chart Section -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-line"></i> Revenue Overview
        </h3>
    </div>
    <div style="position: relative; height: 300px;">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<script>
// Revenue Chart
$(document).ready(function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Get monthly revenue data (you can enhance this with real data from PHP)
    const monthlyRevenue = [
        <?php
        // Generate sample data for the last 6 months
        $months = [];
        $revenues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('M Y', strtotime("-$i months"));
            $months[] = "'$month'";
            
            // Get actual revenue for this month
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $stmt = $pdo->prepare('SELECT COALESCE(SUM(total), 0) FROM invoices WHERE status = "Paid" AND created_at BETWEEN ? AND ?');
            $stmt->execute([$monthStart, $monthEnd]);
            $revenue = $stmt->fetchColumn();
            $revenues[] = $revenue;
        }
        echo implode(',', $revenues);
        ?>
    ];
    
    const months = [<?= implode(',', $months) ?>];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Revenue',
                data: monthlyRevenue,
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
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
});
</script>

<?php render_footer(); ?>