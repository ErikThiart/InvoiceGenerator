# Invoice Generator

This is a web-based application for managing invoices, clients, and payments.

## Features

*   **Dashboard:** Overview of total clients, invoices, revenue, pending invoices, and overdue invoices. Includes a chart for revenue overview and quick actions.
*   **Clients:** Manage clients (add, view, edit, delete).
*   **Invoices:** Manage invoices (create, view, edit, delete, download PDF, send via email).
*   **Payments:** Record and manage payments for invoices.
*   **Reports:** View business analytics (not fully implemented in the provided code).
*   **User Authentication:** Secure login and registration system.

## Technologies Used

*   PHP
*   SQLite (for database)
*   HTML, CSS, JavaScript
*   Chart.js (for charts)
*   FPDF (for PDF generation)

## Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/ErikThiart/InvoiceGenerator.git
    # or if you\\'ve downloaded a ZIP:
    # unzip InvoiceGenerator-main.zip (or similar, depending on the downloaded file name)
    cd InvoiceGenerator
    ```
2.  **Configure the application:**
    *   Copy `config_app.php.example` to `config_app.php` and update the settings (e.g., `base_url`).
        ```bash
        cp config_app.php.example config_app.php
        ```
    *   Copy `config_database.php.example` to `config_database.php`. For the default SQLite setup, no changes are usually needed in this file as the path is relative.
        ```bash
        cp config_database.php.example config_database.php
        ```
    *   Copy `config_email.php.example` to `config_email.php` and update with your SMTP server details if you plan to use the "send invoice via email" feature.
        ```bash
        cp config_email.php.example config_email.php
        ```
    *   After copying, edit each `config_*.php` file to set your specific values.
3.  **Database Setup:**
    *   The application uses an SQLite database by default. The schema is defined in `schema.sql`.
    *   The SQLite database file (`invoice_generator.sqlite`) will be created automatically in the project root if it doesn\'t exist, when the application first tries to access the database (e.g., on first load or registration).
    *   Alternatively, you can create it manually using the schema:
        ```bash
        sqlite3 invoice_generator.sqlite < schema.sql
        ```
4.  **Create a test user (optional but recommended for first use):**
    *   Run the `create_test_user.php` script **from your command line** in the project root:
        ```bash
        php create_test_user.php
        ```
    *   This will create a user with email `test@example.com` and password `password`.
    *   **Security Note:** This script should ideally be removed or secured after initial setup in a production environment. It is designed for CLI execution only.
5.  **Web Server Configuration:**
    *   Point your web server (Apache, Nginx, etc.) to the project directory.
    *   Ensure `mod_rewrite` (or equivalent) is enabled if you plan to use cleaner URLs (not explicitly handled by the current routing in `index.php` but good practice).
6.  **Permissions:**
    *   Ensure the web server has write permissions to the `invoice_generator.sqlite` file if it needs to create or modify it.

## How to Use

1.  **Register/Login:** Access the application through your web browser. You'll be prompted to log in or register.
2.  **Dashboard:** After logging in, you'll see the dashboard with an overview of your business.
3.  **Manage Clients:** Navigate to the "Clients" section to add, view, edit, or delete clients.
4.  **Manage Invoices:**
    *   Go to the "Invoices" section to view existing invoices.
    *   Click "Create Invoice" to generate a new invoice. You'll need to select a client and add line items.
    *   From the invoice view, you can download it as a PDF or send it via email (if email is configured).
5.  **Record Payments:** In the "Payments" section (or potentially from an invoice view), you can record payments made against invoices.
6.  **View Reports:** Access the "Reports" section for business analytics.

## File Structure Overview

*   `index.php`: Main entry point and router for the application.
*   `config_*.php`: Your local configuration files (ignored by Git).
*   `config_*.php.example`: Example configuration files. Copy these to `config_*.php` and customize.
*   `schema.sql`: SQL schema for the database.
*   `invoice_generator.sqlite`: The SQLite database file.
*   `includes/`: Contains core include files.
    *   `db.php`: Handles database connection.
    *   `layout.php`: Functions for rendering common layout elements (header, footer).
*   `includes_*.php`: PHP files handling specific pages/modules (e.g., `includes_dashboard.php`, `includes_clients.php`, `includes_invoices.php`).
*   `assets_css/`: CSS stylesheets for the application.
*   `vendor/`: Contains third-party libraries (like FPDF for PDF generation).
*   `tests/`: Contains unit, integration, and functional tests for the application.
    *   `RunAllTests.php`: Script to execute the entire test suite.
*   `.gitignore`: Specifies intentionally untracked files that Git should ignore.
*   `README.md`: This file.

## Key Files and Functionality

*   **`index.php`**:
    *   Acts as the main controller, routing requests to the appropriate `includes_*.php` file based on the `page` query parameter.
*   **`includes_auth.php`**:
    *   Manages user sessions, login, logout, and access control (`require_login()`).
*   **`includes/db.php`**:
    *   Establishes the connection to the SQLite database using PDO.
*   **`includes_dashboard.php`**:
    *   Fetches and displays summary statistics (total clients, invoices, revenue, etc.).
    *   Renders the revenue overview chart using Chart.js.
*   **`includes_clients.php` & `includes_create_client.php`**:
    *   Handle listing, adding, and potentially editing/viewing clients.
*   **`includes_invoices.php`, `includes_create_invoice.php`, `includes_view_invoice.php`**:
    *   Manage the lifecycle of invoices: creation, listing, detailed view.
*   **`includes_download_invoice.php` & `includes_pdf_generator.php`**:
    *   Responsible for generating a PDF version of an invoice using the FPDF library.
*   **`includes_send_invoice.php`**:
    *   Handles sending invoices via email (requires email configuration).
*   **`includes_payments.php` & `includes_add_payment.php`**:
    *   Manage recording and viewing payments.
*   **`includes_functions.php`**:
    *   Likely contains utility functions used across the application (e.g., `redirect()`, `render_status_badge()`).
*   **`includes/layout.php`**:
    *   Provides functions like `render_header()` and `render_footer()` to maintain a consistent look and feel.

## Database Schema (`schema.sql`)

*   **`users`**: Stores user credentials (email, hashed password).
*   **`clients`**: Stores client information (name, email, phone, company).
*   **`invoices`**: Stores invoice details (client ID, creation date, status, total amount). Linked to `clients`.
*   **`invoice_items`**: Stores individual line items for each invoice (description, quantity, rate, total). Linked to `invoices`.
*   **`payments`**: Stores payment records for invoices (invoice ID, amount, payment date, method, reference). Linked to `invoices`.

## Running Tests

The application includes a test suite to ensure functionality.
To run all tests, execute the following command from the project root:

```bash
php tests/RunAllTests.php
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.
