<?php
session_start();

// Try to connect to database, but continue if it fails
$db_connected = false;
$pdo = null;
try {
    require_once 'db.php';
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database connection failed in checkout.php: " . $e->getMessage());
    // Continue without database - will show error to user
}

$session_id = session_id();
$items = [];

// Fetch cart items from DB if connected
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->prepare("
          SELECT ci.id as cart_id, p.id as product_id, p.name, p.price, p.image_path, ci.quantity, p.stock
          FROM cart_items ci
          JOIN products p ON p.id = ci.product_id
          WHERE ci.session_id = ?
        ");
        $stmt->execute([$session_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching cart from database in checkout.php: " . $e->getMessage());
    }
}

if (empty($items)) {
    if (!$db_connected) {
        $_SESSION['cart_error'] = 'The database is currently unavailable. Please try again later or contact support.';
    } else {
        $_SESSION['cart_error'] = 'Your cart is empty. Add items before checking out.';
    }
    header('Location: products.php');
    exit;
}

$total = 0;
foreach ($items as $it) $total += $it['price'] * $it['quantity'];

// Pre-fill from session if present
$prefill = $_SESSION['checkout_prefill'] ?? [];

function e($s){ return htmlspecialchars($s ?? ''); }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Checkout • BeautyVibe</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
</head>
<body class="checkout-page">
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
        <input type="text" placeholder="Search...">
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

  <main class="checkout-main">
    <a href="cart.php" class="back-btn">&#8592; Back to cart</a>

    <section class="checkout-content container">
      <div class="checkout-columns">
        <aside class="checkout-card checkout-summary">
          <h1>Your Order</h1>
          <div class="checkout-summary-items">
            <?php foreach ($items as $it): ?>
              <div class="checkout-item">
                <div>
                  <p class="checkout-item-name"><?=e($it['name'])?></p>
                  <p class="checkout-item-qty">Qty: <?=intval($it['quantity'])?></p>
                </div>
                <div class="checkout-item-pricing">
                  <span class="checkout-item-price">$<?=number_format($it['price'],2)?></span>
                  <span class="checkout-item-subtotal">$<?=number_format($it['price'] * $it['quantity'],2)?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="checkout-order-total">
            <span>Total</span>
            <span>$<?=number_format($total,2)?></span>
          </div>
        </aside>

        <section class="checkout-card checkout-form">
          <h1>Your Details &amp; Payment</h1>
          <form method="post" action="process_checkout.php" enctype="multipart/form-data" novalidate>
            <div class="form-section">
              <h2>Contact &amp; Shipping</h2>
              <div class="form-grid">
                <label>Full name
                  <input name="customer_name" required value="<?=e($prefill['customer_name'] ?? '')?>">
                </label>
                <label>Email
                  <input name="customer_email" type="email" required value="<?=e($prefill['customer_email'] ?? '')?>">
                </label>
                <label>Phone
                  <input name="customer_phone" value="<?=e($prefill['customer_phone'] ?? '')?>">
                </label>
                <label>Address line 1
                  <input name="address_line1" value="<?=e($prefill['address_line1'] ?? '')?>">
                </label>
                <label>Address line 2
                  <input name="address_line2" value="<?=e($prefill['address_line2'] ?? '')?>">
                </label>
                <div class="form-row">
                  <label>City
                    <input name="city" placeholder="City" value="<?=e($prefill['city'] ?? '')?>">
                  </label>
                  <label>State
                    <input name="state" placeholder="State" value="<?=e($prefill['state'] ?? '')?>">
                  </label>
                  <label>Postal code
                    <input name="postal_code" placeholder="Postal" value="<?=e($prefill['postal_code'] ?? '')?>">
                  </label>
                  <label>Country
                    <input name="country" placeholder="Country" value="<?=e($prefill['country'] ?? '')?>">
                  </label>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h2>Payment (demo)</h2>
              <div class="form-grid two-column">
                <label>Payment method
                  <select name="payment_method">
                    <option value="card">Card</option>
                    <option value="bank">Bank transfer</option>
                    <option value="cod">Pay on delivery</option>
                  </select>
                </label>
                <label>Card number (demo only) — we'll only keep last 4 digits
                  <input name="card_number" placeholder="4242 4242 4242 4242">
                </label>
              </div>
            </div>

            <div class="form-section">
              <h2>Mock-up design &amp; changes</h2>
              <p class="section-help">If you have a mock-up design image (jpg/png), upload it here. After order placement you'll get a review link to approve or request final changes.</p>
              <label class="file-label">Upload mock-up (optional)
                <input type="file" name="design_file" accept="image/*">
              </label>
              <label>Your notes / final change requests (optional)
                <textarea name="customer_notes" rows="4"><?=e($prefill['customer_notes'] ?? '')?></textarea>
              </label>
            </div>

            <div class="form-actions">
              <button type="submit" class="checkout-submit">Place order and send confirmation email</button>
            </div>
          </form>
        </section>
      </div>
    </section>
  </main>

  <footer class="footer">
    <p>&copy; 2025 BeautyVibe. All rights reserved.</p>
  </footer>
</body>
</html>
