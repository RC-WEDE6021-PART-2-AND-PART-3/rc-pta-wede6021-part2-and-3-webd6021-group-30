<?php
// db.php — database connection helper for Pastimes
// Provides getDB() which returns a connected mysqli instance,
// auto-redirecting to setup.php if the database or core tables are missing.

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ClothingStore');

// Returns a connected and charset-configured mysqli connection.
// Redirects to setup.php if the ClothingStore database or tblClothes is missing.
function getDB(): mysqli {

    // Step 1: verify MySQL server is reachable
    $test = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($test->connect_error) {
        die('
        <div style="font-family:sans-serif;padding:3rem;max-width:560px;margin:4rem auto;background:#fff;border-radius:1rem;box-shadow:0 8px 32px rgba(0,0,0,.12);">
            <h2 style="color:#b91c1c;margin-bottom:.75rem;">MySQL Connection Failed</h2>
            <p style="color:#666;margin-bottom:1rem;">Cannot connect to MySQL. Make sure <strong>MySQL is running</strong> in XAMPP Control Panel.</p>
            <p style="font-size:.85rem;color:#999;">Error: ' . htmlspecialchars($test->connect_error) . '</p>
        </div>');
    }
    $test->close();

    // Step 2: connect to the ClothingStore database
    $c = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // If the database does not exist yet, redirect to setup.php
    if ($c->connect_error) {
        $setupUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/setup.php';
        $setupUrl = str_replace('\\', '/', $setupUrl);
        header('Location: ' . $setupUrl);
        exit;
    }

    // Use UTF-8 for all queries and results
    $c->set_charset('utf8mb4');

    // Step 3: ensure core tables exist; if not, redirect to setup.php
    $check = $c->query("SHOW TABLES LIKE 'tblClothes'");
    if (!$check || $check->num_rows === 0) {
        $c->close();
        $setupUrl = str_replace(basename($_SERVER['PHP_SELF']), 'setup.php', $_SERVER['PHP_SELF']);
        header('Location: ' . $setupUrl);
        exit;
    }

    // Step 4: auto-create order tables if they were added after the initial setup
    $c->query("CREATE TABLE IF NOT EXISTS tblOrder (
        order_id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id          INT NOT NULL,
        order_ref        VARCHAR(20) NOT NULL,
        total_price      DECIMAL(10,2) NOT NULL,
        status           ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending',
        payment_status   ENUM('pending','paid') NOT NULL DEFAULT 'pending',
        shipping_address VARCHAR(255) DEFAULT NULL,
        session_id       VARCHAR(120) DEFAULT NULL,
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_tblorder_user FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $c->query("CREATE TABLE IF NOT EXISTS tblOrderItem (
        item_id    INT AUTO_INCREMENT PRIMARY KEY,
        order_id   INT NOT NULL,
        clothes_id INT DEFAULT NULL,
        quantity   INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL,
        CONSTRAINT fk_tblorderitem_order   FOREIGN KEY (order_id)   REFERENCES tblOrder(order_id)    ON DELETE CASCADE,
        CONSTRAINT fk_tblorderitem_clothes FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    return $c;
}

// Runs a raw SQL query and logs any errors; returns the result or false.
function dbQuery(mysqli $db, string $sql): mysqli_result|bool {
    $result = $db->query($sql);
    if ($result === false) {
        error_log('Pastimes DB Error: ' . $db->error . ' | SQL: ' . $sql);
        return false;
    }
    return $result;
}

// Executes a SELECT query and returns all rows as an associative array.
function fetchAll(mysqli $db, string $sql): array {
    $rows = [];
    $res  = dbQuery($db, $sql);
    if ($res && $res !== true) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    return $rows;
}
