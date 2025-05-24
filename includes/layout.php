<?php
/**
 * Common Layout Components for Invoice Generator
 * Provides consistent header, navigation, and footer across all pages
 */

function render_header($title = 'Invoice Generator', $page = '') {
    $current_page = $_GET['page'] ?? 'home';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        
        <!-- Chart.js for Reports -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- Custom CSS -->
        <link rel="stylesheet" href="assets_css/style.css">
        
        <!-- Meta tags -->
        <meta name="description" content="Professional Invoice Generator - Create, manage, and track invoices easily">
        <meta name="keywords" content="invoice, generator, billing, payments, business">
        <meta name="author" content="Invoice Generator">
        
        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    </head>
    <body class="app-container">
        <header>
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-chart-line"></i>
                    Invoice Generator
                </a>
                
                <nav class="main-nav">
                    <a href="index.php" class="nav-link <?= $current_page == 'home' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
                        <a href="index.php?page=dashboard" class="nav-link <?= $current_page == 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="index.php?page=clients" class="nav-link <?= $current_page == 'clients' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> Clients
                        </a>
                        <a href="index.php?page=invoices" class="nav-link <?= $current_page == 'invoices' ? 'active' : '' ?>">
                            <i class="fas fa-file-invoice"></i> Invoices
                        </a>
                        <a href="index.php?page=payments" class="nav-link <?= $current_page == 'payments' ? 'active' : '' ?>">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
                        <a href="index.php?page=reports" class="nav-link <?= $current_page == 'reports' ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <a href="index.php?page=logout" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="nav-link <?= $current_page == 'login' ? 'active' : '' ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="index.php?page=register" class="nav-link <?= $current_page == 'register' ? 'active' : '' ?>">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>
        
        <main class="main-content">
    <?php
}

function render_footer() {
    ?>
        </main>
        
        <footer>
            <div style="max-width: 1200px; margin: 0 auto;">
                <p>&copy; <?= date('Y') ?> Invoice Generator. Built with PHP & Modern Web Technologies.</p>
                <div style="margin-top: 1rem; display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <span style="color: var(--text-secondary); font-size: 0.75rem;">
                        <i class="fas fa-shield-alt"></i> Secure
                    </span>
                    <span style="color: var(--text-secondary); font-size: 0.75rem;">
                        <i class="fas fa-mobile-alt"></i> Responsive
                    </span>
                    <span style="color: var(--text-secondary); font-size: 0.75rem;">
                        <i class="fas fa-rocket"></i> Fast
                    </span>
                </div>
            </div>
        </footer>
        
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
        
        <!-- Bootstrap Bundle (for modals, tooltips, etc.) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Custom JavaScript -->
        <script>
            // Initialize DataTables with consistent settings
            $(document).ready(function() {
                if ($.fn.DataTable) {
                    $('.data-table').DataTable({
                        responsive: true,
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        dom: 'Bfrtip',
                        buttons: [
                            {
                                extend: 'excel',
                                className: 'btn btn-success btn-sm',
                                text: '<i class="fas fa-file-excel"></i> Excel'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-danger btn-sm',
                                text: '<i class="fas fa-file-pdf"></i> PDF'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-info btn-sm',
                                text: '<i class="fas fa-print"></i> Print'
                            }
                        ],
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries found",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        },
                        order: [[0, 'desc']]
                    });
                }
                
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Form validation
                $('form').on('submit', function(e) {
                    var form = this;
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
                
                // Auto-hide alerts after 5 seconds
                $('.alert').delay(5000).fadeOut('slow');
                
                // Confirm deletion actions
                $('a[href*="delete"], button[data-action="delete"]').on('click', function(e) {
                    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
                
                // Loading states for forms
                $('form').on('submit', function() {
                    $(this).find('button[type="submit"]').addClass('loading').prop('disabled', true);
                });
            });
            
            // Utility functions
            function showAlert(message, type = 'info') {
                var alertClass = 'alert-' + type;
                var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                
                $('.main-content').prepend(alertHtml);
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $('.alert').fadeOut('slow');
                }, 5000);
            }
            
            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(amount);
            }
            
            function formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        </script>
    </body>
    </html>
    <?php
}

function render_page_header($title, $subtitle = '', $actions = []) {
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="card-title"><?= htmlspecialchars($title) ?></h1>
                <?php if ($subtitle): ?>
                    <p class="mb-0" style="color: var(--text-secondary); font-size: 0.875rem;">
                        <?= htmlspecialchars($subtitle) ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)): ?>
                <div class="d-flex gap-2">
                    <?php foreach ($actions as $action): ?>
                        <?php if (is_array($action)): ?>
                            <?php if (!isset($action['show']) || $action['show']): ?>
                                <a href="<?= htmlspecialchars($action['url']) ?>" 
                                   class="btn <?= $action['class'] ?? 'btn-primary' ?>">
                                    <?php if (isset($action['icon'])): ?>
                                        <i class="<?= htmlspecialchars($action['icon']) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($action['text']) ?>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= $action ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function render_status_badge($status) {
    $status_lower = strtolower($status);
    $class = 'status-badge status-' . $status_lower;
    
    $icons = [
        'draft' => 'fas fa-edit',
        'sent' => 'fas fa-paper-plane',
        'paid' => 'fas fa-check-circle',
        'overdue' => 'fas fa-exclamation-triangle',
        'cancelled' => 'fas fa-times-circle',
        'partially paid' => 'fas fa-clock'
    ];
    
    $icon = $icons[$status_lower] ?? 'fas fa-info-circle';
    
    echo '<span class="' . $class . '">';
    echo '<i class="' . $icon . '"></i> ' . htmlspecialchars($status);
    echo '</span>';
}

function render_alert($message, $type = 'info', $dismissible = true) {
    $icons = [
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'danger' => 'fas fa-exclamation-circle',
        'info' => 'fas fa-info-circle'
    ];
    
    $icon = $icons[$type] ?? $icons['info'];
    
    echo '<div class="alert alert-' . $type . ($dismissible ? ' alert-dismissible' : '') . '">';
    echo '<i class="' . $icon . '"></i> ' . htmlspecialchars($message);
    if ($dismissible) {
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    }
    echo '</div>';
}
?>
