<?php

$pageTitle = 'Admin Portal';
require_once 'includes/db.php';
require_once 'includes/session.php';

if (isAdmin()) redirect('admin.php');

// A customer session must be ended before the admin portal can be accessed,
// to avoid a single browser session holding both a customer and an admin login.
$customerLoggedIn = isLoggedIn();

$stickyEmail = '';
$error       = '';

if (!$customerLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $stickyEmail = $email;

    if (!$email || !$password) {
        $error = 'Both fields are required.';
    } else {
        $db   = getDB();
        $hash = md5($password);



        $st = $db->prepare("SELECT admin_id, username, email, password FROM tblAdmin WHERE email = ?");
        if (!$st) {
            $error = 'Database error. Please try again.';
        } else {
            $st->bind_param('s', $email);
            $st->execute();
            $res   = $st->get_result();
            $admin = $res ? $res->fetch_assoc() : null;
            $st->close();
            $db->close();

            if (!$admin) {
                $error = 'No administrator account found with that email address.';
            } elseif ($admin['password'] !== $hash) {
                $error = 'Incorrect password.';
            } else {
                $_SESSION['admin_id']    = $admin['admin_id'];
                $_SESSION['admin_name']  = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                redirect('admin.php');
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="form-card" style="max-width:420px;">


  <div style="text-align:center;margin-bottom:1.75rem;">
    <div style="width:3.5rem;height:3.5rem;background:var(--accent);border-radius:.75rem;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
      <i data-lucide="shield" class="icon" style="width:1.75rem;height:1.75rem;color:#fff;"></i>
    </div>
    <h1 style="font-size:1.5rem;">Admin Portal</h1>
    <p class="subtitle" style="margin-bottom:0;">Pastimes Administration Panel</p>
  </div>

  <?php if ($customerLoggedIn): ?>

  <div class="alert alert-error">
    <i data-lucide="alert-circle" class="icon"></i>
    You're currently signed in as <strong><?= authName() ?></strong>. Please log out of your customer account before accessing the admin portal.
  </div>

  <a href="logout.php?redirect=admin_login.php" class="btn btn-dark btn-block" style="margin-top:.75rem;">
    <i data-lucide="log-out" class="icon icon-sm"></i> Log Out of Customer Account
  </a>

  <hr class="form-divider">

  <p class="form-hint">
    <a href="index.php">
      <i data-lucide="arrow-left" class="icon icon-sm"></i> Back to Store
    </a>
  </p>

  <?php else: ?>

  <?php if ($error): ?>
  <div class="alert alert-error">
    <i data-lucide="alert-circle" class="icon"></i>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif ?>

  <form method="POST" action="admin_login.php" novalidate>

    <div class="form-group">
      <label>
        <i data-lucide="mail" class="icon icon-sm"></i> Admin Email (Username)
      </label>
      <input type="email" name="email"
             value="<?= htmlspecialchars($stickyEmail) ?>"
             placeholder="admin@pastimes.co.za"
             required autocomplete="email">
    </div>

    <div class="form-group">
      <label>
        <i data-lucide="lock" class="icon icon-sm"></i> Password
      </label>
      <input type="password" name="password"
             required autocomplete="current-password">
    </div>

    <button type="submit" class="btn btn-dark btn-block" style="margin-top:.75rem;">
      <i data-lucide="shield-check" class="icon icon-sm"></i> Sign In as Admin
    </button>

  </form>

  <hr class="form-divider">

  <p class="form-hint">
    <a href="index.php">
      <i data-lucide="arrow-left" class="icon icon-sm"></i> Back to Store
    </a>
  </p>


  <div class="alert alert-info" style="font-size:.78rem;">
    <i data-lucide="info" class="icon icon-sm"></i>
    <div>
      <strong>Demo Admin:</strong><br>
      <a href="mailto:admin@pastimes.co.za">admin@pastimes.co.za</a> / Password1
    </div>
  </div>

  <?php endif ?>

</div>

<?php include 'includes/footer.php'; ?>
