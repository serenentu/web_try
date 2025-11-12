<?php
// product.php - detailed product page with Add to Cart form
session_start();
require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    echo "Product not found.";
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?=htmlspecialchars($product['name'])?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <p><a href="products.php">Back to products</a> | <a href="cart.php">Cart</a></p>

  <h1><?=htmlspecialchars($product['name'])?></h1>
  <?php if (!empty($product['image_path'])): ?>
    <img src="<?=htmlspecialchars($product['image_path'])?>" alt="<?=htmlspecialchars($product['name'])?>" class="product-image-large">
  <?php endif; ?>
  <p><?=nl2br(htmlspecialchars($product['description']))?></p>
  <p>Price: $<?=number_format($product['price'],2)?></p>
  <p>Stock: <?=intval($product['stock'])?></p>

  <form method="post" action="add_to_cart.php">
    <input type="hidden" name="product_id" value="<?=intval($product['id'])?>">
    <label>Quantity: <input type="number" name="quantity" value="1" min="1" max="<?=intval($product['stock'])?>"></label>
    <button type="submit">Add to cart</button>
  </form>
</body>
</html>
