# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Multi-tenant SaaS billing/invoicing application built on **CodeIgniter 3** (PHP). The app manages invoices, sales, purchases, inventory, POS, customers, suppliers, subscriptions, and reporting for multiple stores (tenants).

## Architecture

### Multi-Tenant Database Strategy

The app uses a **single MySQL connection with runtime `USE` database switching** — not separate DB connections per tenant. The master database is `bill_xml`. On login, a tenant's `database_name` is stored in the session; `MY_Controller` issues `USE {tenant_db}` on every request, with a shutdown function that switches back to `bill_xml` so CI's session driver writes to the correct table (`bill_xml.ci_sessions`). SuperAdmin (store_id=1) always uses the master database.

All tables are prefixed with `db_` (e.g., `db_store`, `db_invoice`, `db_items`, `db_currency`).

### CodeIgniter 3 MVC Layout

- **Entry point:** `index.php` — bootstraps CI, sets environment via `CI_ENV` server variable (defaults to `production`)
- **`system/`** — stock CodeIgniter 3 framework (do not modify)
- **`application/core/MY_Controller.php`** — base controller. All controllers extend this. Handles: tenant DB switching, language loading, currency loading, subscription expiry/grace-period enforcement, permission checks, read-only mode for expired tenants
- **`application/core/MY_Model.php`** — thin wrapper (extends `CI_Model`)
- **`application/controllers/`** — ~80 controllers (one per feature domain)
- **`application/models/`** — ~65 models (naming: `Feature_model.php`)
- **`application/views/`** — ~148 view files, mix of standalone files and subdirectories
- **`application/helpers/`** — globally autoloaded helpers: `custom`, `inventory`, `accounts`, `appinfo`, `advance`, `saas`, `currency`, `foreign_currency`
- **`application/libraries/`** — payment gateways (PayPal, Stripe, Skrill, Instamojo), PDF generation (dompdf, TCPDF), barcode/QR

### Key Helpers

- **`saas_helper.php`** — SaaS mode functions (`store_module()`, `is_admin()`, `is_store_admin()`, `special_access()`)
- **`custom_helper.php`** — app-wide utilities (`demo_app()`, `app_version()`, store/user detail functions)
- **`inventory_helper.php`** — stock and item helpers
- **`accounts_helper.php`** — accounting helpers

### Authentication & Authorization

- Login controller is the default route (`$route['default_controller'] = 'login'`)
- Session-based auth stored in `ci_sessions` table in the master `bill_xml` database
- Role-based permissions checked via `MY_Controller::permissions()` querying `db_permissions` table
- User ID 1 is always admin (bypasses permission checks)

### Subscription & Grace Period Logic

Located in `MY_Controller::verify_store_and_user_status()`:
- Expired FREE packages get 30-day grace period
- Expired paid packages get 60-day grace period
- During grace period: read-only mode (POST requests blocked except whitelisted AJAX methods)
- After grace period: store auto-deactivated, user redirected to logout

### Frontend

- Static assets in `theme/` — Bootstrap 3/4.5.2, jQuery, various plugins
- Views use CI's `$this->load->view()` pattern
- Toastr for notifications, DataTables for lists

### Languages

Multi-language support via CI language files in `application/language/` (Thai default, plus Arabic, English, French, Spanish, Russian, etc.)

## Commands

### Install Dependencies
```bash
composer install
```

### Run Tests (CodeIgniter core tests only)
```bash
cd tests && phpunit
```

### Database

The master schema is in `bill_xml.sql`. Per-tenant migration SQLs are `bill_xml_st*.sql`. Database updates are handled programmatically via `Updates_model` (called on login via `MY_Controller::update_db()`).

### Local Development

Requires a PHP environment (Apache/Nginx + PHP + MySQL). Configure database credentials in `application/config/database.php`. Set `CI_ENV` server variable to `development` for error display.

## Important Patterns

- Controllers call `$this->load_global()` at the start of authenticated methods — this validates login, store status, subscription, and loads common view data into `$this->data`
- Currency formatting uses `$this->currency($value)` from `MY_Controller`
- Permission checks: `$this->permission_check('permission_name')` or `$this->permissions('name')` for boolean
- Store ownership validation: `$this->belong_to('table', $record_id)` ensures records belong to current store
- The `_obsolete_/` directory contains deprecated code — do not reference or modify
