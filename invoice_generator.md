# Invoice Generator

A professional PHP-based invoice generation and management system for small businesses and freelancers.

## Overview

This application allows users to create, manage, and track professional invoices with client management, payment tracking, and reporting features. Built with PHP, MySQL, and modern web technologies.

## Core Features

### 1. Invoice Creation & Management
- **Create Professional Invoices**
  - Add company logo and branding
  - Customizable invoice templates (multiple designs)
  - Line items with description, quantity, rate, and total
  - Automatic tax calculations (configurable tax rates)
  - Discount support (percentage or fixed amount)
  - Invoice numbering (auto-increment with custom prefix)
  - Due date calculations
  - Notes and terms section

- **Invoice Status Tracking**
  - Draft (not sent)
  - Sent (awaiting payment)
  - Paid (fully paid)
  - Overdue (past due date)
  - Cancelled
  - Partially Paid

### 2. Client Management
- **Client Database**
  - Contact information (name, email, phone, address)
  - Company details
  - Billing address vs shipping address
  - Client notes and history
  - Default payment terms per client

- **Client Portal** (Optional)
  - Clients can view their invoices
  - Payment history
  - Download PDF invoices

### 3. Payment Tracking
- **Payment Recording**
  - Mark invoices as paid
  - Partial payment support
  - Payment method tracking (cash, check, bank transfer, etc.)
  - Payment date recording
  - Payment reference numbers

- **Payment Reminders**
  - Automatic email reminders for overdue invoices
  - Customizable reminder templates
  - Multiple reminder schedules (7 days, 14 days, 30 days overdue)

### 4. Reporting & Analytics
- **Financial Reports**
  - Monthly/quarterly/yearly revenue reports
  - Outstanding invoices report
  - Overdue invoices report
  - Client payment history
  - Tax reports for accounting

- **Dashboard**
  - Total revenue (monthly/yearly)
  - Outstanding balance
  - Overdue amount
  - Recent invoices
  - Payment trends chart

### 5. PDF Generation & Email
- **PDF Export**
  - Professional PDF invoices
  - Multiple template options
  - Downloadable invoices
  - Print-friendly format

- **Email Integration**
  - Send invoices directly via email
  - Customizable email templates
  - Email delivery confirmation
  - Automatic follow-up emails

## Technical Features

### User Management
- User registration and login
- Role-based access (Admin, User)
- Profile management
- Password reset functionality
- Session management

### Data Export/Import
- Export data to CSV/Excel
- Backup functionality
- Import clients from CSV
- Invoice template import/export

### Customization
- **Company Settings**
  - Company information
  - Logo upload
  - Default payment terms
  - Tax rates configuration
  - Invoice numbering format

- **Invoice Templates**
  - Multiple professional templates
  - Custom CSS styling options
  - Header/footer customization
  - Color scheme options

## Database Structure

### Tables Required
- `users` - User accounts and authentication
- `companies` - Company/business information
- `clients` - Client contact information
- `invoices` - Invoice headers and metadata
- `invoice_items` - Individual line items
- `payments` - Payment records
- `settings` - Application configuration
- `email_templates` - Customizable email templates

## Security Features
- Input validation and sanitization
- SQL injection prevention (prepared statements)
- CSRF protection
- Password hashing (bcrypt)
- Session security
- File upload validation
- Access control and permissions

## Installation Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- GD extension (for image processing)
- PDO MySQL extension
- cURL extension (for email features)

### Optional Requirements
- SMTP server for email functionality
- SSL certificate (recommended for production)
- Composer for dependency management

## File Structure
```
invoice-generator/
+-- config/
|   +-- database.php
|   +-- email.php
|   +-- app.php
+-- includes/
|   +-- auth.php
|   +-- functions.php
|   +-- pdf_generator.php
+-- templates/
|   +-- invoice_templates/
|   +-- email_templates/
+-- assets/
|   +-- css/
|   +-- js/
|   +-- images/
+-- dashboard/
+-- invoices/
+-- clients/
+-- payments/
+-- reports/
+-- uploads/
+-- vendor/ (if using Composer)
```

## Key Workflows

### Creating an Invoice
1. Select client (or create new)
2. Add line items (products/services)
3. Apply taxes and discounts
4. Preview invoice
5. Save as draft or send immediately
6. Generate PDF and/or email to client

### Payment Processing
1. Receive payment notification
2. Record payment amount and method
3. Update invoice status
4. Send payment confirmation
5. Update client payment history

### Monthly Reporting
1. Generate revenue reports
2. Identify overdue invoices
3. Send payment reminders
4. Export data for accounting
5. Review client payment patterns

## Success Metrics
- Reduce invoice creation time by 80%
- Improve payment collection rates
- Eliminate manual tracking errors
- Provide clear financial visibility
- Streamline client communication

## Future Enhancements
- Mobile app integration
- Online payment processing (Stripe/PayPal)
- Recurring invoice automation
- Multi-currency support
- API for third-party integrations
- Advanced reporting with charts
- Expense tracking integration
- Time tracking for service-based invoices

## Getting Started
1. Clone the repository
2. Configure database connection
3. Run database migrations
4. Set up email configuration
5. Upload company logo
6. Create first client
7. Generate first invoice

This invoice generator will help small businesses and freelancers manage their billing process efficiently while maintaining a professional appearance with clients.