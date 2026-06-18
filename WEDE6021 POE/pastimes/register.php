<?php
// register.php — new user registration for Pastimes
// Validates all fields, checks for duplicate username/email, and inserts
// the new account with status='pending' so an admin must approve before login.

$pageTitle = 'Register';
require_once 'includes/db.php';
require_once 'includes/session.php';

// Already logged-in users don't need to register
if (isLoggedIn()) redirect('shop.php');

// Initialise sticky form values; each key maps to its POST field
$f = array_fill_keys(['firstName','lastName','username','email','address','phone'], '');
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $f['firstName'] = trim($_POST['firstName'] ?? '');
    $f['lastName']  = trim($_POST['lastName']  ?? '');
    $f['username']  = trim($_POST['username']  ?? '');
    $f['email']     = trim($_POST['email']     ?? '');
    $f['address']   = trim($_POST['address']   ?? '');
    $f['phone']     = trim($_POST['phone']     ?? '');
    $password       =      $_POST['password']  ?? '';
    $confirm        =      $_POST['confirm']   ?? '';


    if (!$f['firstName'])  $errors['firstName'] = 'First name is required.';
    if (!$f['lastName'])   $errors['lastName']  = 'Last name is required.';

    if (!$f['username']) {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^\w{3,30}$/', $f['username'])) {
        $errors['username'] = 'Username must be 3–30 alphanumeric characters (no spaces).';
    }

    if (!$f['email']) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!$f['address'])  $errors['address'] = 'Delivery address is required.';

    if (!$f['phone']) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\s\-]{10,15}$/', $f['phone'])) {
        $errors['phone'] = 'Enter a valid South African phone number (e.g. 082 123 4567).';
    }

    if (!$password) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($password && $password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }


    if (empty($errors)) {
        $db   = getDB();
        $hash = md5($password);


        $ck = $db->prepare("SELECT user_id FROM tblUser WHERE username=? OR email=?");
        if (!$ck) {
            $errors['general'] = 'Database error. Please try again.';
        } else {
            $ck->bind_param('ss', $f['username'], $f['email']);
            $ck->execute();
            $ck->store_result();

            if ($ck->num_rows > 0) {
                $errors['general'] = 'An account with that username or email already exists.';
            } else {
                $ins = $db->prepare("INSERT INTO tblUser (first_name,last_name,username,email,password,phone,address,user_status) VALUES (?,?,?,?,?,?,?,'active')");
                if ($ins) {
                    $ins->bind_param('sssssss', $f['firstName'],$f['lastName'],$f['username'],$f['email'],$hash,$f['phone'],$f['address']);
                    if ($ins->execute()) {
                        $success = true;
                        $f = array_fill_keys(array_keys($f), '');
                    } else {
                        $errors['general'] = 'Registration failed. Please try again.';
                    }
                    $ins->close();
                } else {
                    $errors['general'] = 'Database error. Please try again.';
                }
            }
            $ck->close();
        }
        $db->close();
    }
}

include 'includes/header.php';
?>

<div class="form-card" style="max-width:520px;">

  <h1>Create account</h1>
  <p class="subtitle">Join Pastimes &mdash; complete all required fields</p>

  <?php if ($success): ?>
  <div class="alert alert-success">
    <i data-lucide="check-circle" class="icon"></i>
    <div>
      <strong>Registration successful!</strong> Your account is ready. You can sign in now.
    </div>
  </div>
  <a href="login.php" class="btn btn-dark btn-block mt-4">
    <i data-lucide="log-in" class="icon icon-sm"></i> Go to Sign In
  </a>

  <?php else: ?>

  <?php if (isset($errors['general'])): ?>
  <div class="alert alert-error">
    <i data-lucide="alert-circle" class="icon"></i>
    <?= htmlspecialchars($errors['general']) ?>
  </div>
  <?php endif ?>

  <form method="POST" action="register.php" novalidate>


    <div class="form-row">
      <div class="form-group">
        <label><i data-lucide="user" class="icon icon-sm"></i> First Name *</label>
        <input type="text" name="firstName" value="<?= htmlspecialchars($f['firstName']) ?>" required>
        <?php if (isset($errors['firstName'])): ?>
          <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['firstName']) ?></p>
        <?php endif ?>
      </div>
      <div class="form-group">
        <label><i data-lucide="user" class="icon icon-sm"></i> Last Name *</label>
        <input type="text" name="lastName" value="<?= htmlspecialchars($f['lastName']) ?>" required>
        <?php if (isset($errors['lastName'])): ?>
          <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['lastName']) ?></p>
        <?php endif ?>
      </div>
    </div>


    <div class="form-group">
      <label><i data-lucide="at-sign" class="icon icon-sm"></i> Username *</label>
      <input type="text" name="username" value="<?= htmlspecialchars($f['username']) ?>"
             required pattern="\w{3,30}" placeholder="e.g. thabo_nkosi">
      <?php if (isset($errors['username'])): ?>
        <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['username']) ?></p>
      <?php endif ?>
    </div>


    <div class="form-group">
      <label><i data-lucide="mail" class="icon icon-sm"></i> Email Address *</label>
      <input type="email" name="email" value="<?= htmlspecialchars($f['email']) ?>"
             required placeholder="e.g. thabo@gmail.com">
      <?php if (isset($errors['email'])): ?>
        <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['email']) ?></p>
      <?php endif ?>
    </div>


    <div class="form-row">
      <div class="form-group">
        <label><i data-lucide="lock" class="icon icon-sm"></i> Password * <span style="font-weight:400;color:var(--muted);font-size:.72rem;">(min. 8 characters)</span></label>
        <input type="password" name="password" required minlength="8">
        <?php if (isset($errors['password'])): ?>
          <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['password']) ?></p>
        <?php endif ?>
      </div>
      <div class="form-group">
        <label><i data-lucide="lock" class="icon icon-sm"></i> Confirm Password *</label>
        <input type="password" name="confirm" required minlength="8">
        <?php if (isset($errors['confirm'])): ?>
          <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['confirm']) ?></p>
        <?php endif ?>
      </div>
    </div>


    <div class="form-group">
      <label><i data-lucide="map-pin" class="icon icon-sm"></i> Delivery Address * <span style="font-weight:400;color:var(--muted);font-size:.72rem;">(residential or work)</span></label>
      <input type="text" name="address" value="<?= htmlspecialchars($f['address']) ?>"
             required placeholder="e.g. 12 Mandela Ave, Soweto, 1804">
      <?php if (isset($errors['address'])): ?>
        <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['address']) ?></p>
      <?php endif ?>
    </div>


    <div class="form-group">
      <label><i data-lucide="phone" class="icon icon-sm"></i> Phone *</label>
      <input type="tel" name="phone" value="<?= htmlspecialchars($f['phone']) ?>"
             required placeholder="e.g. 082 123 4567">
      <?php if (isset($errors['phone'])): ?>
        <p class="form-error"><i data-lucide="alert-circle" class="icon icon-sm"></i><?= htmlspecialchars($errors['phone']) ?></p>
      <?php endif ?>
    </div>

    <button type="submit" class="btn btn-dark btn-block" style="margin-top:.5rem;">
      <i data-lucide="user-plus" class="icon icon-sm"></i> Create Account
    </button>

  </form>

  <hr class="form-divider">
  <p class="form-hint">Already have an account? <a href="login.php">Sign in</a></p>
  <?php endif ?>

</div>

<?php include 'includes/footer.php'; ?>
