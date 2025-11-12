<?php
// cart.php - shopping cart with database integration
session_start();

// Try to connect to database
$db_connected = false;
$pdo = null;
try {
    require_once 'db.php';
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database connection failed in cart: " . $e->getMessage());
}

$session_id = session_id();
$items = [];
$total = 0;
$itemCount = 0;

// Fetch cart items with product details
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
        
        // Calculate totals
        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
            $itemCount += $item['quantity'];
        }
    } catch (Exception $e) {
        error_log("Error fetching cart items: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - BeautyVibe</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Only styles unique to cart page go here */
        .cart-title {
            font-size: 2em;
            color: #da8277dc;
            font-weight: 700;
            margin-bottom: 26px;
            text-align: center;
            margin-top: 30px;
        }
        .cart-items { margin-bottom: 36px; }
        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 18px 0;
            margin: 2px 90px;
            
        }
        .cart-img { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; }
        .cart-details { flex: 1; }
        .cart-name { font-size: 1.08em; font-weight: 500; color: black; }
        .cart-price, .cart-subtotal { font-size: 1em; color: #da8277dc; margin-top: 2px; }
        .cart-qty-input { width: 64px; padding: 6px; font-size: 1em; border-radius: 6px; text-align:center; }
        .remove-btn { background: white; color: #da8277dc; border: 2px solid #ffd2bc; border-radius: 7px; padding: 8px 12px; cursor:pointer; text-decoration: none; display: inline-block; }
        .cart-summary { display:flex; justify-content:flex-end; gap: 12px; align-items:center; padding-top:12px; }
        .checkout-btn { background:#ffd2bc; color:white; padding:12px;  border:none; border-radius:8px; cursor:pointer; text-decoration: none; display: inline-block; }
        .empty { text-align:center; color:#888; font-size:1.1em; padding:30px 0; }
        .update-btn { background:#f8b500; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
        .cart-item-right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; min-width: 200px; }
        .cart-qty-wrapper { display: flex; align-items: center; gap: 8px; }
        .cart-subtotal { margin: 0; text-align: right; }
        .remove-btn { margin: 0; }
        @media (max-width:600px) {
        .cart-item { flex-direction: column; align-items:flex-start; }
        }

        .back-btn {
            color: #da8277dc;
            background: white;
            border: 2px solid #ffd2bc;
            border-radius: 7px;
            padding: 10px 10px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            margin: 18px 30px 1px;
            transition: background 0.2s, color 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background: #f8b500;
            color: #da8277dc;
        }

        /* checkout modal styles (added) */
        .checkout-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 2000;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.5);
        }
        .checkout-modal .modal-card {
            background: #fff;
            width: 94%;
            max-width: 700px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
        }
        .checkout-modal input[type="text"],
        .checkout-modal input[type="email"],
        .checkout-modal input[type="number"],
        .checkout-modal textarea,
        .checkout-modal select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        .checkout-actions {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-top:12px;
        }
        .checkout-actions .btn {
            padding:10px 16px;
            border-radius:6px;
            cursor:pointer;
            border: none;
        }
        .btn-cancel { background:#e0e0e0; color:#222; }
        .btn-submit { background:#c44569; color:white; }
        .checkout-error { color:#900; margin-top:8px; display:none; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
        .message { padding: 12px; margin: 12px 90px; border-radius: 6px; }
        .message.error { background: #ffebee; color: #c62828; border: 1px solid #ef5350; }
        .message.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #66bb6a; }
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
<main>
        <a href="index.html" class="back-btn">&#8592; Back</a>
        <div class="cart-title">Your Cart</div>
        
        <?php if (!empty($_SESSION['cart_error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['cart_error']); ?></div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['cart_success'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['cart_success']); ?></div>
            <?php unset($_SESSION['cart_success']); ?>
        <?php endif; ?>
        
        <div class="cart-items" id="cartItems">
            <?php if (empty($items)): ?>
                <div class="empty">Your cart is empty.</div>
            <?php else: ?>
                <form method="post" action="cart_update.php">
                    <?php foreach ($items as $item): 
                        $price = floatval($item['price']);
                        $qty = intval($item['quantity']);
                        $subtotal = $price * $qty;
                        $imgSrc = !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : '';
                    ?>
                        <div class="cart-item" data-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                            <img src="<?php echo $imgSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="cart-img" 
                                 onerror="this.style.visibility='hidden'">
                            <div class="cart-details">
                                <div class="cart-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-price">Price: $<?php echo number_format($price, 2); ?></div>
                            </div>
                            <div class="cart-item-right">
                                <div class="cart-qty-wrapper">
                                    <label class="sr-only" for="qty-<?php echo htmlspecialchars($item['cart_id']); ?>">
                                        Quantity for <?php echo htmlspecialchars($item['name']); ?>
                                    </label>
                                    <input id="qty-<?php echo htmlspecialchars($item['cart_id']); ?>" 
                                           class="cart-qty-input" 
                                           type="number" 
                                           min="1" 
                                           max="<?php echo intval($item['stock']); ?>"
                                           name="quantity[<?php echo intval($item['cart_id']); ?>]"
                                           value="<?php echo $qty; ?>">
                                    <button type="submit" class="update-btn">Update</button>
                                </div>
                                <div class="cart-subtotal">Subtotal: $<?php echo number_format($subtotal, 2); ?></div>
                                <a href="cart_remove.php?id=<?php echo intval($item['cart_id']); ?>" 
                                   class="remove-btn">Remove</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="cart-summary" id="cartSummary">
            <?php if (!empty($items)): ?>
                <strong>Total:</strong> $<?php echo number_format($total, 2); ?> &nbsp; 
                <span>Items: <?php echo $itemCount; ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($items)): ?>
            <a href="checkout.php" class="checkout-btn" aria-label="Checkout">Checkout</a>
        <?php endif; ?>
</main>

<!-- No JavaScript needed - all functionality handled server-side via forms and links -->

</body>
</html>
