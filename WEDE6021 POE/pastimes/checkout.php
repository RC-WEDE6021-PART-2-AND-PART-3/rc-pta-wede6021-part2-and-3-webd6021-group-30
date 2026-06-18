<?php
// checkout.php — Checkout with payment method selection for Pastimes
// Student:  Vukosi Rikhotso       Student No: ST10439408
// Partner:  Theo Golele           Student No: ST10439863
// Group:    Code Couture (Group 02)   Module: WEDE6021
// Institution: IIE Rosebank College, Pretoria
// Declaration: Own original work except where referenced inline.

$pageTitle = 'Checkout';
require_once 'includes/db.php';
require_once 'includes/session.php';
require_once 'classes/ShoppingCart.php';

$cart = new ShoppingCart();

if (!$cart->IsLoggedIn()) {
    redirect('login.php?required=1');
}
if ($cart->IsEmpty()) {
    redirect('cart.php');
}

$orderRef  = '';
$sessionId = session_id();
$err       = '';
$step      = (int)($_POST['step'] ?? $_GET['step'] ?? 1);

// ── Step 2 POST: place order ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $payMethod = trim($_POST['pay_method'] ?? '');
    $cardName  = trim($_POST['card_name']  ?? '');
    $cardNum   = preg_replace('/\D/', '', $_POST['card_number']  ?? '');
    $cardExp   = trim($_POST['card_expiry'] ?? '');
    $cardCvv   = trim($_POST['card_cvv']   ?? '');
    $eftRef    = trim($_POST['eft_ref']    ?? '');

    // Validate
    if (!in_array($payMethod, ['card', 'eft', 'cod'])) {
        $err  = 'Please select a payment method.';
        $step = 2;
    } elseif ($payMethod === 'card') {
        if (!$cardName)                          { $err = 'Cardholder name is required.';          $step = 2; }
        elseif (strlen($cardNum) < 13)           { $err = 'Please enter a valid card number.';     $step = 2; }
        elseif (!preg_match('/^\d{2}\/\d{2}$/', $cardExp)) { $err = 'Expiry must be MM/YY.';       $step = 2; }
        elseif (!preg_match('/^\d{3,4}$/', $cardCvv))      { $err = 'CVV must be 3–4 digits.';     $step = 2; }
    } elseif ($payMethod === 'eft' && !$eftRef) {
        $err  = 'Please enter your EFT reference number.';
        $step = 2;
    }

    if (!$err) {
        $db     = getDB();

        // Add payment_method column if it doesn't exist yet (safe ALTER)
        $db->query("ALTER TABLE tblOrder ADD COLUMN IF NOT EXISTS
                    payment_method VARCHAR(20) NOT NULL DEFAULT 'cod'");

        $result = $cart->Checkout($db, $payMethod);
        $db->close();

        if ($result['success']) {
            $orderRef  = $result['ref'];
            $sessionId = $result['sessionId'];
        } else {
            $err  = $result['error'];
            $step = 2;
        }
    }
}

$total = $cart->GetTotal();
$items = $cart->GetItems();
include 'includes/header.php';
?>

<div class="section-sm" style="max-width:760px;margin:0 auto;">

<?php if ($orderRef): ?>
<!-- ═══ SUCCESS ═══════════════════════════════════════════════════════════════ -->
<div class="text-center" style="padding:3rem 0;">
  <div style="width:4.5rem;height:4.5rem;background:#dcfce7;border-radius:9999px;
              display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
    <i data-lucide="check-circle" class="icon" style="width:2.25rem;height:2.25rem;color:#16a34a;"></i>
  </div>
  <h1 style="font-size:1.85rem;margin-bottom:.5rem;">Order Confirmed!</h1>
  <p class="text-muted text-sm" style="margin-bottom:2rem;">
    Thank you, <strong><?= authName() ?></strong>. Your order is placed and our team will be in touch shortly.
  </p>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
              padding:1.75rem;display:inline-block;text-align:left;min-width:340px;margin-bottom:2rem;">
    <div style="margin-bottom:1.1rem;">
      <p style="font-size:.68rem;text-transform:uppercase;letter-spacing:.09em;color:var(--muted);margin-bottom:.25rem;">Order Reference</p>
      <p style="font-size:1.6rem;font-weight:800;letter-spacing:.05em;"><?= htmlspecialchars($orderRef) ?></p>
    </div>
    <div>
      <p style="font-size:.68rem;text-transform:uppercase;letter-spacing:.09em;color:var(--muted);margin-bottom:.25rem;">Session ID</p>
      <p style="font-size:.75rem;font-family:monospace;word-break:break-all;color:var(--muted);"><?= htmlspecialchars($sessionId) ?></p>
    </div>
  </div>

  <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
    <a href="orders.php"  class="btn btn-dark"><i data-lucide="package" class="icon icon-sm"></i> View My Orders</a>
    <a href="shop.php"    class="btn btn-outline"><i data-lucide="shopping-bag" class="icon icon-sm"></i> Continue Shopping</a>
  </div>
</div>

<?php elseif ($err && $step !== 2): ?>
<!-- ═══ GENERIC ERROR ═══════════════════════════════════════════════════════ -->
<div class="alert alert-error"><i data-lucide="alert-circle" class="icon"></i> <?= htmlspecialchars($err) ?></div>
<a href="cart.php" class="btn btn-outline" style="margin-top:1rem;">
  <i data-lucide="arrow-left" class="icon icon-sm"></i> Back to Cart
</a>

<?php else: ?>
<!-- ═══ CHECKOUT FORM (step 1 = review, step 2 = payment) ══════════════════ -->

<!-- Progress bar -->
<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:2rem;">
  <?php foreach([1=>'Review Cart', 2=>'Payment', 3=>'Confirm'] as $s => $lbl): ?>
  <div style="display:flex;align-items:center;gap:.35rem;flex:1;">
    <div style="width:1.75rem;height:1.75rem;border-radius:9999px;display:flex;align-items:center;
                justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;
                background:<?= $step >= $s ? 'var(--accent)' : 'var(--surface)' ?>;
                color:<?= $step >= $s ? '#fff' : 'var(--muted)' ?>;
                border:1.5px solid <?= $step >= $s ? 'var(--accent)' : 'var(--border)' ?>;">
      <?= $step > $s ? '✓' : $s ?>
    </div>
    <span style="font-size:.78rem;font-weight:<?= $step === $s ? '600' : '400' ?>;
                 color:<?= $step === $s ? 'var(--text)' : 'var(--muted)' ?>;"><?= $lbl ?></span>
    <?php if ($s < 3): ?><div style="flex:1;height:1.5px;background:var(--border);"></div><?php endif ?>
  </div>
  <?php endforeach ?>
</div>

<?php if ($step <= 1): ?>
<!-- ── STEP 1: ORDER REVIEW ─────────────────────────────────────────────── -->
<h1 style="font-size:1.5rem;margin-bottom:1rem;">
  <i data-lucide="shopping-cart" class="icon icon-lg"></i> Review Your Order
</h1>

<div class="alert alert-info" style="margin-bottom:1.25rem;">
  <i data-lucide="user-check" class="icon"></i>
  Ordering as <strong><?= authName() ?></strong>
</div>

<table class="data-table" style="margin-bottom:1.5rem;">
  <thead><tr><th>Item</th><th>Size</th><th>Qty</th><th style="text-align:right;">Price</th></tr></thead>
  <tbody>
  <?php foreach ($items as $item): ?>
  <tr>
    <td>
      <div style="display:flex;align-items:center;gap:.65rem;">
        <img src="images/<?= htmlspecialchars($item['image_file']) ?>"
             style="width:2.5rem;height:2.5rem;object-fit:cover;border-radius:.4rem;border:1px solid var(--border);"
             alt="">
        <div>
          <strong><?= htmlspecialchars($item['brand']) ?></strong><br>
          <span class="text-xs text-muted"><?= htmlspecialchars($item['item_name']) ?></span>
        </div>
      </div>
    </td>
    <td><?= htmlspecialchars($item['size']) ?></td>
    <td><?= (int)$item['qty'] ?></td>
    <td style="text-align:right;">R<?= number_format($item['sell_price'] * $item['qty'], 2) ?></td>
  </tr>
  <?php endforeach ?>
  <tr style="background:var(--surface);">
    <td colspan="3"><strong>Order Total</strong></td>
    <td style="text-align:right;"><strong>R<?= number_format($total, 2) ?></strong></td>
  </tr>
  </tbody>
</table>

<div style="display:flex;gap:.75rem;flex-wrap:wrap;">
  <a href="checkout.php?step=2" class="btn btn-dark">
    <i data-lucide="credit-card" class="icon icon-sm"></i> Proceed to Payment
  </a>
  <a href="cart.php" class="btn btn-outline">
    <i data-lucide="arrow-left" class="icon icon-sm"></i> Edit Cart
  </a>
</div>

<?php else: ?>
<!-- ── STEP 2: PAYMENT METHOD ───────────────────────────────────────────── -->
<h1 style="font-size:1.5rem;margin-bottom:1.25rem;">
  <i data-lucide="credit-card" class="icon icon-lg"></i> Choose Payment Method
</h1>

<?php if ($err): ?>
<div class="alert alert-error" style="margin-bottom:1rem;">
  <i data-lucide="alert-circle" class="icon"></i> <?= htmlspecialchars($err) ?>
</div>
<?php endif ?>

<form method="POST" action="checkout.php" id="payForm"
      onsubmit="return validatePayForm()">
<input type="hidden" name="step"    value="2">
<input type="hidden" name="confirm" value="1">

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

  <!-- LEFT: payment selector + dynamic fields -->
  <div>
    <!-- Method cards -->
    <p style="font-size:.78rem;font-weight:600;text-transform:uppercase;
              letter-spacing:.06em;color:var(--muted);margin-bottom:.75rem;">Select a method</p>
    <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.5rem;">

      <?php
      $methods = [
        'card' => ['credit-card',  'Credit / Debit Card',        'Visa, Mastercard, Amex'],
        'eft'  => ['landmark',     'EFT / Bank Transfer',        'Direct bank transfer'],
        'cod'  => ['package-check','Cash on Delivery',           'Pay when your order arrives'],
      ];
      foreach ($methods as $val => [$ico, $label, $sub]):
      ?>
      <label style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;
                    border:1.5px solid var(--border);border-radius:var(--radius);
                    cursor:pointer;transition:.15s;" id="lbl_<?= $val ?>"
             onclick="selectMethod('<?= $val ?>')">
        <input type="radio" name="pay_method" value="<?= $val ?>"
               id="pm_<?= $val ?>" style="accent-color:var(--accent);"
               <?= (($_POST['pay_method'] ?? '') === $val) ? 'checked' : '' ?>>
        <span style="width:2rem;height:2rem;border-radius:.5rem;background:var(--bg);
                     border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i data-lucide="<?= $ico ?>" class="icon icon-sm"></i>
        </span>
        <span>
          <span style="display:block;font-weight:600;font-size:.875rem;"><?= $label ?></span>
          <span style="display:block;font-size:.73rem;color:var(--muted);"><?= $sub ?></span>
        </span>
      </label>
      <?php endforeach ?>
    </div>

    <!-- Card fields -->
    <div id="fields_card" style="display:none;animation:fadeIn .2s;">
      <p style="font-size:.78rem;font-weight:600;text-transform:uppercase;
                letter-spacing:.06em;color:var(--muted);margin-bottom:.65rem;">Card Details</p>
      <div class="form-group">
        <label>Cardholder Name</label>
        <input type="text" name="card_name" id="card_name"
               placeholder="As it appears on your card"
               value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Card Number</label>
        <input type="text" name="card_number" id="card_number"
               placeholder="1234 5678 9012 3456" maxlength="19"
               value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"
               oninput="fmtCard(this)">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        <div class="form-group">
          <label>Expiry (MM/YY)</label>
          <input type="text" name="card_expiry" id="card_expiry"
                 placeholder="06/27" maxlength="5"
                 value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>"
                 oninput="fmtExpiry(this)">
        </div>
        <div class="form-group">
          <label>CVV</label>
          <input type="text" name="card_cvv" id="card_cvv"
                 placeholder="123" maxlength="4"
                 value="<?= htmlspecialchars($_POST['card_cvv'] ?? '') ?>">
        </div>
      </div>
    </div>

    <!-- EFT fields -->
    <div id="fields_eft" style="display:none;animation:fadeIn .2s;">
      <div style="background:var(--surface);border:1px solid var(--border);
                  border-radius:var(--radius);padding:1.1rem;margin-bottom:1rem;font-size:.83rem;
                  line-height:1.9;">
        <p style="font-weight:700;margin-bottom:.5rem;">Bank Transfer Details</p>
        <p><strong>Bank:</strong> First National Bank (FNB)</p>
        <p><strong>Account Name:</strong> Pastimes (Pty) Ltd</p>
        <p><strong>Account Number:</strong> 62801234567</p>
        <p><strong>Branch Code:</strong> 250655</p>
        <p style="margin-top:.6rem;color:var(--muted);font-size:.77rem;">
          Use your order reference as payment reference. Orders are processed within 24 hours of payment confirmation.
        </p>
      </div>
      <div class="form-group">
        <label>Your EFT Reference / Proof of Payment</label>
        <input type="text" name="eft_ref" id="eft_ref"
               placeholder="e.g. TRN-20260618-001"
               value="<?= htmlspecialchars($_POST['eft_ref'] ?? '') ?>">
      </div>
    </div>

    <!-- COD info -->
    <div id="fields_cod" style="display:none;animation:fadeIn .2s;">
      <div style="background:#fff7ed;border:1px solid #fed7aa;
                  border-radius:var(--radius);padding:1.1rem;font-size:.83rem;line-height:1.8;">
        <p style="font-weight:700;margin-bottom:.35rem;">
          <i data-lucide="truck" class="icon icon-sm"></i> Cash on Delivery
        </p>
        <p>Pay in cash when your parcel is delivered. Our courier will collect payment at the door.</p>
        <p style="margin-top:.5rem;color:#92400e;font-size:.77rem;">
          COD is available within South Africa only. A R50 COD handling fee applies.
        </p>
      </div>
    </div>
  </div>

  <!-- RIGHT: order summary -->
  <div>
    <p style="font-size:.78rem;font-weight:600;text-transform:uppercase;
              letter-spacing:.06em;color:var(--muted);margin-bottom:.75rem;">Order Summary</p>
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--radius);padding:1.25rem;">
      <?php foreach ($items as $item): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;
                  padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.83rem;">
        <div style="display:flex;align-items:center;gap:.5rem;">
          <img src="images/<?= htmlspecialchars($item['image_file']) ?>"
               style="width:2rem;height:2rem;object-fit:cover;border-radius:.3rem;" alt="">
          <div>
            <span style="font-weight:600;"><?= htmlspecialchars($item['brand']) ?></span><br>
            <span style="color:var(--muted);font-size:.72rem;"><?= htmlspecialchars($item['item_name']) ?> &times;<?= (int)$item['qty'] ?></span>
          </div>
        </div>
        <span>R<?= number_format($item['sell_price'] * $item['qty'], 2) ?></span>
      </div>
      <?php endforeach ?>
      <div style="display:flex;justify-content:space-between;padding:.6rem 0;font-size:.83rem;">
        <span>Subtotal</span><span>R<?= number_format($total, 2) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:.3rem 0;font-size:.83rem;color:var(--muted);" id="cod-fee-row" style="display:none;">
        <span>COD Handling</span><span>R50.00</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-weight:700;
                  padding:.75rem 0 0;border-top:1.5px solid var(--border);margin-top:.5rem;">
        <span>Total</span>
        <span id="summary-total">R<?= number_format($total, 2) ?></span>
      </div>
    </div>

    <button type="submit" class="btn btn-dark btn-block" style="margin-top:1rem;">
      <i data-lucide="lock" class="icon icon-sm"></i> Place Order
    </button>
    <a href="checkout.php?step=1" class="btn btn-outline btn-block" style="margin-top:.5rem;">
      <i data-lucide="arrow-left" class="icon icon-sm"></i> Back to Review
    </a>
  </div>

</div><!-- /grid -->
</form>
<?php endif; // step ?>
<?php endif; // orderRef / err ?>
</div>

<style>
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; } }
</style>

<script>
var baseTotal = <?= number_format($total, 2, '.', '') ?>;

function selectMethod(m) {
  ['card','eft','cod'].forEach(function(x) {
    document.getElementById('fields_'+x).style.display = (x===m) ? 'block' : 'none';
    var lbl = document.getElementById('lbl_'+x);
    if (lbl) {
      lbl.style.borderColor = (x===m) ? 'var(--accent)' : 'var(--border)';
      lbl.style.background  = (x===m) ? 'var(--surface)' : '';
    }
  });
  document.getElementById('pm_'+m).checked = true;
  // COD fee
  var feeRow = document.getElementById('cod-fee-row');
  if (feeRow) feeRow.style.display = (m==='cod') ? 'flex' : 'none';
  var tot = document.getElementById('summary-total');
  if (tot) tot.textContent = 'R' + (m==='cod' ? (baseTotal+50).toFixed(2) : baseTotal.toFixed(2)).replace(/\B(?=(\d{3})+(?!\d))/g,',');
}

function fmtCard(el) {
  var v = el.value.replace(/\D/g,'').substring(0,16);
  el.value = v.replace(/(.{4})/g,'$1 ').trim();
}
function fmtExpiry(el) {
  var v = el.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
  el.value = v;
}
function validatePayForm() {
  var m = document.querySelector('input[name="pay_method"]:checked');
  if (!m) { alert('Please select a payment method.'); return false; }
  if (m.value === 'card') {
    var n = document.getElementById('card_name').value.trim();
    var c = document.getElementById('card_number').value.replace(/\D/g,'');
    var e = document.getElementById('card_expiry').value.trim();
    var v = document.getElementById('card_cvv').value.trim();
    if (!n) { alert('Enter the cardholder name.'); return false; }
    if (c.length < 13) { alert('Enter a valid card number.'); return false; }
    if (!/^\d{2}\/\d{2}$/.test(e)) { alert('Enter expiry as MM/YY.'); return false; }
    if (!/^\d{3,4}$/.test(v)) { alert('Enter a 3 or 4 digit CVV.'); return false; }
  }
  if (m.value === 'eft') {
    var r = document.getElementById('eft_ref').value.trim();
    if (!r) { alert('Enter your EFT reference number.'); return false; }
  }
  return true;
}
// Restore selected method on page reload (sticky form)
(function() {
  var checked = document.querySelector('input[name="pay_method"]:checked');
  if (checked) selectMethod(checked.value);
})();
</script>

<?php include 'includes/footer.php'; ?>
