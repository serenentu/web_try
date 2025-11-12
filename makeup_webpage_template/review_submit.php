<?php
session_start();
require_once 'db.php';

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$action = $_POST['action'] ?? 'request_changes';
$review_comment = trim($_POST['review_comment'] ?? '');

if (!$order_id) { header('Location: products.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { echo "Order not found."; exit; }

// Handle revised design upload
$revised_path = null;
if (!empty($_FILES['revised_design']) && $_FILES['revised_design']['error'] === UPLOAD_ERR_OK) {
    $u = $_FILES['revised_design'];
    $ext = strtolower(pathinfo($u['name'], PATHINFO_EXTENSION));
    $allowed = ['png','jpg','jpeg','gif','webp'];
    if (in_array($ext, $allowed)) {
        $uploadDir = __DIR__ . '/uploads/designs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = 'design_rev_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($u['tmp_name'], $target)) {
            $revised_path = 'uploads/designs/' . $filename;
        }
    }
}

// Update order status and notes
$new_status = $action === 'approve' ? 'approved' : 'change_requested';
$now = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();
    $updateStmt = $pdo->prepare("UPDATE orders SET status = ?, customer_notes = CONCAT(COALESCE(customer_notes,''), ?, ?), design_path = COALESCE(?, design_path), updated_at = ? WHERE id = ?");
    // append review_comment with timestamp
    $append = "\n\n[Review " . $now . "] " . ($review_comment ?: ($action === 'approve' ? 'Approved' : 'Requested changes'));
    $updateStmt->execute([$new_status, $append, '', $revised_path, $now, $order_id]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Review submit error: " . $e->getMessage());
    $_SESSION['cart_error'] = 'Failed to save review. Try again.';
    header("Location: order_review.php?order_id={$order_id}");
    exit;
}

// Email notification: send to shop owner and customer
$siteOwnerEmail = 'store@example.com'; // change to your shop email
$subject_customer = "Update on your order #{$order_id}";
$reviewLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/order_review.php?order_id={$order_id}";
$msg_customer = "Hello {$order['customer_name']},\n\nYour review has been received. Status: {$new_status}\n\nYou can view the order here: {$reviewLink}\n\nThanks.";
$headers = "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'example.com') . "\r\n";
@mail($order['customer_email'], $subject_customer, $msg_customer, $headers);

// Notify shop
@mail($siteOwnerEmail, "Order #{$order_id} updated by customer", "Order #{$order_id} has a new review: {$reviewLink}", $headers);

header("Location: order_review.php?order_id={$order_id}&updated=1");
exit;
