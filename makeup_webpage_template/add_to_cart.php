<?php
// add_to_cart.php - add product to session cart
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$session_id = session_id();

$errors = [];
if ($product_id <= 0) $errors[] = "Invalid product.";
if ($quantity <= 0) $errors[] = "Quantity must be at least 1.";

if ($errors) {
    $_SESSION['cart_error'] = implode(' ', $errors);
    header('Location: product.php?id=' . $product_id);
    exit;
}

// Check product exists and stock
$stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    $_SESSION['cart_error'] = "Product not found.";
    header('Location: products.php');
    exit;
}
if ($quantity > $product['stock']) {
    $_SESSION['cart_error'] = "Requested quantity exceeds stock.";
    header('Location: product.php?id=' . $product_id);
    exit;
}

// Insert or update cart_items
$stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ?");
$stmt->execute([$session_id, $product_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $newQty = $existing['quantity'] + $quantity;
    $update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $update->execute([$newQty, $existing['id']]);
} else {
    $insert = $pdo->prepare("INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->execute([$session_id, $product_id, $quantity]);
}

$_SESSION['cart_success'] = "Item added to cart.";
header('Location: cart.php');
exit;
?>
