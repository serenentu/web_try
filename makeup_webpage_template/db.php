<?php
// db.php - PDO connection: update credentials below
$host = '127.0.0.1:3306';
$user = 'shop_user';
$pass = 'ShopPass123!';
$db = 'shop_db';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Throw exception so calling code can handle it
    throw new Exception("Database connection failed: " . $e->getMessage());
}
?>
