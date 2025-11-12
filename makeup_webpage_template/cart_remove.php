<?php
// cart_remove.php?id=<cart_id>
session_start();
require_once 'db.php';

$cart_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$session_id = session_id();

if ($cart_id <= 0) {
    $_SESSION['cart_error'] = "Invalid item.";
    header('Location: cart.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
$stmt->execute([$cart_id, $session_id]);

$_SESSION['cart_success'] = "Item removed from cart.";
header('Location: cart.php');
exit;
?>
