#  Invoice Generator 🧾

A simple PHP-based web application for creating and managing invoices, clients, and payments.

## ✨ Features

*   **Dashboard:** Overview of total clients, invoices, revenue, pending invoices, and overdue invoices. Includes a chart for revenue overview and quick actions.
*   **Clients:** Manage clients (add, view, edit, delete).
*   **Invoices:** Manage invoices (create, view, edit, delete, download PDF, send via email).
*   **Payments:** Record and manage payments for invoices.
*   **Reports:** View business analytics (currently basic, with room for expansion).
*   **User Authentication:** Secure login and registration system.

## 🛠️ Technologies Used

*   PHP
*   SQLite
*   HTML, CSS, JavaScript
*   Chart.js (for charts)
*   FPDF (for PDF generation)

## 📋 Table of Contents

1.  [Getting Started](#🚀-getting-started)
    *   [Prerequisites](#✅-prerequisites)
    *   [Installation & Setup](#⚙️-installation--setup)
2.  [How to Use](#💡-how-to-use)
3.  [File Structure Overview](#📁-file-structure-overview)
4.  [Key Files and Functionality](#🔑-key-files-and-functionality)
5.  [Database Schema](#🗄️-database-schema)
6.  [Running Tests](#🧪-running-tests)
7.  [Contributing](#🤝-contributing)
8.  [License](#📜-license)

---

## 🚀 Getting Started

Follow these instructions to get a copy of the project up and running on your local machine.

### ✅ Prerequisites

*   A web server with PHP support (e.g., Apache, Nginx).
*   PHP (ensure SQLite PDO driver is enabled).
*   Git (for cloning the repository).
*   A web browser.

### ⚙️ Installation & Setup

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/ErikThiart/InvoiceGenerator.git
    cd InvoiceGenerator
    ```
    *(Or download and extract the ZIP file from GitHub.)*

2.  **Configure the Application:**
    Create your local configuration files by copying the `.example` templates:
    ```bash
    cp config_app.php.example config_app.php
    cp config_database.php.example config_database.php
    cp config_email.php.example config_email.php
    ```
    Then, edit each of the new `config_*.php` files to match your environment:
    *   `config_app.php`: Update `base_url` if your project isn\'t in the web server\'s root or uses a different domain.
    *   `config_database.php`: For the default SQLite setup, no changes are typically needed here.
    *   `config_email.php`: Fill in your SMTP server details if you intend to use the email functionality.

3.  **Database Setup:**
    *   The application uses an SQLite database (`invoice_generator.sqlite`) stored in the project root.
    *   This database file will be **automatically created** if it doesn\'t exist when the application first tries to access it (e.g., upon loading the registration page or logging in).
    *   Alternatively, you can create it manually using the provided schema:
        ```bash
        sqlite3 invoice_generator.sqlite < schema.sql
        ```

4.  **Create a Test User (Recommended for First Use):**
    *   Run the `create_test_user.php` script from your **command line** within the project root:
        ```bash
        php create_test_user.php
        ```
    *   This creates a user:
        *   **Email:** `test@example.com`
        *   **Password:** `password`
    *   ⚠️ **Security Note:** This script is for CLI execution only. For production, consider removing or further securing this script after initial setup.

5.  **Web Server Configuration:**
    *   Point your web server (e.g., Apache Virtual Host or Nginx server block) to the `InvoiceGenerator` project directory as its document root.
    *   Ensure `mod_rewrite` (for Apache) or equivalent URL rewriting capabilities are enabled if you plan to implement cleaner URLs in the future (the current routing is simple and doesn\'t strictly require it).

6.  **File Permissions:**
    *   Ensure your web server has write permissions to the project root directory if it needs to create the `invoice_generator.sqlite` file automatically. It also needs write permission to the `invoice_generator.sqlite` file itself once created.

---

## 💡 How to Use

1.  **Register/Login:** Access the application via your configured `base_url`. You\'ll be directed to register a new user or log in.
2.  **Dashboard:** Upon login, the dashboard provides an overview of your business metrics.
3.  **Manage Clients:** Use the "Clients" section to add, view, edit, or delete client records.
4.  **Manage Invoices:**
    *   Navigate to "Invoices" to see existing invoices.
    *   Click "Create Invoice" to generate new invoices, selecting clients and adding line items.
    *   View individual invoices to download as PDF or send via email (if email is configured).
5.  **Record Payments:** Track payments made against invoices in the "Payments" section or directly from an invoice view.
6.  **View Reports:** The "Reports" section offers business analytics (currently basic).

---

## 📁 File Structure Overview

*   `index.php`: Main application entry point and router.
*   `config_*.php.example`: Template configuration files.
*   `config_*.php`: Your local configuration files (ignored by Git).
*   `schema.sql`: Defines the SQLite database structure.
*   `invoice_generator.sqlite`: The SQLite database file (created locally, ignored by Git).
*   `create_test_user.php`: CLI script to create a default user.
*   `includes/`: Core PHP files (database connection, layout functions).
*   `includes_*.php`: PHP files for specific pages/modules (dashboard, clients, invoices, etc.).
*   `assets_css/`: CSS stylesheets.
*   `vendor/`: Third-party libraries (e.g., FPDF).
*   `tests/`: Application test suite.
    *   `RunAllTests.php`: Main script to execute all tests.
*   `.gitignore`: Specifies files and directories for Git to ignore.
*   `README.md`: This file.

---

## 🔑 Key Files and Functionality

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

---

## 🗄️ Database Schema (`schema.sql`)

*   **`users`**: Stores user credentials (email, hashed password).
*   **`clients`**: Stores client information (name, email, phone, company).
*   **`invoices`**: Stores invoice details (client ID, creation date, status, total amount). Linked to `clients`.
*   **`invoice_items`**: Stores individual line items for each invoice (description, quantity, rate, total). Linked to `invoices`.
*   **`payments`**: Stores payment records for invoices (invoice ID, amount, payment date, method, reference). Linked to `invoices`.

---

## 🧪 Running Tests

The application includes a comprehensive test suite. To execute all tests, run the following command from the project root:

```bash
php tests/RunAllTests.php
```

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change. Ensure to update tests as appropriate.

---

## 📜 License

This project is currently unlicensed. Consider adding an open-source license like MIT or Apache 2.0 if you wish to share it more broadly. You can create a `LICENSE` file in the root of the project and specify the terms there.
