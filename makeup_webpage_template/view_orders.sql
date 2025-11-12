-- View Orders in SQL - Useful Queries
-- Run with: mysql -u shop_user -pShopPass123! shop_db < view_orders.sql
-- Or copy and paste into MySQL client

-- 1. View all orders with customer info
SELECT 
    id AS order_id,
    customer_name,
    customer_email,
    customer_phone,
    total,
    status,
    created_at,
    payment_method,
    payment_last4
FROM orders
ORDER BY created_at DESC;

-- 2. View complete order details (orders + items)
SELECT 
    o.id AS order_id,
    o.customer_name,
    o.customer_email,
    o.total AS order_total,
    o.status,
    o.created_at,
    o.address_line1,
    o.city,
    o.state,
    o.postal_code,
    oi.product_name,
    oi.price AS unit_price,
    oi.quantity,
    oi.subtotal AS item_subtotal
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
ORDER BY o.created_at DESC, oi.id;

-- 3. View orders summary (with item count)
SELECT 
    o.id AS order_id,
    o.customer_name,
    o.customer_email,
    o.total,
    o.status,
    o.created_at,
    COUNT(oi.id) AS number_of_items
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.customer_name, o.customer_email, o.total, o.status, o.created_at
ORDER BY o.created_at DESC;

-- 4. View a specific order by ID (replace 1 with actual order_id)
SELECT 
    o.*,
    oi.product_name,
    oi.price,
    oi.quantity,
    oi.subtotal
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
WHERE o.id = 1
ORDER BY oi.id;

-- 5. View order statistics
SELECT 
    COUNT(*) AS total_orders,
    SUM(total) AS total_revenue,
    AVG(total) AS average_order_value,
    MIN(created_at) AS first_order,
    MAX(created_at) AS last_order
FROM orders;
