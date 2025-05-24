<?php
// Add Payment page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes_functions.php';
require_login();

$error = '';
$success = '';
$invoice_id = intval($_GET['invoice_id'] ?? 0);

// Get invoice details if invoice_id is provided
$invoice = null;
if ($invoice_id) {
    $stmt = $pdo->prepare('
        SELECT i.*, c.name as client_name,
               COALESCE(SUM(p.amount), 0) as paid_amount
        FROM invoices i 
        LEFT JOIN clients c ON i.client_id = c.id 
        LEFT JOIN payments p ON i.id = p.invoice_id
        WHERE i.id = ?
        GROUP BY i.id
    ');
    $stmt->execute([$invoice_id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_id = intval($_POST['invoice_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
    $method = sanitize($_POST['method'] ?? '');
    $reference = sanitize($_POST['reference'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!$invoice_id) {
        $error = 'Please select an invoice.';
    } elseif ($amount <= 0) {
        $error = 'Payment amount must be greater than zero.';
    } else {
        // Get current invoice details
        $stmt = $pdo->prepare('
            SELECT i.total, COALESCE(SUM(p.amount), 0) as paid_amount
            FROM invoices i 
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE i.id = ?
            GROUP BY i.id
        ');
        $stmt->execute([$invoice_id]);
        $inv_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inv_data) {
            $error = 'Invoice not found.';
        } else {
            $remaining_balance = $inv_data['total'] - $inv_data['paid_amount'];
            
            if ($amount > $remaining_balance) {
                $error = 'Payment amount cannot exceed the remaining balance of $' . number_format($remaining_balance, 2);
            } else {
                // Add the payment
                $stmt = $pdo->prepare('INSERT INTO payments (invoice_id, amount, payment_date, method, reference, notes) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$invoice_id, $amount, $payment_date, $method, $reference, $notes]);
                
                // Update invoice status if fully paid
                $new_paid_amount = $inv_data['paid_amount'] + $amount;
                if ($new_paid_amount >= $inv_data['total']) {
                    $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?')->execute(['Paid', $invoice_id]);
                }
                
                $success = 'Payment recorded successfully!';
                // Clear form data
                $_POST = [];
            }
        }
    }
}

// Get invoices with outstanding balance for dropdown
$invoices = $pdo->query('
    SELECT i.id, i.total, c.name as client_name,
           COALESCE(SUM(p.amount), 0) as paid_amount,
           (i.total - COALESCE(SUM(p.amount), 0)) as balance
    FROM invoices i 
    LEFT JOIN clients c ON i.client_id = c.id 
    LEFT JOIN payments p ON i.id = p.invoice_id
    WHERE i.status != "Paid"
    GROUP BY i.id
    HAVING balance > 0
    ORDER BY i.created_at DESC
')->fetchAll(PDO::FETCH_ASSOC);

render_header('Record Payment - Invoice Generator', 'payments');
render_page_header(
    'Record Payment', 
    'Add a payment record for an invoice'
);

if ($error) {
    render_alert($error, 'danger');
}
if ($success) {
    render_alert($success, 'success');
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-dollar-sign"></i> Payment Details</h5>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="invoice_id" class="form-label">
                                    <i class="fas fa-file-invoice"></i> Invoice <span class="text-danger">*</span>
                                </label>
                                <select name="invoice_id" id="invoice_id" class="form-select" required onchange="updateInvoiceDetails()">
                                    <option value="">Select an invoice...</option>
                                    <?php foreach ($invoices as $inv): ?>
                                    <option value="<?= $inv['id'] ?>" 
                                            <?= ($invoice && $inv['id'] == $invoice['id']) ? 'selected' : '' ?>
                                            data-total="<?= $inv['total'] ?>"
                                            data-paid="<?= $inv['paid_amount'] ?>"
                                            data-balance="<?= $inv['balance'] ?>"
                                            data-client="<?= htmlspecialchars($inv['client_name']) ?>">
                                        #<?= str_pad($inv['id'], 4, '0', STR_PAD_LEFT) ?> - <?= htmlspecialchars($inv['client_name']) ?> 
                                        (Balance: $<?= number_format($inv['balance'], 2) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select an invoice.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    <i class="fas fa-money-bill"></i> Payment Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           name="amount" 
                                           id="amount" 
                                           class="form-control" 
                                           min="0.01" 
                                           step="0.01" 
                                           value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>"
                                           required 
                                           placeholder="0.00">
                                    <div class="invalid-feedback">Please enter a valid payment amount.</div>
                                </div>
                                <small class="form-text text-muted" id="balanceHelp"></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">
                                    <i class="fas fa-calendar"></i> Payment Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="payment_date" 
                                       id="payment_date" 
                                       class="form-control" 
                                       value="<?= $_POST['payment_date'] ?? date('Y-m-d') ?>" 
                                       required>
                                <div class="invalid-feedback">Please select a payment date.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="method" class="form-label">
                                    <i class="fas fa-credit-card"></i> Payment Method
                                </label>
                                <select name="method" id="method" class="form-select">
                                    <option value="">Select method...</option>
                                    <option value="Cash" <?= ($_POST['method'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                                    <option value="Check" <?= ($_POST['method'] ?? '') === 'Check' ? 'selected' : '' ?>>Check</option>
                                    <option value="Credit Card" <?= ($_POST['method'] ?? '') === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                                    <option value="Bank Transfer" <?= ($_POST['method'] ?? '') === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                    <option value="PayPal" <?= ($_POST['method'] ?? '') === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                                    <option value="Other" <?= ($_POST['method'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference" class="form-label">
                                    <i class="fas fa-hashtag"></i> Reference/Transaction ID
                                </label>
                                <input type="text" 
                                       name="reference" 
                                       id="reference" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>"
                                       placeholder="Check number, transaction ID, etc.">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note"></i> Notes
                                </label>
                                <textarea name="notes" 
                                          id="notes" 
                                          class="form-control" 
                                          rows="2" 
                                          placeholder="Additional notes about this payment..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details Panel (shown when invoice is selected) -->
                    <div id="invoiceDetails" class="alert alert-info d-none">
                        <h6><i class="fas fa-info-circle"></i> Invoice Details</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>Client:</strong> <span id="detailClient">-</span><br>
                                <strong>Invoice Total:</strong> $<span id="detailTotal">0.00</span>
                            </div>
                            <div class="col-sm-6">
                                <strong>Amount Paid:</strong> $<span id="detailPaid">0.00</span><br>
                                <strong>Remaining Balance:</strong> $<span id="detailBalance">0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=payments" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Payments
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateInvoiceDetails() {
    const select = document.getElementById('invoice_id');
    const detailsPanel = document.getElementById('invoiceDetails');
    const amountInput = document.getElementById('amount');
    const balanceHelp = document.getElementById('balanceHelp');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const total = parseFloat(option.dataset.total);
        const paid = parseFloat(option.dataset.paid);
        const balance = parseFloat(option.dataset.balance);
        const client = option.dataset.client;
        
        // Update details panel
        document.getElementById('detailClient').textContent = client;
        document.getElementById('detailTotal').textContent = total.toFixed(2);
        document.getElementById('detailPaid').textContent = paid.toFixed(2);
        document.getElementById('detailBalance').textContent = balance.toFixed(2);
        
        // Set max amount and placeholder
        amountInput.max = balance.toFixed(2);
        amountInput.placeholder = balance.toFixed(2);
        balanceHelp.textContent = `Maximum amount: $${balance.toFixed(2)}`;
        
        detailsPanel.classList.remove('d-none');
    } else {
        detailsPanel.classList.add('d-none');
        amountInput.removeAttribute('max');
        amountInput.placeholder = '0.00';
        balanceHelp.textContent = '';
    }
}

// Initialize if invoice is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    updateInvoiceDetails();
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>

<?php render_footer(); ?>
