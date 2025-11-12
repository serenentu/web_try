<?php
// test_db_connection.php - Test database credentials
// Access via: http://localhost:8000/test_db_connection.php
// Or run from terminal: php test_db_connection.php

echo "========================================\n";
echo "Database Connection Test\n";
echo "========================================\n\n";

// Check if db.php exists
if (!file_exists('db.php')) {
    die("❌ ERROR: db.php file not found!\n");
}

echo "✅ db.php file found\n\n";

// Read and display credentials
require_once 'db.php';

// Try to access the variables (they're set in db.php)
$host = isset($host) ? $host : 'NOT SET';
$user = isset($user) ? $user : 'NOT SET';
$db = isset($db) ? $db : 'NOT SET';
$pass = isset($pass) ? str_repeat('*', strlen($pass)) : 'NOT SET';

echo "Credentials from db.php:\n";
echo "  Host: $host\n";
echo "  Username: $user\n";
echo "  Password: " . (isset($pass) ? str_repeat('*', 10) : 'NOT SET') . "\n";
echo "  Database: $db\n\n";

// Test connection
echo "Testing connection...\n";

try {
    // If $pdo exists from db.php, test it
    if (isset($pdo)) {
        // Test query
        $stmt = $pdo->query("SELECT DATABASE() as db_name, USER() as db_user");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ CONNECTION SUCCESSFUL!\n\n";
        echo "Connected to:\n";
        echo "  Database: " . $result['db_name'] . "\n";
        echo "  User: " . $result['db_user'] . "\n\n";
        
        // Test if we can query tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Available tables (" . count($tables) . "):\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
        
        // Test product count
        if (in_array('products', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "\n✅ Products table has " . $count['count'] . " products\n";
        }
        
        // Test orders count
        if (in_array('orders', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Orders table has " . $count['count'] . " orders\n";
        }
        
        // Test cart items count
        if (in_array('cart_items', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM cart_items");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Cart items table has " . $count['count'] . " items\n";
        }
        
    } else {
        echo "❌ ERROR: PDO connection object not created\n";
    }
    
} catch (Exception $e) {
    echo "❌ CONNECTION FAILED!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "1. Check if MySQL is running\n";
    echo "2. Verify the credentials in db.php match your database\n";
    echo "3. Make sure the database and user exist\n";
    echo "4. Run: mysql -u $user -p$db to test manually\n";
    exit(1);
}

echo "\n========================================\n";
echo "✅ All tests passed! Database is ready.\n";
echo "========================================\n";
