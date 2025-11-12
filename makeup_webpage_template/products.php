<?php
// products.php - simple product list
session_start();
require_once 'db.php';

// Fetch products
$stmt = $pdo->query("SELECT id, name, price, stock, image_path, category FROM products ORDER BY id ASC");
$products = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Products</h1>
  <p><a href="cart.php">View Cart</a> | <a href="orders.php">Order History</a></p>

  <?php if (count($products) === 0): ?>
    <p>No products in the catalog. Insert some via DB or import sample data.</p>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $p): ?>
        <div class="product-card">
          <?php if (!empty($p['image_path'])): ?>
            <img src="<?=htmlspecialchars($p['image_path'])?>" alt="<?=htmlspecialchars($p['name'])?>" class="product-image">
          <?php endif; ?>
          <h3><?=htmlspecialchars($p['name'])?></h3>
          <p>Price: $<?=number_format($p['price'],2)?></p>
          <p>Stock: <?=intval($p['stock'])?></p>
          <p><a href="product.php?id=<?=intval($p['id'])?>">View / Add to cart</a></p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</body>
</html>
