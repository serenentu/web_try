<?php
// order_confirmation.php - show order confirmation with complete details
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Show errors to help diagnose
ini_set('log_errors', 1);

session_start();

// Try to connect to database, but continue if it fails
$db_connected = false;
$pdo = null;
try {
    require_once 'db.php';
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database connection failed in order_confirmation.php: " . $e->getMessage());
    // Continue without database - use session fallback
}

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
if (!$order_id) {
    header('Location: index.html');
    exit;
}

$order = null;
$items = [];

// Try to fetch from database first
if ($db_connected && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([intval($order_id)]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Fetch order items
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$order['id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Error fetching order details from database: " . $e->getMessage());
        $order = null; // Reset to try session fallback
    }
}

// Fallback to session if database didn't work
if (!$order && isset($_SESSION['pending_order'])) {
    $session_order_id = strval($_SESSION['pending_order']['order_id']);
    $requested_order_id = strval($order_id);
    
    error_log("Checking session fallback: requested=$requested_order_id, session=$session_order_id");
    
    if ($session_order_id === $requested_order_id) {
        $order = $_SESSION['pending_order'];
        $items = $order['items'] ?? [];
        error_log("Order found in session: " . $session_order_id);
    }
}

// If still no order found, show error
if (!$order) {
    error_log("Order not found: order_id=$order_id, db_connected=" . ($db_connected ? 'true' : 'false') . ", has_session=" . (isset($_SESSION['pending_order']) ? 'yes' : 'no'));
    ?>
    <!doctype html>
    <html>
    <head>
        <title>Order Not Found</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            h1 { color: #d9534f; }
        </style>
    </head>
    <body>
        <h1>Order Not Found</h1>
        <p>The order you're looking for doesn't exist or has expired.</p>
        <p>Order ID requested: <code><?= htmlspecialchars($order_id) ?></code></p>
        <p><a href='index.html'>Go to Home</a> | <a href='products.php'>Continue Shopping</a></p>
        <hr>
        <details>
            <summary>Debug Info (for developers)</summary>
            <pre>
Database Connected: <?= $db_connected ? 'Yes' : 'No' ?>
Session Has Order: <?= isset($_SESSION['pending_order']) ? 'Yes' : 'No' ?>
<?php if (isset($_SESSION['pending_order'])): ?>
Session Order ID: <?= htmlspecialchars($_SESSION['pending_order']['order_id']) ?>
<?php endif; ?>
            </pre>
        </details>
    </body>
    </html>
    <?php
    exit;
}

function e($s) { return htmlspecialchars($s ?? ''); }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order Confirmation - BeautyVibe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .confirmation-container { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .success-icon { text-align: center; font-size: 64px; color: #28a745; margin-bottom: 20px; }
        h1 { color: #c44569; text-align: center; margin-bottom: 10px; }
        .order-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; margin: 8px 0; }
        .info-label { font-weight: 600; color: #555; }
        .order-items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .order-items-table th { background: #c44569; color: white; padding: 12px; text-align: left; }
        .order-items-table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .order-items-table tr:hover { background: #f8f9fa; }
        .total-row { font-weight: 700; font-size: 1.1em; color: #c44569; }
        .actions { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; padding: 12px 24px; margin: 5px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .btn-primary { background: #c44569; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; }
        .email-notice { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✓</div>
        <h1>Order Confirmed!</h1>
        <p style="text-align: center; color: #666; font-size: 1.1em;">Thank you for your purchase at BeautyVibe</p>

        <?php if (!empty($_SESSION['order_success'])): ?>
            <div class="email-notice">
                <?= e($_SESSION['order_success']) ?>
                <?php unset($_SESSION['order_success']); ?>
            </div>
        <?php else: ?>
            <div class="email-notice">
                <strong>📧 Confirmation Email Sent!</strong><br>
                A confirmation email has been sent to <strong><?= e($order['customer_email']) ?></strong> with your order details and tracking information.
            </div>
        <?php endif; ?>

        <div class="order-info">
            <h2 style="margin-top: 0;">Order Information</h2>
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span>#<?= intval($order_id) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Order Date:</span>
                <span><?= date('F d, Y - g:i A', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span style="color: #28a745; font-weight: 600;"><?= ucfirst(e($order['status'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Customer Name:</span>
                <span><?= e($order['customer_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span><?= e($order['customer_email']) ?></span>
            </div>
            <?php if ($order['customer_phone']): ?>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span><?= e($order['customer_phone']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['address_line1']): ?>
            <div class="info-row">
                <span class="info-label">Shipping Address:</span>
                <span>
                    <?= e($order['address_line1']) ?><?= $order['address_line2'] ? ', ' . e($order['address_line2']) : '' ?><br>
                    <?= e($order['city']) ?><?= $order['state'] ? ', ' . e($order['state']) : '' ?> <?= e($order['postal_code']) ?><br>
                    <?= e($order['country']) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <h2>Order Items</h2>
        <table class="order-items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): 
                    // Handle both database format (product_name) and session format (name)
                    $product_name = $item['product_name'] ?? $item['name'] ?? 'Unknown Product';
                    $price = floatval($item['price'] ?? 0);
                    $quantity = intval($item['quantity'] ?? 0);
                    // Calculate subtotal if not provided
                    $subtotal = isset($item['subtotal']) ? floatval($item['subtotal']) : ($price * $quantity);
                ?>
                <tr>
                    <td><?= e($product_name) ?></td>
                    <td>$<?= number_format($price, 2) ?></td>
                    <td><?= $quantity ?></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Total:</td>
                    <td>$<?= number_format($order['total'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if ($order['design_path']): ?>
        <div style="margin: 20px 0;">
            <h3>Your Uploaded Mock-up Design</h3>
            <img src="<?= e($order['design_path']) ?>" alt="Mock-up Design" style="max-width: 400px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>
        <?php endif; ?>

        <?php if ($order['customer_notes']): ?>
        <div style="margin: 20px 0;">
            <h3>Your Notes</h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <?= nl2br(e($order['customer_notes'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="actions">
            <a href="order_review.php?order_id=<?= intval($order_id) ?>" class="btn btn-primary">Review Order / Request Changes</a>
            <a href="order_history.php?email=<?= urlencode($order['customer_email']) ?>" class="btn btn-secondary">View Order History</a>
            <a href="index.html" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</body>
</html>
