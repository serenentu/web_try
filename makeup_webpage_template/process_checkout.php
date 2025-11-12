<?php
session_start();

function parse_cart_xml(string $xmlString): array {
    if (trim($xmlString) === '') {
        return [];
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false) {
        libxml_clear_errors();
        return [];
    }

    $items = [];
    foreach ($xml->item as $item) {
        if (!isset($item->id, $item->name, $item->price, $item->quantity)) {
            continue;
        }

        $items[] = [
            'product_id' => intval($item->id),
            'name' => (string)$item->name,
            'price' => floatval($item->price),
            'quantity' => intval($item->quantity),
            'image_path' => isset($item->image) ? (string)$item->image : ''
        ];
    }

    return $items;
}

// Try to connect to database, but continue if it fails
$db_connected = false;
$pdo = null;
try {
    require_once 'db.php';
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    // Continue without database - use session fallback
}

$session_id = session_id();

// get posted form
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$address_line1 = trim($_POST['address_line1'] ?? '');
$address_line2 = trim($_POST['address_line2'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$country = trim($_POST['country'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'card');
$card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
$customer_notes = trim($_POST['customer_notes'] ?? '');

// Server-side validation
$errors = [];
if (!$customer_name) $errors[] = 'Please provide your name.';
if (!$customer_email || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}
if (!$address_line1) $errors[] = 'Please provide your address.';

if ($errors) {
    $_SESSION['cart_error'] = implode(' ', $errors);
    // preserve what user filled for repopulation
    $_SESSION['checkout_prefill'] = $_POST;
    header('Location: checkout.php');
    exit;
}

// Fetch cart items (try from database first, then fallback to localStorage)
$items = [];

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
        error_log("Error fetching cart from database: " . $e->getMessage());
    }
}

// Fallback: Get cart from client_cart_xml (localStorage data from cart.php)
if (empty($items) && !empty($_POST['client_cart_xml'])) {
    $client_cart_items = parse_cart_xml($_POST['client_cart_xml']);
    if (!empty($client_cart_items)) {
        $items = $client_cart_items;
    }
}

if (empty($items)) {
    $_SESSION['cart_error'] = 'Your cart is empty.';
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($items as $it) $total += $it['price'] * $it['quantity'];

// Handle design file upload
$design_path = null;
if (!empty($_FILES['design_file']) && $_FILES['design_file']['error'] === UPLOAD_ERR_OK) {
    $u = $_FILES['design_file'];
    $ext = strtolower(pathinfo($u['name'], PATHINFO_EXTENSION));
    $allowed = ['png','jpg','jpeg','gif','webp'];
    if (in_array($ext, $allowed)) {
        $uploadDir = __DIR__ . '/uploads/designs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = 'design_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($u['tmp_name'], $target)) {
            $design_path = 'uploads/designs/' . $filename;
        }
    }
}

// Mask card last4
$card_last4 = $card_number ? substr($card_number, -4) : null;

// Insert order and items
$order_id = null;
// Define $now before transaction so it's available for email even if DB fails
$now = date('Y-m-d H:i:s');

if ($db_connected && $pdo) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, session_token, total, created_at, customer_name, customer_email, customer_phone, address_line1, address_line2, city, state, postal_code, country, payment_method, payment_last4, design_path, customer_notes, status, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // If you have user auth, replace null with $_SESSION['user_id']
        $stmt->execute([null, $session_id, $total, $now, $customer_name, $customer_email, $customer_phone, $address_line1, $address_line2, $city, $state, $postal_code, $country, $payment_method, $card_last4, $design_path, $customer_notes, 'pending', $now]);
        $order_id = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($items as $it) {
            $subtotal = $it['price'] * $it['quantity'];
            $stmtItem->execute([$order_id, $it['product_id'], $it['name'], $it['price'], $it['quantity'], $subtotal]);
        }

        // Clear cart_items for this session
        $del = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ?");
        $del->execute([$session_id]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo) $pdo->rollBack();
        $db_error_message = "Database order insert error: " . $e->getMessage();
        error_log($db_error_message);
        // Log full error details for debugging
        error_log("Full error details: " . print_r($e, true));
        // Set error but don't immediately fail - we'll check $order_id below
        $db_connected = false;
    }
}

// Fallback: Store order in session/file if database failed
if (!$db_connected || !$order_id) {
    // Log warning that order is being stored in session instead of database
    error_log("WARNING: Order could not be saved to database. Storing in session only. Order will not appear in order history until database is fixed.");
    $order_id = time() . rand(1000, 9999); // Generate temporary order ID
    
    // Store order in session for this session
    $_SESSION['pending_order'] = [
        'order_id' => $order_id,
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'address_line1' => $address_line1,
        'address_line2' => $address_line2,
        'city' => $city,
        'state' => $state,
        'postal_code' => $postal_code,
        'country' => $country,
        'payment_method' => $payment_method,
        'card_last4' => $card_last4,
        'design_path' => $design_path,
        'customer_notes' => $customer_notes,
        'items' => $items,
        'total' => $total,
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];
    
    error_log("Order #{$order_id} created in session (database unavailable)");
}

// Send email to customer with review link
$siteURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$siteDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$reviewLink = $siteURL . $siteDir . "/order_review.php?order_id={$order_id}";
$orderHistoryLink = $siteURL . $siteDir . "/order_history.php?email=" . urlencode($customer_email);

$subject = "Order Confirmation - Order #{$order_id} from BeautyVibe";
$message = "Hello {$customer_name},\n\n";
$message .= "Thank you for your order at BeautyVibe!\n\n";
$message .= "Order Details:\n";
$message .= "Order Number: #{$order_id}\n";
$message .= "Order Date: {$now}\n";
$message .= "Order Total: $" . number_format($total, 2) . "\n\n";
$message .= "Items Ordered:\n";
foreach ($items as $it) {
    $message .= "- " . $it['name'] . " (x" . $it['quantity'] . ") - $" . number_format($it['price'] * $it['quantity'], 2) . "\n";
}
$message .= "\n";

if ($design_path) {
    $message .= "You uploaded a mock-up design for this order.\n\n";
}

$message .= "You can review your order and request changes here:\n";
$message .= $reviewLink . "\n\n";
$message .= "View your order history:\n";
$message .= $orderHistoryLink . "\n\n";
$message .= "If you have any questions, please reply to this email.\n\n";
$message .= "Thank you for shopping with BeautyVibe!\n\n";
$message .= "-- BeautyVibe Team";

$siteOwnerEmail = 'beautyvibe@example.com';
$headers = "From: BeautyVibe <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'example.com') . ">\r\n";
$headers .= "Reply-To: {$siteOwnerEmail}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email to customer
$mail_sent = @mail($customer_email, $subject, $message, $headers);

// Log email status
if ($mail_sent) {
    error_log("Order confirmation email sent to {$customer_email} for order #{$order_id}");
} else {
    error_log("Failed to send order confirmation email to {$customer_email} for order #{$order_id}");
}

// Optionally notify store owner
$admin_message = "New order placed:\n\n";
$admin_message .= "Order #: {$order_id}\n";
$admin_message .= "Customer: {$customer_name} ({$customer_email})\n";
$admin_message .= "Total: $" . number_format($total, 2) . "\n\n";
$admin_message .= "Review order: {$reviewLink}";
@mail($siteOwnerEmail, "New Order #{$order_id} - BeautyVibe", $admin_message, $headers);

// Redirect to order confirmation for regular form submission
$_SESSION['order_success'] = "Order #{$order_id} placed successfully! A confirmation email has been sent to {$customer_email}.";
header("Location: order_confirmation.php?order_id={$order_id}");
exit;
