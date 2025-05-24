<?php
// Create Client page (requires login)
require_once __DIR__ . '/includes_auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes_functions.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $company = sanitize($_POST['company'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if ($name && $email) {
        try {
            $stmt = $pdo->prepare('INSERT INTO clients (name, email, phone, company, address, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $phone, $company, $address, $notes]);
            $success = 'Client created successfully!';
            // Clear form data
            $name = $email = $phone = $company = $address = $notes = '';
        } catch (PDOException $e) {
            $error = 'Error creating client: ' . $e->getMessage();
        }
    } else {
        $error = 'Name and email are required fields.';
    }
}

render_header('Add Client - Invoice Generator', 'create_client');
?>

<?php 
render_page_header(
    'Add New Client', 
    'Create a new client record',
    [
        [
            'text' => 'Back to Clients',
            'url' => 'index.php?page=clients',
            'class' => 'btn-outline-secondary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
); 
?>

<?php if ($error): ?>
    <?php render_alert($error, 'danger'); ?>
<?php endif; ?>

<?php if ($success): ?>
    <?php render_alert($success, 'success'); ?>
<?php endif; ?>

<div class="form-container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-plus"></i> Client Information</h3>
        </div>
        <div class="card-content">
            <form method="POST" class="form-modern" id="clientForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label required">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               value="<?= htmlspecialchars($name ?? '') ?>"
                               placeholder="Enter client's full name"
                               required>
                        <div class="form-help">The primary contact name for this client</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               placeholder="client@example.com"
                               required>
                        <div class="form-help">Primary email for invoices and communication</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-control" 
                               value="<?= htmlspecialchars($phone ?? '') ?>"
                               placeholder="+1 (555) 123-4567">
                        <div class="form-help">Contact phone number (optional)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="company" class="form-label">
                            <i class="fas fa-building"></i> Company Name
                        </label>
                        <input type="text" 
                               id="company" 
                               name="company" 
                               class="form-control" 
                               value="<?= htmlspecialchars($company ?? '') ?>"
                               placeholder="Company or Organization">
                        <div class="form-help">Business or organization name (optional)</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <textarea id="address" 
                              name="address" 
                              class="form-control" 
                              rows="3"
                              placeholder="Business or billing address"><?= htmlspecialchars($address ?? '') ?></textarea>
                    <div class="form-help">Complete address for billing and correspondence</div>
                </div>
                
                <div class="form-group">
                    <label for="notes" class="form-label">
                        <i class="fas fa-sticky-note"></i> Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              class="form-control" 
                              rows="4"
                              placeholder="Additional notes about this client..."><?= htmlspecialchars($notes ?? '') ?></textarea>
                    <div class="form-help">Internal notes and client preferences</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                        <i class="fas fa-save"></i> Create Client
                    </button>
                    <a href="index.php?page=clients" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clientForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        
        if (!name || !email) {
            e.preventDefault();
            showAlert('error', 'Please fill in all required fields.');
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });
    
    // Enable form validation styling
    enableFormValidation('clientForm');
});
</script>

<?php render_footer(); ?>
