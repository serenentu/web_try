-- Migration: add checkout/customer fields to orders and ensure order_items exists
-- Backup DB before running.

-- Create orders table if it doesn't exist (keeps things safe on fresh DBs).
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  session_token VARCHAR(128) DEFAULT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  customer_name VARCHAR(255) DEFAULT NULL,
  customer_email VARCHAR(255) DEFAULT NULL,
  customer_phone VARCHAR(50) DEFAULT NULL,
  address_line1 VARCHAR(255) DEFAULT NULL,
  address_line2 VARCHAR(255) DEFAULT NULL,
  city VARCHAR(100) DEFAULT NULL,
  state VARCHAR(100) DEFAULT NULL,
  postal_code VARCHAR(50) DEFAULT NULL,
  country VARCHAR(100) DEFAULT NULL,
  payment_method VARCHAR(50) DEFAULT NULL,
  payment_last4 VARCHAR(8) DEFAULT NULL,
  design_path VARCHAR(255) DEFAULT NULL,
  customer_notes TEXT DEFAULT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  updated_at DATETIME DEFAULT NULL,
  INDEX(user_id),
  INDEX(session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create order_items table if it doesn't exist
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  product_name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If an orders table already exists but is missing columns, add them (safe with IF NOT EXISTS variant).
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS address_line1 VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS address_line2 VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS city VARCHAR(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS state VARCHAR(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS postal_code VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS country VARCHAR(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS payment_last4 VARCHAR(8) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS design_path VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS customer_notes TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS status VARCHAR(50) NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT NULL;
