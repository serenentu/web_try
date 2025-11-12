<?php
// cart_update.php - update quantities in the cart
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

// Expected POST: quantity[<cart_id>] => value
$quantities = $_POST['quantity'] ?? [];
$session_id = session_id();

$errors = [];
$pdo->beginTransaction();
try {
    $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND session_id = ?");
    foreach ($quantities as $cart_id_str => $qty_str) {
        $cart_id = intval($cart_id_str);
        $qty = intval($qty_str);
        if ($cart_id <= 0) continue;
        if ($qty <= 0) {
            // treat as remove
            $del = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
            $del->execute([$cart_id, $session_id]);
            continue;
        }
        // check stock
        $stmt = $pdo->prepare("SELECT p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.session_id = ?");
        $stmt->execute([$cart_id, $session_id]);
        $row = $stmt->fetch();
        if (!$row) continue;
        if ($qty > (int)$row['stock']) {
            $errors[] = "Requested quantity for an item exceeds stock and was clamped.";
            $qty = (int)$row['stock'];
        }
        $updateStmt->execute([$qty, $cart_id, $session_id]);
    }
    $pdo->commit();
    if ($errors) {
        $_SESSION['cart_error'] = implode(' ', $errors);
    } else {
        $_SESSION['cart_success'] = "Cart updated.";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['cart_error'] = "Failed to update cart: " . $e->getMessage();
}

header('Location: cart.php');
exit;
?>
