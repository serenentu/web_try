<?php
// order_history.php - dynamic order history page with authentication
session_start();

// Try to connect to database, but show friendly error if it fails
$db_connected = false;
$pdo = null;
$db_error = null;
try {
    require_once 'db.php';
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database connection failed in order_history.php: " . $e->getMessage());
    $db_error = "We're currently unable to connect to our database. Please try again later or contact support if the issue persists.";
}

$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$orders = [];
$show_form = true;

if ($email) {
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!$db_connected) {
        $error = $db_error;
    } else {
        // Fetch orders for this email
        try {
            $stmt = $pdo->prepare("
                SELECT o.id as order_id, o.created_at, o.status, o.total, o.customer_name
                FROM orders o
                WHERE o.customer_email = ?
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$email]);
            $orderRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch items for each order
            foreach ($orderRows as $orderRow) {
                $itemStmt = $pdo->prepare("
                    SELECT product_name, price, quantity, subtotal
                    FROM order_items
                    WHERE order_id = ?
                ");
                $itemStmt->execute([$orderRow['order_id']]);
                $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

                $orders[] = [
                    'order_id' => $orderRow['order_id'],
                    'created_at' => $orderRow['created_at'],
                    'status' => $orderRow['status'],
                    'total' => $orderRow['total'],
                    'customer_name' => $orderRow['customer_name'],
                    'items' => $items
                ];
            }

            // Store email in session for convenience
            $_SESSION['order_history_email'] = $email;
            $show_form = false;
        } catch (Exception $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            $error = "An error occurred while fetching your orders. Please try again later.";
        }
    }
}

function e($s) { return htmlspecialchars($s ?? ''); }

// Status color mapping
function getStatusColor($status) {
    $colors = [
        'pending' => '#ffc107',
        'paid' => '#28a745',
        'processing' => '#17a2b8',
        'shipped' => '#007bff',
        'delivered' => '#28a745',
        'cancelled' => '#dc3545',
        'failed' => '#dc3545'
    ];
    return $colors[strtolower($status)] ?? '#6c757d';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - BeautyVibe</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">   
    <style>
        h1 { font-size: 2em; color: #da8277dc; margin: 0 0 10px 10px; }        
        .email-form { background: #fff; height: auto; margin: 20px 50px 350px; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center; }
        .email-form input { padding: 12px; width: 100%; max-width: 400px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; }
        .email-form button { padding: 12px 30px; background: #da8277dc; color: white; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; cursor: pointer; margin-top: 15px; }
        .email-form button:hover { background: #f8b500; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .order-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 24px; margin-bottom: 28px; position: relative; }
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0; }
        .order-number { font-size: 1.2em; font-weight: 700; color: #333; }
        .order-date { color: #888; font-size: 0.95em; }
        .order-status { font-weight: 600; color: #fff; padding: 6px 16px; border-radius: 20px; font-size: 0.9em; }
        .order-items { margin: 16px 0; }
        .order-item { display: flex; align-items: center; border-bottom: 1px solid #f8f8fa; padding: 12px 0; }
        .item-details { flex: 1; }
        .item-name { font-size: 1em; font-weight: 500; color: #333; }
        .item-meta { color: #888; font-size: 0.9em; margin-top: 4px; }
        .item-price { font-size: 1em; color: #da8277dc; font-weight: 600; min-width: 80px; text-align: right; }
        .order-summary { text-align: right; margin-top: 16px; padding-top: 12px; border-top: 2px solid #f0f0f0; }
        .order-total { font-weight: 700; font-size: 1.2em; color: #da8277dc; }
        .order-actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .action-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9em; display: inline-block; }
        .btn-primary { background: #da8277dc; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .action-btn:hover { opacity: 0.9; }
        .empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; }
        .empty-state-icon { font-size: 64px; color: #ddd; }
        .empty-state h2 { color: #666; margin: 20px 0 10px; }
        .empty-state p { color: #999; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #da8277dc; text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<header class="header">
  <div class="ticker" role="region" aria-label="Scrolling text">
  <div class="ticker__text">
    <span>Catch our #WinterBeauty exclusive sale!</span>
    <span>Up to 30% off on selected items.</span>
    <span>Free shipping on orders over $50.</span>
    <span>Available only during Winter!</span>
    <span>Catch our #WinterBeauty exclusive sale!</span>
    <span>Up to 30% off on selected items.</span>
    <span>Free shipping on orders over $50.</span>
    <span>Available only during Winter!</span>
  </div>
</div>
  <div class="container">
    <nav class="nav-menu">
      <ul>
        <li><a href="Home.html">Home</a></li>
        <li><a href="index.html">Products</a></li>
        <li><a href="about.html">About Us</a></li>
      </ul>
    </nav>
    <div class="logo">
      <img src="BeautyVibeLogo.png" alt="BeautyVibe Logo">
    </div>
      <div class="search-box">
      <input type="text" id="SearchInput" placeholder="Search...">
      <i class="fas fa-search"></i>
      </div>
      <nav class="nav-menu">
        <ul>
          <li><a href="cart.php" class="cart-icon"><i class="fas fa-shopping-cart"></i></a></li>
          <li><a href="account.html" class="user-icon"><i class="fas fa-user"></i></a></li>
        </ul>
      </nav>
  </div>
</header>
        <?php if ($show_form): ?>
        <h1>Order History</h1>

        <?php if (isset($error)): ?>
        <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="email-form">
            <h2 style="margin-top: 0; color: #333;">View Your Orders</h2>
            <p style="color: #666;">Enter your email address to view your order history</p>
            <form method="get" action="order_history.php">
                <div>
                    <input type="email" name="email" placeholder="Enter your email address" required value="<?= e($email) ?>">
                </div>
                <button type="submit">View Orders</button>
            </form>
        </div>
        
        <?php else: ?>
        
        <a href="?show_form=1" class="back-link">← Enter Different Email</a>
        
        <div class="header">
            <h1>Order History</h1>
            <div class="breadcrumb">
                Showing orders for: <strong><?= e($email) ?></strong>
            </div>
        </div>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h2>No Orders Found</h2>
            <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
            <a href="index.html" class="action-btn btn-primary" style="margin-top: 20px;">Start Shopping</a>
        </div>
        <?php else: ?>
        
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-number">Order #<?= intval($order['order_id']) ?></div>
                    <div class="order-date">Placed on <?= date('F d, Y - g:i A', strtotime($order['created_at'])) ?></div>
                </div>
                <div class="order-status" style="background: <?= getStatusColor($order['status']) ?>">
                    <?= ucfirst(e($order['status'])) ?>
                </div>
            </div>
            
            <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                <div class="order-item">
                    <div class="item-details">
                        <div class="item-name"><?= e($item['product_name']) ?></div>
                        <div class="item-meta">
                            Quantity: <?= intval($item['quantity']) ?> × $<?= number_format($item['price'], 2) ?>
                        </div>
                    </div>
                    <div class="item-price">$<?= number_format($item['subtotal'], 2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <div class="order-total">Total: $<?= number_format($order['total'], 2) ?></div>
            </div>
            
            <div class="order-actions">
                <a href="order_confirmation.php?order_id=<?= intval($order['order_id']) ?>" class="action-btn btn-primary">View Details</a>
                <a href="order_review.php?order_id=<?= intval($order['order_id']) ?>" class="action-btn btn-secondary">Review / Request Changes</a>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.html" class="action-btn btn-primary">Continue Shopping</a>
        </div>
        
        <?php endif; ?>
<footer class="footer">
    <p>&copy; 2025 BeautyVibe. All rights reserved.</p>
</footer>
</body>
</html>
