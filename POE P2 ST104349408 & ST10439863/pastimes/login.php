<?php
// login.php — handles user authentication for Pastimes
// Validates username, email, and hashed password against tblUser.
// Blocks login if the account is still pending admin verification.

$pageTitle = 'Sign In';
require_once 'includes/db.php';
require_once 'includes/session.php';

// Redirect already-logged-in users away from the login page
if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isLoggedIn()) redirect('shop.php');
}

// Sticky form values and status messages
$stickyUser  = '';
$stickyEmail = '';
$error       = '';
$info        = '';
$loginUser   = null;

// Show info message if redirected from registration or a protected page
if (isset($_GET['registered'])) $info = 'Account created! You can sign in now.';
if (isset($_GET['required']))   $info = 'Please sign in to continue.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $stickyUser  = $username;
    $stickyEmail = $email;

    if (!$username || !$email || !$password) {
        $error = 'All fields are required.';
    } else {
        $db   = getDB();
        $hash = md5($password);


        $st = $db->prepare(
            "SELECT user_id, first_name, last_name, username, email,
                    phone, address, user_status, created_at
             FROM tblUser
             WHERE username = ? AND email = ?"
        );
        if (!$st) {
            $error = 'Database error. Please try again.';
        } else {
            $st->bind_param('ss', $username, $email);
            $st->execute();
            $res  = $st->get_result();
            $user = $res ? $res->fetch_assoc() : null;
            $st->close();


            $st2 = $db->prepare(
                "SELECT user_id, first_name, last_name, username, email,
                        phone, address, user_status, created_at
                 FROM tblUser
                 WHERE username = ? AND email = ? AND password = ?"
            );
            $verifiedUser = null;
            if ($user && $st2) {
                $st2->bind_param('sss', $username, $email, $hash);
                $st2->execute();
                $res2 = $st2->get_result();
                $verifiedUser = $res2 ? $res2->fetch_assoc() : null;
                $st2->close();
            }
            $db->close();

            if (!$user) {
                $error = 'No account found with that username and email combination.';
            } elseif (!$verifiedUser) {
                $error = 'Incorrect password. Please try again.';
            } elseif ($verifiedUser['user_status'] === 'pending') {
                $error = 'Your account is pending administrator verification. Please check back later.';
            } else {

                $_SESSION['user_id']     = $verifiedUser['user_id'];
                $_SESSION['user_name']   = $verifiedUser['first_name'] . ' ' . $verifiedUser['last_name'];
                $_SESSION['user_email']  = $verifiedUser['email'];
                $_SESSION['user_status'] = $verifiedUser['user_status'];

                $loginUser = $verifiedUser;
            }
        }
    }
}

include 'includes/header.php';
?>

<?php if ($loginUser): ?>

<div class="section-sm" style="max-width:720px;margin:0 auto;">


  <div class="alert alert-success" style="font-size:1rem;padding:1rem 1.25rem;">
    <i data-lucide="check-circle" class="icon"></i>
    <strong>User <?= htmlspecialchars($loginUser['first_name'].' '.$loginUser['last_name']) ?> is logged in.</strong>
  </div>

  <h2 style="margin-top:1.5rem;margin-bottom:.875rem;font-size:1.1rem;">
    <i data-lucide="user" class="icon icon-sm"></i> Your Account Details
  </h2>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem 1.25rem;margin-bottom:1.75rem;">
    <?php
      $fields = [
        'User ID'    => htmlspecialchars((string)$loginUser['user_id']),
        'First Name' => htmlspecialchars($loginUser['first_name']),
        'Last Name'  => htmlspecialchars($loginUser['last_name']),
        'Username'   => htmlspecialchars($loginUser['username']),
        'Email'      => htmlspecialchars($loginUser['email']),
        'Phone'      => htmlspecialchars($loginUser['phone'] ?? '—'),
        'Status'     => '<span class="badge badge-'.$loginUser['user_status'].'">'.ucfirst($loginUser['user_status']).'</span>',
        'Member Since' => htmlspecialchars($loginUser['created_at']),
      ];
      foreach ($fields as $label => $value): ?>
      <div style="background:var(--bg-secondary,#f5f5f7);border-radius:.5rem;padding:.625rem .875rem;">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted,#888);margin-bottom:.2rem;"><?= $label ?></div>
        <div style="font-size:.9rem;color:var(--text-primary,#1a1a1a);word-break:break-all;"><?= $value ?></div>
      </div>
    <?php endforeach ?>

    <?php /* Address spans full width */ ?>
    <div style="grid-column:1/-1;background:var(--bg-secondary,#f5f5f7);border-radius:.5rem;padding:.625rem .875rem;">
      <div style="font-size:.68rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted,#888);margin-bottom:.2rem;">Address</div>
      <div style="font-size:.9rem;color:var(--text-primary,#1a1a1a);"><?= htmlspecialchars($loginUser['address'] ?? '—') ?></div>
    </div>
  </div>

  <a href="shop.php" class="btn btn-dark">
    <i data-lucide="shopping-bag" class="icon icon-sm"></i> Continue to Shop
  </a>

</div>

<?php else: ?>

<div class="form-card" style="max-width:440px;">

  <h1>Welcome back</h1>
  <p class="subtitle">Sign in to your Pastimes account</p>

  <?php if ($info): ?>
  <div class="alert alert-info">
    <i data-lucide="info" class="icon"></i>
    <?= htmlspecialchars($info) ?>
  </div>
  <?php endif ?>

  <?php if ($error): ?>
  <div class="alert alert-error">
    <i data-lucide="alert-circle" class="icon"></i>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif ?>


  <form method="POST" action="login.php" novalidate>

    <div class="form-group">
      <label>
        <i data-lucide="user" class="icon icon-sm"></i> Username *
      </label>
      <input type="text" name="username"
             value="<?= htmlspecialchars($stickyUser) ?>"
             placeholder="e.g. user01"
             required autocomplete="username">
    </div>

    <div class="form-group">
      <label>
        <i data-lucide="mail" class="icon icon-sm"></i> Email Address *
      </label>
      <input type="email" name="email"
             value="<?= htmlspecialchars($stickyEmail) ?>"
             placeholder="e.g. user@example.com"
             required autocomplete="email">
    </div>

    <div class="form-group">
      <label>
        <i data-lucide="lock" class="icon icon-sm"></i> Password *
      </label>
      <input type="password" name="password"
             placeholder="Your password"
             required autocomplete="current-password">
    </div>

    <button type="submit" class="btn btn-dark btn-block" style="margin-top:.75rem;">
      <i data-lucide="log-in" class="icon icon-sm"></i> Sign In
    </button>
  </form>

  <hr class="form-divider">

  <p class="form-hint">
    Don't have an account? <a href="register.php">Register here</a>
  </p>


  <div class="alert alert-info" style="margin-top:1rem;font-size:.78rem;">
    <i data-lucide="info" class="icon icon-sm"></i>
    <div>
      <strong>Demo User:</strong><br>
      Username: <code style="background:rgba(0,0,0,.06);padding:.1rem .35rem;border-radius:.25rem;">user01</code>
      &nbsp;|&nbsp; Email: <code style="background:rgba(0,0,0,.06);padding:.1rem .35rem;border-radius:.25rem;">thabo.nkosi@gmail.com</code>
      &nbsp;|&nbsp; Password: <code style="background:rgba(0,0,0,.06);padding:.1rem .35rem;border-radius:.25rem;">Password1</code>
    </div>
  </div>

</div>
<?php endif ?>

<?php include 'includes/footer.php'; ?>
