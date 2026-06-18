<?php
// ajax/variant_stock.php — return JSON stock info for a clothes variant
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error' => 'invalid_id']);
    exit;
}

$db = getDB();
$st = $db->prepare('SELECT clothes_id, size, stock_qty, sell_price FROM tblClothes WHERE clothes_id = ? LIMIT 1');
if (!$st) {
    echo json_encode(['error' => 'db_prepare_failed']);
    exit;
}
$st->bind_param('i', $id);
$st->execute();
$res = $st->get_result();
$row = $res ? $res->fetch_assoc() : null;
$st->close();
$db->close();

if (!$row) {
    echo json_encode(['error' => 'not_found']);
    exit;
}

echo json_encode([
    'clothes_id' => (int)$row['clothes_id'],
    'size'       => $row['size'],
    'stock_qty'  => (int)$row['stock_qty'],
    'sell_price' => (float)$row['sell_price'],
]);

exit;
