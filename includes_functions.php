<?php
// General utility functions placeholder
// Add reusable functions here

// Utility functions
function sanitize($input) {
    if (!is_string($input)) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Email sending using PHP's mail() function (placeholder)
function send_invoice_email($to, $subject, $body, $headers = '') {
    // In production, use a library like PHPMailer for SMTP support
    return mail($to, $subject, $body, $headers);
}
