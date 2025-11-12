<?php
// view_orders.php - View all orders from database
// Access via: http://localhost:8000/view_orders.php

session_start();
require_once 'db.php';

try {
    // Get all orders
    $stmt = $pdo->query("
        SELECT 
            o.id,
            o.customer_name,
            o.customer_email,
            o.total,
            o.status,
            o.created_at,
            o.payment_method,
            COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order items for selected order (if any)
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    $order_items = [];
    $selected_order = null;
    
    if ($order_id) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $selected_order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($selected_order) {
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

function e($s) { return htmlspecialchars($s ?? ''); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - BeautyVibe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #d4af37;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .orders-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .orders-table th {
            background: #d4af37;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .orders-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        .orders-table tr:hover {
            background: #f9f9f9;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.completed { background: #d4edda; color: #155724; }
        .order-detail {
            background: white;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-detail h3 {
            color: #d4af37;
            margin-top: 0;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }
        .detail-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .detail-value {
            color: #333;
            font-size: 14px;
            margin-top: 4px;
        }
        .items-table {
            width: 100%;
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            background: #d4af37;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn:hover {
            background: #b8941f;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            color: #d4af37;
        }
    </style>
</head>
<body>
    <h1>📦 BeautyVibe - Order Management</h1>
    
    <p><a href="index.html" class="btn">← Back to Store</a></p>
    
    <h2>All Orders (<?php echo count($orders); ?>)</h2>
    
    <?php if (empty($orders)): ?>
        <div class="empty">
            <p>No orders found in the database yet.</p>
            <p>Place an order through the checkout to see it here!</p>
        </div>
    <?php else: ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo e($order['id']); ?></strong></td>
                        <td><?php echo e($order['customer_name']); ?></td>
                        <td><?php echo e($order['customer_email']); ?></td>
                        <td class="total">$<?php echo number_format($order['total'], 2); ?></td>
                        <td><?php echo intval($order['item_count']); ?> item(s)</td>
                        <td><span class="status <?php echo e($order['status']); ?>"><?php echo e($order['status']); ?></span></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                        <td><a href="?order_id=<?php echo $order['id']; ?>" class="btn">View Details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <?php if ($selected_order): ?>
        <div class="order-detail">
            <h3>Order #<?php echo e($selected_order['id']); ?> - Details</h3>
            
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Customer Name</div>
                    <div class="detail-value"><?php echo e($selected_order['customer_name']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?php echo e($selected_order['customer_email']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><?php echo e($selected_order['customer_phone'] ?? 'N/A'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Order Total</div>
                    <div class="detail-value total">$<?php echo number_format($selected_order['total'], 2); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value"><span class="status <?php echo e($selected_order['status']); ?>"><?php echo e($selected_order['status']); ?></span></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Payment Method</div>
                    <div class="detail-value"><?php echo e($selected_order['payment_method'] ?? 'N/A'); ?></div>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-label">Address</div>
                    <div class="detail-value">
                        <?php 
                        echo e($selected_order['address_line1']);
                        if ($selected_order['address_line2']) echo ', ' . e($selected_order['address_line2']);
                        echo '<br>';
                        echo e($selected_order['city']) . ', ' . e($selected_order['state']) . ' ' . e($selected_order['postal_code']);
                        ?>
                    </div>
                </div>
            </div>
            
            <h3>Order Items</h3>
            <table class="orders-table items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo e($item['product_name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo intval($item['quantity']); ?></td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f9f9f9; font-weight: bold;">
                        <td colspan="3" style="text-align: right;">Total:</td>
                        <td class="total">$<?php echo number_format($selected_order['total'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
    
    <hr style="margin: 40px 0;">
    <p><small>To view orders directly in MySQL, use: <code>mysql -u shop_user -pShopPass123! shop_db -e "SELECT * FROM orders;"</code></small></p>
</body>
</html>
