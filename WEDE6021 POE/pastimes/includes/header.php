<?php

require_once __DIR__ . '/session.php';
$p = basename($_SERVER['PHP_SELF']);
function nav(string $file, string $cur): string {
    return $file === $cur ? 'active' : '';
}

$_unreadMsgs = 0;
if (isLoggedIn() && function_exists('getDB')) {
    try {
        $_hdrDb = getDB();
        $_st = $_hdrDb->prepare("SELECT COUNT(*) FROM tblMessages WHERE receiver_id=? AND is_read=0");
        if ($_st) {
            $_uid = (int)$_SESSION['user_id'];
            $_st->bind_param('i', $_uid);
            $_st->execute();
            $_st->bind_result($_unreadMsgs);
            $_st->fetch();
            $_st->close();
        }
        $_hdrDb->close();
    } catch (Throwable $e) {  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $pageTitle ?? 'Pastimes' ?> — Pre-Loved Fashion SA</title>
  <link rel="stylesheet" href="<?= str_repeat('../', substr_count($p, '/')) ?>css/style.css">
  
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
</head>
<body>
<div class="page-card">
<header class="site-header">

  
  <a href="index.php" class="logo">
    <span class="logo-mark">P</span>
    <span class="logo-text">Past<em>imes</em></span>
  </a>

  
  <nav class="site-nav">
    <a href="index.php"   class="<?= nav('index.php',   $p) ?>">
      <i data-lucide="home" class="icon icon-sm"></i> Home
    </a>
    <a href="shop.php"    class="<?= nav('shop.php',    $p) ?>">
      <i data-lucide="shopping-bag" class="icon icon-sm"></i> Shop
    </a>
    <a href="about.php"   class="<?= nav('about.php',   $p) ?>">
      <i data-lucide="info" class="icon icon-sm"></i> About Us
    </a>
    <a href="contact.php" class="<?= nav('contact.php', $p) ?>">
      <i data-lucide="mail" class="icon icon-sm"></i> Contact
    </a>
    <?php if (isLoggedIn()): ?>
    <a href="cart.php" class="<?= nav('cart.php', $p) ?> cart-wrap">
      <i data-lucide="shopping-cart" class="icon icon-sm"></i> Cart
      <?php if (cartCount() > 0): ?>
        <span class="cart-badge"><?= cartCount() ?></span>
      <?php endif ?>
    </a>
    <?php endif ?>
  </nav>


  <div class="header-right">
    <?php if (isLoggedIn()): ?>
      <div class="user-dropdown">
        <button class="user-chip" onclick="toggleUserMenu()" type="button">
          <i data-lucide="user-check" class="icon icon-sm"></i>
          <?= authName() ?>
          <i data-lucide="chevron-down" class="icon icon-sm" style="opacity:.5;"></i>
        </button>
        <div class="user-menu" id="userMenu">
          <a href="messages.php" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:.5rem;">
              <i data-lucide="message-circle" class="icon icon-sm"></i> Messages
            </span>
            <?php if ($_unreadMsgs > 0): ?>
              <span style="background:#dc2626;color:#fff;border-radius:9999px;font-size:.68rem;font-weight:700;padding:.1rem .45rem;line-height:1.4;">
                <?= $_unreadMsgs ?>
              </span>
            <?php endif ?>
          </a>
          <a href="orders.php"><i data-lucide="package" class="icon icon-sm"></i> Purchase History</a>
          <a href="sell_request.php"><i data-lucide="tag" class="icon icon-sm"></i> Sell a Piece</a>
          <hr style="margin:.35rem 0;border:none;border-top:1px solid var(--border);">
          <a href="logout.php" style="color:#dc2626;"><i data-lucide="log-out" class="icon icon-sm"></i> Log out</a>
        </div>
      </div>
      <a href="sell_request.php" class="btn btn-dark btn-sm <?= nav('sell_request.php', $p) ?>">
        <i data-lucide="tag" class="icon icon-sm"></i> Sell
      </a>
    <?php elseif (isAdmin()): ?>
      <span class="user-chip">
        <i data-lucide="shield-check" class="icon icon-sm"></i> Admin
      </span>
      <a href="admin.php"        class="btn btn-outline btn-sm">Dashboard</a>
      <a href="admin_logout.php" class="btn btn-dark btn-sm">
        <i data-lucide="log-out" class="icon icon-sm"></i> Log out
      </a>
    <?php else: ?>
      <a href="login.php"    class="btn btn-outline btn-sm">
        <i data-lucide="log-in" class="icon icon-sm"></i> Login
      </a>
      <a href="register.php" class="btn btn-dark btn-sm">
        <i data-lucide="user-plus" class="icon icon-sm"></i> Register
      </a>
    <?php endif ?>
  </div>

</header>
<style>
.user-dropdown { position:relative; }
.user-dropdown .user-chip { cursor:pointer; border:none; background:none; font:inherit; }
.user-menu {
  display:none; position:absolute; right:0; top:calc(100% + .5rem);
  background:var(--bg); border:1px solid var(--border); border-radius:var(--radius);
  box-shadow:0 8px 24px rgba(0,0,0,.1); min-width:180px; z-index:200; padding:.35rem;
}
.user-menu.open { display:block; }
.user-menu a {
  display:flex; align-items:center; gap:.5rem; padding:.55rem .75rem;
  border-radius:calc(var(--radius) - 2px); font-size:.83rem; color:var(--text);
  text-decoration:none; white-space:nowrap;
}
.user-menu a:hover { background:var(--surface); }
</style>
<script>
function toggleUserMenu() {
  document.getElementById('userMenu').classList.toggle('open');
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.user-dropdown')) {
    var m = document.getElementById('userMenu');
    if (m) m.classList.remove('open');
  }
});
</script>
