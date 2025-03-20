# Exigo Plugin for WooCommerce

This plugin integrates WooCommerce with the Exigo API to synchronize customers and orders between systems. It handles customer registration, authentication, and order creation in Exigo.

## Table of Contents

- [Structure](#structure)
- [Key Components](#key-components)
- [API Endpoints](#api-endpoints)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Hooks and Filters](#hooks-and-filters)
- [Troubleshooting](#troubleshooting)

## Structure

The plugin follows a modular structure:

```
exigo-plugin/
├── css/
│   └── exigo-public.css
├── includes/
│   ├── class-database-handler.php
│   ├── class-exigo-api-handler.php
│   └── class-exigo-plugin.php
├── public/
│   └── class-exigo-public.php
├── exigo-plugin.php
└── uninstall.php
```

## Key Components

### 1. Main Plugin Class (`class-exigo-plugin.php`)

The primary plugin controller that:
- Loads dependencies
- Defines hooks
- Initializes session management

```php
// Main plugin initialization
public function __construct() {
    $this->load_dependencies();
    $this->define_public_hooks();
}
```

### 2. API Handler (`class-exigo-api-handler.php`)

Manages all communication with the Exigo API, including:
- Authentication
- Customer operations
- Order creation

```php
// API configuration
public function __construct() {
    $this->api_base_url = 'https://stem-api.exigo.com/3.0/';
    
    // Authentication setup
    $api_user = $_ENV['EXIGO_API_USER'] ?? '';
    $api_company = $_ENV['EXIGO_API_COMPANY_KEY'] ?? '';
    $api_password = $_ENV['EXIGO_API_PASSWORD'] ?? '';
    
    $auth_string = $api_user . '@' . $api_company . ':' . $api_password;
    $this->api_auth = base64_encode($auth_string);
}
```

### 3. Public Class (`class-exigo-public.php`)

Handles all front-end interactions:
- Customer registration form
- Login processes
- WooCommerce checkout integration
- Order processing

```php
// WooCommerce checkout validation
public function check_customer_validation() {
    if (!is_user_logged_in() || !$this->is_customer_validated()) {
        wp_safe_redirect(home_url('/registro-cliente'));
        exit;
    }
}
```

## API Endpoints

The plugin interacts with the following Exigo API endpoints:

### 1. Customer Authentication
- **Endpoint**: `/customers/authenticate`
- **Method**: POST
- **Used In**: `authenticate_customer()` in `class-exigo-api-handler.php`
- **Purpose**: Validates existing customer credentials

```php
public function authenticate_customer($username, $password) {
    $url = $this->api_base_url . "customers/authenticate";
    // Implementation details
}
```

### 2. Customer Lookup
- **Endpoint**: `/customers?customerID={id}`
- **Method**: GET
- **Used In**: `get_customer()` in `class-exigo-api-handler.php`
- **Purpose**: Finds customer/recruiter details by ID

```php
public function get_customer($customer_id) {
    $url = $this->api_base_url . "customers?customerID=" . $customer_id;
    // Implementation details
}
```

### 3. Customer Creation
- **Endpoint**: `/customers`
- **Method**: POST
- **Used In**: `create_customer()` in `class-exigo-api-handler.php`
- **Purpose**: Creates new customers in Exigo

```php
public function create_customer($customer_data) {
    $url = $this->api_base_url . "customers";
    // Implementation details
}
```

### 4. Order Creation
- **Endpoint**: `/orders`
- **Method**: POST
- **Used In**: `create_order()` in `class-exigo-api-handler.php`
- **Purpose**: Creates new orders in Exigo

```php
public function create_order($order_data) {
    $url = $this->api_base_url . "orders";
    // Implementation details
}
```

## WooCommerce Integration

The plugin integrates with WooCommerce through these key hooks:

```php
// In class-exigo-plugin.php
private function define_public_hooks() {
    add_action('woocommerce_before_checkout_form', array($this->public, 'check_customer_validation'));
    add_action('woocommerce_before_cart', array($this->public, 'check_customer_validation'));
    add_filter('woocommerce_get_checkout_url', array($this->public, 'modify_checkout_url'));
}

// In class-exigo-public.php
public function __construct($api_handler) {
    add_action('woocommerce_thankyou', array($this, 'process_exigo_order'), 10, 1);
    add_action('woocommerce_payment_complete', array($this, 'process_exigo_order'), 10, 1);
}
```

## Order Processing

The order creation flow is managed by `process_exigo_order()` in `class-exigo-public.php`:

```php
public function process_exigo_order($order_id) {
    // Validate order and customer
    // Prepare order details
    // Create order in Exigo
    // Store Exigo order ID in WooCommerce order metadata
}
```

Key data sent to Exigo includes:
- Customer ID
- Order date
- Currency code
- Shipping details
- Line items with SKUs, quantities and prices

## Required API Data Structure

For order creation, Exigo requires:

```json
{
  "customerID": 123,
  "orderStatus": null,
  "orderDate": "2025-02-10T00:00:00-06:00",
  "currencyCode": "MXN",
  "warehouseID": 1,
  "shipMethodID": 1,
  "priceType": 1,
  "country": "MX",
  "state": "JAL",
  "details": [
    {
      "itemCode": "PROD123",
      "quantity": 2,
      "warehouseID": 1,
      "priceType": 1,
      "price": 29.99
    }
  ]
}
```

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure Exigo API credentials via environment variables

## Configuration

Set the following environment variables:
- `EXIGO_API_USER` - Exigo API username
- `EXIGO_API_COMPANY_KEY` - Company key
- `EXIGO_API_PASSWORD` - API password

## Troubleshooting

Common issues:
- **API Authentication Errors**: Check credentials and environment variables
- **Invalid SKU Errors**: Ensure all WooCommerce products have valid SKUs matching Exigo
- **Order Creation Failures**: Verify required fields (country, state) are included

Debug mode can be enabled by adding to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```