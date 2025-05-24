<?php
// Create Invoice page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes_functions.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id'] ?? 0);
    $invoice_number = sanitize($_POST['invoice_number'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $due_date = $_POST['due_date'] ?? '';
    $status = 'draft';
    $notes = sanitize($_POST['notes'] ?? '');
    $items = $_POST['items'] ?? [];
    
    if ($client_id && !empty($items)) {
        try {
            $pdo->beginTransaction();
            
            // Calculate total
            $total = 0;
            foreach ($items as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                $rate = floatval($item['rate'] ?? 0);
                if ($qty > 0 && $rate >= 0) {
                    $total += $qty * $rate;
                }
            }
            
            // Generate invoice number if not provided
            if (!$invoice_number) {
                $invoice_number = 'INV-' . date('Y') . '-' . str_pad($pdo->query('SELECT COUNT(*) + 1 FROM invoices')->fetchColumn(), 4, '0', STR_PAD_LEFT);
            }
            
            $stmt = $pdo->prepare('INSERT INTO invoices (client_id, invoice_number, created_at, due_date, status, total, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$client_id, $invoice_number, $date, $due_date ?: null, $status, $total, $notes]);
            $invoice_id = $pdo->lastInsertId();
            
            // Add invoice items
            $item_stmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, rate, total) VALUES (?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $desc = sanitize($item['description'] ?? '');
                $qty = floatval($item['quantity'] ?? 0);
                $rate = floatval($item['rate'] ?? 0);
                if ($desc && $qty > 0 && $rate >= 0) {
                    $item_total = $qty * $rate;
                    $item_stmt->execute([$invoice_id, $desc, $qty, $rate, $item_total]);
                }
            }
            
            $pdo->commit();
            $success = 'Invoice created successfully!';
            
            // Redirect after success
            header('Location: index.php?page=view_invoice&id=' . $invoice_id);
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollback();
            $error = 'Error creating invoice: ' . $e->getMessage();
        }
    } else {
        $error = 'Please select a client and add at least one item.';
    }
}

// Get clients for dropdown
$clients = $pdo->query('SELECT id, name, company FROM clients ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

// Get pre-selected client if passed via URL
$selected_client = intval($_GET['client_id'] ?? 0);

render_header('Create Invoice - Invoice Generator', 'create_invoice');
?>

<?php render_page_header('Create New Invoice', 'Generate a new invoice for a client', [
    '<a href="index.php?page=invoices" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Invoices
    </a>'
]); ?>

<?php if ($error): ?>
    <?= render_alert('error', $error) ?>
<?php endif; ?>

<?php if ($success): ?>
    <?= render_alert('success', $success) ?>
<?php endif; ?>

<form method="POST" class="form-modern" id="invoiceForm">
    <div class="form-container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice"></i> Invoice Details</h3>
            </div>
            <div class="card-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="client_id" class="form-label required">
                            <i class="fas fa-user"></i> Client
                        </label>
                        <select id="client_id" name="client_id" class="form-control" required>
                            <option value="">Select a client...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= $selected_client == $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['name']) ?>
                                    <?php if ($client['company']): ?>
                                        - <?= htmlspecialchars($client['company']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number" class="form-label">
                            <i class="fas fa-hashtag"></i> Invoice Number
                        </label>
                        <input type="text" 
                               id="invoice_number" 
                               name="invoice_number" 
                               class="form-control" 
                               placeholder="Auto-generated if empty">
                        <div class="form-help">Leave blank to auto-generate</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="date" class="form-label required">
                            <i class="fas fa-calendar"></i> Invoice Date
                        </label>
                        <input type="date" 
                               id="date" 
                               name="date" 
                               class="form-control" 
                               value="<?= date('Y-m-d') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date" class="form-label">
                            <i class="fas fa-calendar-check"></i> Due Date
                        </label>
                        <input type="date" 
                               id="due_date" 
                               name="due_date" 
                               class="form-control" 
                               value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes" class="form-label">
                        <i class="fas fa-sticky-note"></i> Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              class="form-control" 
                              rows="3"
                              placeholder="Additional notes or terms..."></textarea>
                </div>
            </div>
        </div>
        
        <!-- Invoice Items -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Invoice Items</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-outline" onclick="addItemRow()">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </div>
            <div class="card-content">
                <div class="table-container">
                    <table id="itemsTable" class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 40%">Description</th>
                                <th style="width: 15%">Quantity</th>
                                <th style="width: 20%">Rate ($)</th>
                                <th style="width: 20%">Total</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr class="item-row">
                                <td>
                                    <input type="text" 
                                           name="items[0][description]" 
                                           class="form-control" 
                                           placeholder="Item description..."
                                           required>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[0][quantity]" 
                                           class="form-control" 
                                           min="0.01" 
                                           step="0.01" 
                                           value="1"
                                           onchange="updateItemTotal(this)"
                                           required>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[0][rate]" 
                                           class="form-control" 
                                           min="0" 
                                           step="0.01" 
                                           value="0"
                                           onchange="updateItemTotal(this)"
                                           required>
                                </td>
                                <td>
                                    <span class="item-total">$0.00</span>
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="removeItemRow(this)"
                                            data-tooltip="Remove Item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="invoice-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value" id="subtotalDisplay">$0.00</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value" id="totalDisplay">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                <i class="fas fa-save"></i> Create Invoice
            </button>
            <a href="index.php?page=invoices" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </div>
</form>

<script>
let itemIndex = 1;

function addItemRow() {
    const tbody = document.getElementById('itemsTableBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.innerHTML = `
        <td>
            <input type="text" 
                   name="items[${itemIndex}][description]" 
                   class="form-control" 
                   placeholder="Item description..."
                   required>
        </td>
        <td>
            <input type="number" 
                   name="items[${itemIndex}][quantity]" 
                   class="form-control" 
                   min="0.01" 
                   step="0.01" 
                   value="1"
                   onchange="updateItemTotal(this)"
                   required>
        </td>
        <td>
            <input type="number" 
                   name="items[${itemIndex}][rate]" 
                   class="form-control" 
                   min="0" 
                   step="0.01" 
                   value="0"
                   onchange="updateItemTotal(this)"
                   required>
        </td>
        <td>
            <span class="item-total">$0.00</span>
        </td>
        <td>
            <button type="button" 
                    class="btn btn-sm btn-danger" 
                    onclick="removeItemRow(this)"
                    data-tooltip="Remove Item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    itemIndex++;
    updateInvoiceTotal();
}

function removeItemRow(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('.item-row').length > 1) {
        row.remove();
        updateInvoiceTotal();
    } else {
        showAlert('warning', 'At least one item is required.');
    }
}

function updateItemTotal(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
    const total = quantity * rate;
    
    row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
    updateInvoiceTotal();
}

function updateInvoiceTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
        const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
        total += quantity * rate;
    });
    
    document.getElementById('subtotalDisplay').textContent = '$' + total.toFixed(2);
    document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('invoiceForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const clientId = document.getElementById('client_id').value;
        const itemRows = document.querySelectorAll('.item-row');
        
        if (!clientId) {
            e.preventDefault();
            showAlert('error', 'Please select a client.');
            return;
        }
        
        let hasValidItems = false;
        itemRows.forEach(row => {
            const desc = row.querySelector('input[name*="[description]"]').value.trim();
            const qty = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
            
            if (desc && qty > 0 && rate >= 0) {
                hasValidItems = true;
            }
        });
        
        if (!hasValidItems) {
            e.preventDefault();
            showAlert('error', 'Please add at least one valid item.');
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });
    
    // Initialize tooltips and form validation
    initTooltips();
    enableFormValidation('invoiceForm');
    updateInvoiceTotal();
});
</script>

<?php render_footer(); ?>
