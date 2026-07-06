# UkulimaBunifu

UkulimaBunifu is a PHP and MySQL farmer-to-buyer marketplace built to help farmers list produce, buyers browse and order it, and admins manage approvals, orders, transport, and stock updates.

The codebase currently uses the `FarmerLink` name in several page titles and UI labels, but the project folder and app can be treated as **UkulimaBunifu**.

## Features

- Farmer registration and login
- Buyer registration and login
- Role-based dashboards for farmers, buyers, and admins
- Farmer produce listings with review workflow
- Browse and filter approved produce listings
- Order and payment flow
- Admin tools for:
  - approving or rejecting listings
  - managing orders and payouts
  - viewing the approved catalog
  - tracking stock updates
  - managing transport options

## Tech Stack

- PHP
- MySQL / MariaDB
- HTML, CSS, JavaScript
- XAMPP-friendly local setup

## Requirements

- PHP 8.x or compatible PHP 7.4+
- MySQL or MariaDB
- Apache web server
- XAMPP recommended for local development

## Project Structure

- `index.php` - landing page
- `login.php` / `register.php` - authentication
- `browse.php` - public marketplace browsing
- `listing.php` - listing detail and ordering page
- `post-listing.php` - farmer listing submission
- `dashboard-buyer.php` - buyer dashboard
- `dashboard-farmer.php` - farmer dashboard
- `admin-dashboard.php` - admin overview
- `admin-listings.php` - listing review and approval
- `admin-orders.php` - order management
- `admin-catalog.php` - approved catalog management
- `admin-stock.php` - stock tracking
- `admin-transport.php` - transport management
- `payment.php` - checkout and payment page
- `server.php` - login and registration logic
- `marketplace_common.php` - shared helpers and database utilities
- `migrate_*.php` - database migration scripts

## Setup

### 1. Clone or place the project in your web root

If you are using XAMPP on Windows, place the folder in:

`C:\xampp\htdocs\toleo`

### 2. Create the database

Create a MySQL database for the project, then import or create the required tables used by the app.

The app expects tables such as:

- `users`
- `listings`
- `orders`
- `order_status`
- `transport_options`
- `stock_updates`

### 3. Configure the database connection

Copy `database_config.example.php` to `database_config.php` and update the credentials:

```php
return array(
    'host' => 'localhost',
    'username' => 'your_db_user',
    'password' => 'your_db_password',
    'database' => 'your_db_name',
);
```

Do not commit real database credentials.

### 4. Run the migrations

Open the migration scripts in your browser, or run them from your local environment if preferred:

- `migrate_admin_marketplace.php`
- `migrate_add_delivery.php`
- `migrate_add_order_fees.php`
- `migrate_add_order_payment.php`

These scripts add newer admin, payment, transport, and stock-related columns/tables.

### 5. Open the app

Visit the project in your browser, for example:

`http://localhost/webappname/`

## User Roles

### Farmer

- Registers as a farmer
- Posts produce listings
- Views farmer dashboard
- Tracks listing and order activity

### Buyer

- Registers as a buyer
- Browses approved produce
- Places orders
- Completes payment on the payment page

### Admin

- Reviews and approves farmer listings
- Manages orders and payouts
- Maintains the approved catalog
- Oversees stock updates and transport options

## Notes

- Listings only appear in the public browse page after they are approved by an admin.
- `database_config.php` is intentionally kept separate from the example config.
- The payment page currently integrates with third-party payment scripts and includes placeholders for provider configuration.

## Troubleshooting

- If login fails, confirm the database is connected and the `users` table exists.
- If pages redirect unexpectedly, make sure you are logged in with the correct role.
- If you see a database connection error, check `database_config.php`.

## License

No license file is currently included. Add one if you want to publish or share the project publicly.

## You can check out the app

https://ukulimabunifu.app/
