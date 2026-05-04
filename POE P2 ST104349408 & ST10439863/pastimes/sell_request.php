<?php
// sell_request.php — lets verified users submit an item for the Pastimes team to review
// Validated submissions are stored in tblSellRequest with status='pending'.
// An admin must approve before the item appears in the shop.

$pageTitle = 'Sell a Piece';
require_once 'includes/db.php';
require_once 'includes/session.php';

// Only logged-in users may submit a sell request
if (!isLoggedIn()) redirect('login.php?required=1');

$errors  = [];
$success = false;
$f = [
    'brand' => '', 'item_name' => '', 'description' => '',
    'size' => '', 'condition_' => 'Good', 'ask_price' => '',
    'category' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['brand']       = trim($_POST['brand']       ?? '');
    $f['item_name']   = trim($_POST['item_name']   ?? '');
    $f['description'] = trim($_POST['description'] ?? '');
    $f['size']        = trim($_POST['size']        ?? '');
    $f['condition_']  =      $_POST['condition_']  ?? 'Good';
    $f['ask_price']   = trim($_POST['ask_price']   ?? '');
    $f['category']    = trim($_POST['category']    ?? '');

    
    if (!$f['brand'])     $errors['brand']     = 'Brand is required.';
    if (!$f['item_name']) $errors['item_name'] = 'Item name is required.';
    if (!$f['size'])      $errors['size']      = 'Size is required.';
    if (!$f['category'])  $errors['category']  = 'Category is required.';
    if (!$f['ask_price'] || !is_numeric($f['ask_price']) || (float)$f['ask_price'] <= 0)
        $errors['ask_price'] = 'Enter a valid asking price.';

    
    $imgFile = '';
    if (!empty($_FILES['image']['name'])) {
        $allowed  = ['jpg','jpeg','png','webp','gif'];
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors['image'] = 'Only JPG, PNG, WEBP or GIF images are allowed.';
        } elseif ($_FILES['image']['size'] > 4 * 1024 * 1024) {
            $errors['image'] = 'Image must be under 4 MB.';
        } else {
            $imgFile = 'sell_' . time() . '_' . preg_replace('/[^a-z0-9_.]/', '', strtolower($_FILES['image']['name']));
            $dest = __DIR__ . '/images/' . $imgFile;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors['image'] = 'Image upload failed. Please try again.';
                $imgFile = '';
            }
        }
    }

    if (empty($errors)) {
        $db    = getDB();
        $uid   = (int)$_SESSION['user_id'];
        $price = (float)$f['ask_price'];

        
        $db->query("CREATE TABLE IF NOT EXISTS tblSellRequest (
            request_id   INT AUTO_INCREMENT PRIMARY KEY,
            user_id      INT           NOT NULL,
            brand        VARCHAR(100)  NOT NULL,
            item_name    VARCHAR(255)  NOT NULL,
            description  TEXT          DEFAULT NULL,
            size         VARCHAR(20)   NOT NULL,
            condition_   ENUM('Excellent','Very Good','Good') NOT NULL DEFAULT 'Good',
            ask_price    DECIMAL(10,2) NOT NULL,
            category     VARCHAR(60)   NOT NULL,
            image_file   VARCHAR(255)  DEFAULT NULL,
            status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_sellreq_user FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $st = $db->prepare("INSERT INTO tblSellRequest (user_id,brand,item_name,description,size,condition_,ask_price,category,image_file) VALUES (?,?,?,?,?,?,?,?,?)");
        if ($st) {
            $st->bind_param('isssssdss',
                $uid, $f['brand'], $f['item_name'], $f['description'],
                $f['size'], $f['condition_'], $price, $f['category'], $imgFile
            );
            if ($st->execute()) {
                $success = true;
                $f = array_fill_keys(array_keys($f), '');
                $f['condition_'] = 'Good';
            } else {
                $errors['general'] = 'Submission failed: ' . $st->error . '. Please try again.';
            }
            $st->close();
        } else {
            $errors['general'] = 'Database error: ' . $db->error . '. Please try again.';
        }
        $db->close();
    }
}

include 'includes/header.php';
?>

<div class="form-card" style="max-width:600px;">

  <p class="eyebrow">Sell on Pastimes</p>
  <h1 style="margin-top:.35rem;">Submit a piece to sell</h1>
  <p class="subtitle">Fill in the details below. Our team reviews every submission before it goes live.</p>

  <?php if ($success): ?>
  <div class="alert alert-success" style="margin-bottom:1.5rem;">
    <i data-lucide="check-circle" class="icon"></i>
    <div>
      <strong>Submission received!</strong><br>
      The Pastimes team will review your item and get back to you within 24 hours.
      <a href="shop.php" style="color:inherit;font-weight:600;">Continue browsing.</a>
    </div>
  </div>
  <?php endif ?>

  <?php if (!empty($errors['general'])): ?>
  <div class="alert alert-error">
    <i data-lucide="alert-circle" class="icon"></i>
    <?= htmlspecialchars($errors['general']) ?>
  </div>
  <?php endif ?>

  <form method="POST" enctype="multipart/form-data">


    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
      <div class="form-group">
        <label>Brand *</label>
        <select name="brand">
          <option value="">Select brand</option>
          <?php foreach(['Nike','Adidas','Puma','Zara','Tommy Hilfiger','Calvin Klein','Guess','H&M','Woolworths','Other'] as $b): ?>
            <option <?= $f['brand']===$b?'selected':'' ?>><?= $b ?></option>
          <?php endforeach ?>
        </select>
        <?php if (!empty($errors['brand'])): ?><p class="form-error"><?= $errors['brand'] ?></p><?php endif ?>
      </div>
      <div class="form-group">
        <label>Item Name *</label>
        <input type="text" name="item_name" value="<?= htmlspecialchars($f['item_name']) ?>"
               placeholder="e.g. Nike Air Max 90" required>
        <?php if (!empty($errors['item_name'])): ?><p class="form-error"><?= $errors['item_name'] ?></p><?php endif ?>
      </div>
    </div>

    
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
      <div class="form-group">
        <label>Category *</label>
        <select name="category" required>
          <option value="">Select</option>
          <?php foreach(['Sneakers','Dresses','Knitwear','Tees','Outerwear'] as $c): ?>
            <option <?= $f['category']===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach ?>
        </select>
        <?php if (!empty($errors['category'])): ?><p class="form-error"><?= $errors['category'] ?></p><?php endif ?>
      </div>
      <div class="form-group">
        <label>Size *</label>
        <input type="text" name="size" value="<?= htmlspecialchars($f['size']) ?>"
               placeholder="e.g. M, L, 9" required>
        <?php if (!empty($errors['size'])): ?><p class="form-error"><?= $errors['size'] ?></p><?php endif ?>
      </div>
      <div class="form-group">
        <label>Condition *</label>
        <select name="condition_">
          <?php foreach(['Excellent','Very Good','Good'] as $c): ?>
            <option <?= $f['condition_']===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach ?>
        </select>
      </div>
    </div>

    
    <div class="form-group">
      <label>Asking Price (R) *</label>
      <input type="number" step="0.01" min="1" name="ask_price"
             value="<?= htmlspecialchars($f['ask_price']) ?>" placeholder="e.g. 450.00" required>
      <?php if (!empty($errors['ask_price'])): ?><p class="form-error"><?= $errors['ask_price'] ?></p><?php endif ?>
      <p class="form-hint" style="margin-top:.35rem;">Pastimes takes a 10% platform fee on sale.</p>
    </div>

    
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="3"
                placeholder="Describe the item — colourway, condition details, wear history…"
                style="width:100%;border-radius:var(--radius-sm);border:1.5px solid var(--border);padding:.65rem .875rem;font-size:.875rem;resize:vertical;"><?= htmlspecialchars($f['description']) ?></textarea>
    </div>

    
    <div class="form-group">
      <label>
        <i data-lucide="image" class="icon icon-sm"></i> Item Photo
      </label>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
             style="padding:.5rem;">
      <?php if (!empty($errors['image'])): ?><p class="form-error"><?= $errors['image'] ?></p><?php endif ?>
      <p class="form-hint" style="margin-top:.35rem;">Upload a clear photo of the item. Max 4 MB. JPG, PNG or WEBP.</p>
    </div>

    <button type="submit" class="btn btn-dark btn-block" style="margin-top:1rem;">
      <i data-lucide="send" class="icon icon-sm"></i> Submit for Review
    </button>
  </form>

  <hr class="form-divider">
  <p class="form-hint">
    <i data-lucide="info" class="icon icon-sm"></i>
    Submissions are reviewed within 24 hours. You will be notified once your item is approved or if further information is needed.
  </p>

</div>

<?php include 'includes/footer.php'; ?>
