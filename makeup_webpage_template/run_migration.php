<?php
// Run migration via existing db.php PDO connection. Run from project root:
// php makeup_webpage_template/run_migration.php
// or visit https://your-site/makeup_webpage_template/run_migration.php (not recommended on public sites).

// security: only run from CLI by default
if (php_sapi_name() !== 'cli') {
    echo "Non-CLI execution detected. To run from browser, remove this guard intentionally.\n";
    // comment out the exit below if you intentionally want web-run
    exit;
}

require_once __DIR__ . '/db.php'; // adjust path if your db.php is located elsewhere

$sqlFile = __DIR__ . '/migrations/2025-11-02_add_checkout_fields.sql';
if (!file_exists($sqlFile)) {
    echo "Migration file not found: {$sqlFile}\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read migration file.\n";
    exit(1);
}

try {
    // Execute all statements in SQL file
    $pdo->exec($sql);
    echo "Migration executed successfully.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
