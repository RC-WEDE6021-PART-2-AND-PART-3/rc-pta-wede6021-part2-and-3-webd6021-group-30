<?php
// orders.php — purchase history for the logged-in user
// Joins tblOrder + tblOrderItem + tblClothes to build a grouped list of
// order cards, each with a line-item table and shipping address.

$pageTitle = 'My Orders';
require_once 'includes/db.php';
require_once 'includes/session.php';

if (!isLoggedIn()) redirect('login.php?required=1');

$db  = getDB();
$uid = (int)$_SESSION['user_id'];

// $orders is keyed by order_id; rows are grouped in PHP after the JOIN query
$orders     = [];
$grandTotal = 0.0;

$st = $db->prepare("
    SELECT o.order_id, o.order_ref, o.total_price AS order_total,
           o.status, o.payment_status, o.shipping_address, o.created_at,
           oi.quantity, oi.unit_price,
           c.brand, c.item_name, c.size, c.condition_, c.image_file
    FROM tblOrder o
    JOIN tblOrderItem oi ON o.order_id = oi.order_id
    LEFT JOIN tblClothes c ON oi.clothes_id = c.clothes_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC, o.order_id DESC, oi.item_id ASC
");
if ($st) {
    $st->bind_param('i', $uid);
    $st->execute();
    $res = $st->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $oid = $row['order_id'];
            if (!isset($orders[$oid])) {
                $orders[$oid] = [
                    'order_id'        => $oid,
                    'order_ref'       => $row['order_ref'],
                    'order_total'     => (float)$row['order_total'],
                    'status'          => $row['status'],
                    'payment_status'  => $row['payment_status'],
                    'shipping_address'=> $row['shipping_address'],
                    'created_at'      => $row['created_at'],
                    'items'           => [],
                ];
                $grandTotal += (float)$row['order_total'];
            }
            $orders[$oid]['items'][] = $row;
        }
    }
    $st->close();
}
$db->close();

include 'includes/header.php';
?>

<section class="section-sm" style="max-width:900px;margin:0 auto;">
  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <div>
      <p class="eyebrow">Account</p>
      <h1 style="font-size:1.75rem;margin-top:.25rem;">
        <i data-lucide="package" class="icon icon-lg"></i> Purchase History
      </h1>
    </div>
    <a href="shop.php" class="btn btn-outline btn-sm" style="margin-left:auto;">
      <i data-lucide="shopping-bag" class="icon icon-sm"></i> Continue Shopping
    </a>
  </div>

  <div class="alert alert-info" style="margin-bottom:1.5rem;">
    <i data-lucide="user-check" class="icon"></i>
    History for <strong><?= authName() ?></strong>
  </div>

  <?php if (empty($orders)): ?>
  <div class="text-center" style="padding:4rem 0;">
    <i data-lucide="package" class="icon" style="width:3.5rem;height:3.5rem;opacity:.2;margin-bottom:1.25rem;"></i>
    <p style="font-size:1.1rem;color:var(--muted);margin-bottom:1.5rem;">No orders yet.</p>
    <a href="shop.php" class="btn btn-dark">
      <i data-lucide="shopping-bag" class="icon icon-sm"></i> Shop now
    </a>
  </div>

  <?php else: ?>

  <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.75rem;">
    <div style="padding:1rem 1.5rem;border:1px solid var(--border);border-radius:var(--radius);min-width:140px;">
      <p style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Total orders</p>
      <p style="font-size:1.75rem;font-weight:700;"><?= count($orders) ?></p>
    </div>
    <div style="padding:1rem 1.5rem;border:1px solid var(--border);border-radius:var(--radius);min-width:180px;">
      <p style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Total spent</p>
      <p style="font-size:1.75rem;font-weight:700;">R<?= number_format($grandTotal, 2) ?></p>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:1.25rem;">
  <?php foreach ($orders as $o):
    $statusColor = match($o['status']) {
      'paid'      => '#16a34a',
      'shipped'   => '#2563eb',
      'cancelled' => '#dc2626',
      default     => '#92400e',
    };
  ?>
  <div style="border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">

    <!-- Order header -->
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;padding:.875rem 1rem;background:var(--surface);border-bottom:1px solid var(--border);">
      <div>
        <span style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);">Order</span>
        <strong style="margin-left:.4rem;"><?= htmlspecialchars($o['order_ref']) ?></strong>
      </div>
      <span style="color:var(--muted);font-size:.8rem;"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
      <span style="margin-left:auto;font-size:.75rem;font-weight:600;padding:.2rem .6rem;border-radius:9999px;background:<?= $statusColor ?>22;color:<?= $statusColor ?>;">
        <?= ucfirst($o['status']) ?>
      </span>
      <span style="font-weight:700;">R<?= number_format($o['order_total'], 2) ?></span>
    </div>

    <!-- Items table -->
    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
      <thead style="background:var(--surface);">
        <tr>
          <th style="padding:.6rem 1rem;text-align:left;font-weight:600;">Item</th>
          <th style="padding:.6rem 1rem;text-align:left;font-weight:600;">Size</th>
          <th style="padding:.6rem 1rem;text-align:left;font-weight:600;">Condition</th>
          <th style="padding:.6rem 1rem;text-align:center;font-weight:600;">Qty</th>
          <th style="padding:.6rem 1rem;text-align:right;font-weight:600;">Unit</th>
          <th style="padding:.6rem 1rem;text-align:right;font-weight:600;">Line total</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($o['items'] as $item):
        $condColor = match($item['condition_'] ?? '') {
          'Excellent' => '#16a34a',
          'Very Good' => '#2563eb',
          default     => '#92400e',
        };
      ?>
      <tr style="border-top:1px solid var(--border);">
        <td style="padding:.65rem 1rem;">
          <div style="display:flex;align-items:center;gap:.65rem;">
            <?php if ($item['image_file']): ?>
            <img src="images/<?= htmlspecialchars($item['image_file']) ?>"
                 style="width:40px;height:40px;object-fit:cover;border-radius:.35rem;" alt="">
            <?php endif ?>
            <div>
              <p style="font-weight:600;"><?= htmlspecialchars($item['brand'] ?? '—') ?></p>
              <p style="font-size:.75rem;opacity:.6;"><?= htmlspecialchars($item['item_name'] ?? '—') ?></p>
            </div>
          </div>
        </td>
        <td style="padding:.65rem 1rem;"><?= htmlspecialchars($item['size'] ?? '—') ?></td>
        <td style="padding:.65rem 1rem;">
          <span style="color:<?= $condColor ?>;font-size:.78rem;font-weight:600;"><?= htmlspecialchars($item['condition_'] ?? '—') ?></span>
        </td>
        <td style="padding:.65rem 1rem;text-align:center;"><?= (int)$item['quantity'] ?></td>
        <td style="padding:.65rem 1rem;text-align:right;">R<?= number_format($item['unit_price'], 2) ?></td>
        <td style="padding:.65rem 1rem;text-align:right;font-weight:600;">
          R<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
        </td>
      </tr>
      <?php endforeach ?>
      </tbody>
    </table>

    <?php if ($o['shipping_address']): ?>
    <div style="padding:.6rem 1rem;font-size:.78rem;color:var(--muted);border-top:1px solid var(--border);">
      <i data-lucide="map-pin" class="icon icon-sm"></i>
      Shipping to: <?= htmlspecialchars($o['shipping_address']) ?>
    </div>
    <?php endif ?>
  </div>
  <?php endforeach ?>
  </div>

  <div style="text-align:right;padding:1rem 0;font-size:.9rem;">
    <strong>Grand total across all orders: R<?= number_format($grandTotal, 2) ?></strong>
  </div>
  <?php endif ?>
</section>

<?php include 'includes/footer.php'; ?>
