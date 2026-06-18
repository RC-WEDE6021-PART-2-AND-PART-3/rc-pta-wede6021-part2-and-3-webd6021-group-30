<?php
// checkout.php — order confirmation and placement for Pastimes
// Wraps the entire checkout in a database transaction: creates tblOrder,
// inserts tblOrderItem rows, and decrements stock — all or nothing.

$pageTitle = 'Checkout';
require_once 'includes/db.php';
require_once 'includes/session.php';

// Require login and a non-empty cart before proceeding
if (!isLoggedIn()) redirect('login.php?required=1');
$cart = getCart();
if (empty($cart)) redirect('cart.php');

// $orderRef is set only after a successful commit, driving the success view
$orderRef  = '';
$sessionId = session_id();
$err       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $db  = getDB();
    $uid = (int)$_SESSION['user_id'];

    // Fetch user's address for shipping
    $st = $db->prepare("SELECT address FROM tblUser WHERE user_id = ?");
    $st->bind_param('i', $uid);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    $shippingAddr = $row['address'] ?? '';

    $ref        = 'PP-' . strtoupper(substr(md5($uid . microtime()), 0, 8));
    $orderTotal = cartTotal();

    $db->begin_transaction();
    $ok      = true;
    $dbError = '';

    // 1. Create the order record
    $ins = $db->prepare("INSERT INTO tblOrder (user_id,order_ref,total_price,shipping_address,session_id) VALUES (?,?,?,?,?)");
    if (!$ins) {
        $ok = false; $dbError = 'prepare tblOrder: ' . $db->error;
    } else {
        $ins->bind_param('isdss', $uid, $ref, $orderTotal, $shippingAddr, $sessionId);
        if (!$ins->execute()) { $ok = false; $dbError = 'insert tblOrder: ' . $ins->error; }
        $orderId = $db->insert_id;
        $ins->close();
    }

    // 2. Insert each item + reduce stock
    if ($ok) {
        foreach ($cart as $item) {
            $cid       = (int)$item['clothes_id'];
            $qty       = (int)$item['qty'];
            $unitPrice = (float)$item['sell_price'];

            $ins2 = $db->prepare("INSERT INTO tblOrderItem (order_id,clothes_id,quantity,unit_price) VALUES (?,?,?,?)");
            if (!$ins2) { $ok = false; $dbError = 'prepare tblOrderItem: ' . $db->error; break; }
            $ins2->bind_param('iiid', $orderId, $cid, $qty, $unitPrice);
            if (!$ins2->execute()) { $ok = false; $dbError = 'insert tblOrderItem: ' . $ins2->error; $ins2->close(); break; }
            $ins2->close();

            $upd = $db->prepare("UPDATE tblClothes SET stock_qty=GREATEST(stock_qty-?,0) WHERE clothes_id=?");
            if ($upd) { $upd->bind_param('ii', $qty, $cid); $upd->execute(); $upd->close(); }
        }
    }

    // 3. Commit or rollback
    if ($ok) {
        $db->commit();
        $_SESSION['cart'] = [];
        $orderRef = $ref;
    } else {
        $db->rollback();
        $err = 'Checkout failed: ' . $dbError;
    }
    $db->close();
}

$total = cartTotal();
include 'includes/header.php';
?>

<div class="section-sm" style="max-width:720px;margin:0 auto;">

  <?php if ($orderRef): ?>
  
  <div class="text-center" style="padding:3rem 0;">
    <div style="width:4rem;height:4rem;background:#dcfce7;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
      <i data-lucide="check-circle" class="icon" style="width:2rem;height:2rem;color:#16a34a;"></i>
    </div>
    <h1 style="font-size:1.75rem;margin-bottom:.5rem;">Order Confirmed!</h1>
    <p class="text-muted text-sm" style="margin-bottom:2rem;">
      Thank you, <strong><?= authName() ?></strong>. Your order has been placed and our team will be in touch shortly.
    </p>
    
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;display:inline-block;text-align:left;min-width:320px;margin-bottom:2rem;">
      <div style="margin-bottom:1rem;">
        <p style="font-size:.68rem;text-transform:uppercase;letter-spacing:.09em;color:var(--muted);margin-bottom:.25rem;">Order Reference</p>
        <p style="font-size:1.5rem;font-weight:800;letter-spacing:.05em;"><?= htmlspecialchars($orderRef) ?></p>
      </div>
      <div>
        <p style="font-size:.68rem;text-transform:uppercase;letter-spacing:.09em;color:var(--muted);margin-bottom:.25rem;">Session ID</p>
        <p style="font-size:.75rem;font-family:monospace;word-break:break-all;color:var(--muted);"><?= htmlspecialchars($sessionId) ?></p>
      </div>
    </div>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="orders.php" class="btn btn-dark"><i data-lucide="package" class="icon icon-sm"></i> View My Orders</a>
      <a href="shop.php" class="btn btn-outline"><i data-lucide="shopping-bag" class="icon icon-sm"></i> Continue Shopping</a>
    </div>
  </div>

  <?php elseif ($err): ?>
  <div class="alert alert-error"><i data-lucide="alert-circle" class="icon"></i> <?= htmlspecialchars($err) ?></div>
  <a href="cart.php" class="btn btn-outline"><i data-lucide="arrow-left" class="icon icon-sm"></i> Back to Cart</a>

  <?php else: ?>
  
  <h1 style="font-size:1.75rem;margin-bottom:1.5rem;">
    <i data-lucide="credit-card" class="icon icon-lg"></i> Review Your Order
  </h1>
  <div class="alert alert-info">
    <i data-lucide="user-check" class="icon"></i>
    Ordering as <strong><?= authName() ?></strong>
  </div>
  <table class="data-table" style="margin-bottom:1.5rem;">
    <thead><tr><th>Item</th><th>Size</th><th>Qty</th><th>Price</th></tr></thead>
    <tbody>
    <?php foreach ($cart as $item): ?>
    <tr>
      <td>
        <strong><?= htmlspecialchars($item['brand']) ?></strong><br>
        <span class="text-xs text-muted"><?= htmlspecialchars($item['item_name']) ?></span>
      </td>
      <td><?= htmlspecialchars($item['size']) ?></td>
      <td><?= $item['qty'] ?></td>
      <td>R<?= number_format($item['sell_price']*$item['qty'],2) ?></td>
    </tr>
    <?php endforeach ?>
    <tr><td colspan="3"><strong>Total</strong></td><td><strong>R<?= number_format($total,2) ?></strong></td></tr>
    </tbody>
  </table>
  <form method="POST" action="checkout.php">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
      <button type="submit" name="confirm" value="1" class="btn btn-dark">
        <i data-lucide="check" class="icon icon-sm"></i> Confirm Order
      </button>
      <a href="cart.php" class="btn btn-outline">
        <i data-lucide="arrow-left" class="icon icon-sm"></i> Edit Cart
      </a>
    </div>
  </form>
  <?php endif ?>

</div>
<?php include 'includes/footer.php'; ?>
