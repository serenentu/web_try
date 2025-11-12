<?php
session_start();

// Try to connect to database, but show friendly error if it fails
$db_connected = false;
$pdo = null;
try {
    require_once 'db.php';
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database connection failed in order_review.php: " . $e->getMessage());
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if (!$order_id) { header('Location: products.php'); exit; }

if (!$db_connected) {
    // Exit early if database is not connected - $pdo will be null here
    echo "<!doctype html><html><head><title>Service Unavailable</title></head><body>";
    echo "<h1>Service Temporarily Unavailable</h1>";
    echo "<p>We're currently unable to connect to our database. Please try again later.</p>";
    echo "<p><a href='products.php'>Go to Products</a></p></body></html>";
    exit;
}

// At this point, $db_connected is true and $pdo is set by db.php
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) { echo "Order not found."; exit; }

    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching order: " . $e->getMessage());
    echo "<!doctype html><html><head><title>Error</title></head><body>";
    echo "<h1>Error Loading Order</h1>";
    echo "<p>An error occurred while loading order details. Please try again later.</p></body></html>";
    exit;
}

function e($s){ return htmlspecialchars($s ?? ''); }
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Review Order #<?=intval($order_id)?></title></head>
<body>
  <h1>Review Order #<?=intval($order_id)?></h1>
  <p>Status: <?=e($order['status'])?></p>
  <p>Customer: <?=e($order['customer_name'])?> — <?=e($order['customer_email'])?></p>

  <?php if ($order['design_path']): ?>
    <h3>Mock-up</h3>
    <img src="<?=e($order['design_path'])?>" alt="Design" style="max-width:600px;">
  <?php else: ?>
    <p>No mock-up uploaded.</p>
  <?php endif; ?>

  <h3>Your notes</h3>
  <p><?=nl2br(e($order['customer_notes']))?></p>

  <h3>Request changes or approve</h3>
  <form method="post" action="review_submit.php" enctype="multipart/form-data">
    <input type="hidden" name="order_id" value="<?=intval($order_id)?>">
    <label>
      Request or comment (describe final changes) <br>
      <textarea name="review_comment" rows="6" cols="70"></textarea>
    </label><br>
    <label>
      Upload revised design (optional): <input type="file" name="revised_design" accept="image/*">
    </label><br>
    <label>
      Action:
      <select name="action">
        <option value="request_changes">Request changes</option>
        <option value="approve">Approve design</option>
      </select>
    </label><br><br>
    <button type="submit">Submit</button>
  </form>

  <p><a href="order_confirmation.php?order_id=<?=intval($order_id)?>">Back to receipt</a></p>
</body>
</html>
