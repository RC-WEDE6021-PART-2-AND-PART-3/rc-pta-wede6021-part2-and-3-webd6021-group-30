<?php
// cart.php — shopping cart view for Pastimes
// Uses the ShoppingCart OOP class (classes/ShoppingCart.php) for all
// state mutations (update qty, remove, clear).  Display logic below
// uses $cartObj->GetItems() and $cartObj->GetTotal().
//
// Student:  Vukosi Rikhotso       Student No: ST10439408
// Partner:  Theo Golele           Student No: ST10439863
// Group:    Code Couture (Group 02)   Module: WEDE6021
// Institution: IIE Rosebank College, Pretoria
// Declaration: Own original work except where referenced inline.

$pageTitle = 'My Cart';
require_once 'includes/session.php';
require_once 'classes/ShoppingCart.php';   // OOP ShoppingCart class

// Instantiate cart — loads existing cart from the PHP session
$cartObj = new ShoppingCart();

// Handle POST actions through the ShoppingCart class ProcessInput method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartObj->ProcessInput($_POST);  // delegates update / remove / clear
    header('Location: cart.php'); exit;
}

// Convenience variables for the view
$cart  = $cartObj->GetItems();
$total = $cartObj->GetTotal();

include 'includes/header.php';
?>

<section class="section-sm">
  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <h1 style="font-size:1.75rem;">
      <i data-lucide="shopping-cart" class="icon icon-lg"></i> Your Cart
    </h1>
    <?php if (!empty($cart)): ?>
    <span class="chip"><?= cartCount() ?> item<?= cartCount()!==1?'s':'' ?></span>
    <?php endif ?>
  </div>

  <?php if (isLoggedIn()): ?>
  <div class="alert alert-info" style="margin-bottom:1.25rem;">
    <i data-lucide="user-check" class="icon"></i>
    Shopping as <strong><?= authName() ?></strong>
  </div>
  <?php endif ?>

  <?php if (empty($cart)): ?>
  <div class="text-center" style="padding:4rem 0;">
    <i data-lucide="shopping-cart" class="icon" style="width:3.5rem;height:3.5rem;opacity:.2;margin-bottom:1.25rem;"></i>
    <p style="font-size:1.1rem;color:var(--muted);margin-bottom:1.5rem;">Your cart is empty.</p>
    <a href="shop.php" class="btn btn-dark">
      <i data-lucide="shopping-bag" class="icon icon-sm"></i> Browse the shop
    </a>
  </div>

  <?php else: ?>
  <div class="cart-layout">
    
    <div>
      <?php foreach ($cart as $item): ?>
      <div class="cart-item">
        <img class="cart-item-img"
             src="images/<?= htmlspecialchars($item['image_file']) ?>"
             alt="<?= htmlspecialchars($item['item_name']) ?>">
        <div>
          <p class="cart-item-title"><?= htmlspecialchars($item['brand'].' — '.$item['item_name']) ?></p>
          <p class="cart-item-meta">Size <?= htmlspecialchars($item['size']) ?> &nbsp;&middot;&nbsp; R<?= number_format($item['sell_price'],2) ?> each</p>
          
          <div class="qty-ctrl">
            <form method="POST" style="display:contents;">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id"     value="<?= $item['clothes_id'] ?>">
              <button type="submit" name="qty" value="<?= max(1,$item['qty']-1) ?>" class="qty-btn">
                <i data-lucide="minus" class="icon icon-sm"></i>
              </button>
            </form>
            <span style="min-width:1.5rem;text-align:center;font-size:.875rem;font-weight:600;"><?= $item['qty'] ?></span>
            <form method="POST" style="display:contents;">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id"     value="<?= $item['clothes_id'] ?>">
              <button type="submit" name="qty" value="<?= $item['qty']+1 ?>" class="qty-btn">
                <i data-lucide="plus" class="icon icon-sm"></i>
              </button>
            </form>
            <form method="POST" style="display:contents;">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="id"     value="<?= $item['clothes_id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm" style="margin-left:.5rem;">
                <i data-lucide="trash-2" class="icon icon-sm"></i> Remove
              </button>
            </form>
          </div>
        </div>
        <div class="cart-price">R<?= number_format($item['sell_price']*$item['qty'],2) ?></div>
      </div>
      <?php endforeach ?>

      <div style="display:flex;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap;">
        <a href="shop.php" class="btn btn-outline">
          <i data-lucide="arrow-left" class="icon icon-sm"></i> Continue Shopping
        </a>
        <form method="POST">
          <input type="hidden" name="action" value="clear">
          <button type="submit" class="btn btn-danger">
            <i data-lucide="trash" class="icon icon-sm"></i> Clear Cart
          </button>
        </form>
      </div>
    </div>

    
    <div class="order-summary">
      <h3><i data-lucide="receipt" class="icon icon-sm"></i> Order Summary</h3>
      <?php foreach ($cart as $item): ?>
      <div class="sum-row">
        <span style="max-width:160px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
          <?= htmlspecialchars($item['item_name']) ?> &times;<?= $item['qty'] ?>
        </span>
        <span>R<?= number_format($item['sell_price']*$item['qty'],2) ?></span>
      </div>
      <?php endforeach ?>
      <div class="sum-row"><span>Shipping</span><span class="text-muted">Calculated at checkout</span></div>
      <div class="sum-row total">
        <span>Total</span>
        <span>R<?= number_format($total,2) ?></span>
      </div>

      <?php if (isLoggedIn()): ?>
      <a href="checkout.php" class="btn btn-dark btn-block" style="margin-top:1rem;">
        <i data-lucide="credit-card" class="icon icon-sm"></i> Proceed to Checkout
      </a>
      <?php else: ?>
      <a href="login.php?required=1" class="btn btn-dark btn-block" style="margin-top:1rem;">
        <i data-lucide="log-in" class="icon icon-sm"></i> Sign in to Checkout
      </a>
      <?php endif ?>
    </div>
  </div>
  <?php endif ?>
</section>

<?php include 'includes/footer.php'; ?>
