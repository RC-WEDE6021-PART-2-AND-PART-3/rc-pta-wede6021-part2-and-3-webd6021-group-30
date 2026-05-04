<?php
// admin.php — Pastimes administration panel
// Single-file dashboard with section routing via ?section=<name>.
// Handles: user verification/CRUD, clothing CRUD, order overview,
// and sell-request approve/reject workflow.

$pageTitle = 'Admin Dashboard';
require_once 'includes/db.php';
require_once 'includes/session.php';

// Reject non-admin sessions immediately
if (!isAdmin()) redirect('admin_login.php');

$db      = getDB();
// Active section drives which panel is rendered; defaults to overview
$section = $_GET['section'] ?? 'dashboard';
$msg     = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    
    if ($action === 'verify') {
        $uid = (int)$_POST['uid'];
        $st  = $db->prepare("UPDATE tblUser SET user_status='active' WHERE user_id=?");
        $st->bind_param('i', $uid); $st->execute(); $st->close();
        $msg = 'User verified and activated.'; $section = 'users';

    
    } elseif ($action === 'del_user') {
        $uid = (int)$_POST['uid'];
        $st  = $db->prepare("DELETE FROM tblUser WHERE user_id=?");
        $st->bind_param('i', $uid); $st->execute(); $st->close();
        $msg = 'User deleted.'; $section = 'users';

    
    } elseif ($action === 'add_user') {
        $fn   = trim($_POST['first_name'] ?? '');
        $ln   = trim($_POST['last_name']  ?? '');
        $un   = trim($_POST['username']   ?? '');
        $em   = trim($_POST['email']      ?? '');
        $ph   = trim($_POST['phone']      ?? '');
        $ad   = trim($_POST['address']    ?? '');
        $pw   = $_POST['password'] ?? '';
        $stat = $_POST['user_status'] ?? 'active';
        if ($fn && $ln && $un && $em && $pw) {
            $hash = md5($pw);
            $st   = $db->prepare("INSERT IGNORE INTO tblUser (first_name,last_name,username,email,password,phone,address,user_status) VALUES (?,?,?,?,?,?,?,?)");
            $st->bind_param('ssssssss',$fn,$ln,$un,$em,$hash,$ph,$ad,$stat);
            if ($st->execute()) $msg = "User '$un' added.";
            else { $msg = 'Error: '.$db->error; $msgType='error'; }
            $st->close();
        } else { $msg='All fields required.'; $msgType='error'; }
        $section = 'users';

    
    } elseif ($action === 'upd_user') {
        $uid  = (int)$_POST['uid'];
        $fn   = trim($_POST['first_name'] ?? '');
        $ln   = trim($_POST['last_name']  ?? '');
        $em   = trim($_POST['email']      ?? '');
        $ph   = trim($_POST['phone']      ?? '');
        $ad   = trim($_POST['address']    ?? '');
        $stat = $_POST['user_status'] ?? 'active';
        $st   = $db->prepare("UPDATE tblUser SET first_name=?,last_name=?,email=?,phone=?,address=?,user_status=? WHERE user_id=?");
        $st->bind_param('ssssssi',$fn,$ln,$em,$ph,$ad,$stat,$uid);
        if ($st->execute()) $msg='User updated.';
        else { $msg='Error: '.$db->error; $msgType='error'; }
        $st->close(); $section='users';

    
    } elseif ($action === 'add_clothes') {
        $br = trim($_POST['brand']       ?? '');
        $nm = trim($_POST['item_name']   ?? '');
        $de = trim($_POST['description'] ?? '');
        $si = trim($_POST['size']        ?? '');
        $co = $_POST['condition_']  ?? 'Good';
        $pr = (float)($_POST['sell_price'] ?? 0);
        $ca = trim($_POST['category'] ?? '');
        $im = trim($_POST['image_file']  ?? '');
        $sq = (int)($_POST['stock_qty']  ?? 1);
        if ($br && $nm && $si && $pr > 0 && $ca) {
            $st = $db->prepare("INSERT INTO tblClothes (brand,item_name,description,size,condition_,sell_price,category,image_file,stock_qty) VALUES (?,?,?,?,?,?,?,?,?)");
            $st->bind_param('sssssdssi',$br,$nm,$de,$si,$co,$pr,$ca,$im,$sq);
            if ($st->execute()) $msg="Clothing item '$nm' added.";
            else { $msg='Error: '.$db->error; $msgType='error'; }
            $st->close();
        } else { $msg='Fill all required fields.'; $msgType='error'; }
        $section='clothes';

    
    } elseif ($action === 'upd_clothes') {
        $cid = (int)$_POST['cid'];
        $br  = trim($_POST['brand']       ?? '');
        $nm  = trim($_POST['item_name']   ?? '');
        $de  = trim($_POST['description'] ?? '');
        $si  = trim($_POST['size']        ?? '');
        $co  = $_POST['condition_']  ?? 'Good';
        $pr  = (float)($_POST['sell_price'] ?? 0);
        $ca  = trim($_POST['category'] ?? '');
        $im  = trim($_POST['image_file']  ?? '');
        $sq  = (int)($_POST['stock_qty']  ?? 1);
        $st  = $db->prepare("UPDATE tblClothes SET brand=?,item_name=?,description=?,size=?,condition_=?,sell_price=?,category=?,image_file=?,stock_qty=? WHERE clothes_id=?");
        $st->bind_param('sssssdssi i',$br,$nm,$de,$si,$co,$pr,$ca,$im,$sq,$cid);
        $st->close();
        $st = $db->prepare("UPDATE tblClothes SET brand=?,item_name=?,description=?,size=?,condition_=?,sell_price=?,category=?,image_file=?,stock_qty=? WHERE clothes_id=?");
        $st->bind_param('sssssdssii',$br,$nm,$de,$si,$co,$pr,$ca,$im,$sq,$cid);
        if ($st->execute()) $msg='Clothing item updated.';
        else { $msg='Error: '.$db->error; $msgType='error'; }
        $st->close(); $section='clothes';

    
    } elseif ($action === 'del_clothes') {
        $cid = (int)$_POST['cid'];
        $st  = $db->prepare("DELETE FROM tblClothes WHERE clothes_id=?");
        $st->bind_param('i',$cid); $st->execute(); $st->close();
        $msg='Clothing item removed from database.'; $section='clothes';

    
    } elseif ($action === 'approve_sell') {
        $rid = (int)$_POST['rid'];
        $st  = $db->prepare("SELECT * FROM tblSellRequest WHERE request_id=?");
        if ($st) {
            $st->bind_param('i', $rid); $st->execute();
            $req = $st->get_result()->fetch_assoc(); $st->close();
            if ($req) {
                $ins = $db->prepare("INSERT INTO tblClothes (brand,item_name,description,size,condition_,sell_price,category,image_file,seller_id,stock_qty) VALUES (?,?,?,?,?,?,?,?,?,1)");
                $ins->bind_param('sssssdssi', $req['brand'],$req['item_name'],$req['description'],$req['size'],$req['condition_'],$req['ask_price'],$req['category'],$req['image_file'],$req['user_id']);
                $ins->execute(); $ins->close();
                $upd = $db->prepare("UPDATE tblSellRequest SET status='approved' WHERE request_id=?");
                $upd->bind_param('i', $rid); $upd->execute(); $upd->close();
                $msg = 'Sell request approved — item added to the shop.';
            }
        }
        $section = 'sell_requests';

    
    } elseif ($action === 'reject_sell') {
        $rid = (int)$_POST['rid'];
        $st  = $db->prepare("UPDATE tblSellRequest SET status='rejected' WHERE request_id=?");
        if ($st) { $st->bind_param('i', $rid); $st->execute(); $st->close(); }
        $msg = 'Sell request rejected.'; $section = 'sell_requests';
    }
}

$users        = [];
$clothes      = [];
$stats        = [];
$orders       = [];
$sellRequests = [];
$editUser     = null;
$editClothes  = null;

$safeCount = function(mysqli $db, string $sql): int {
    $r = $db->query($sql);
    if ($r && $row = $r->fetch_assoc()) return (int)($row['c'] ?? 0);
    return 0;
};

if (in_array($section,['dashboard','users'])) {
    $r = $db->query("SELECT * FROM tblUser ORDER BY created_at DESC");
    if ($r) while ($row = $r->fetch_assoc()) $users[] = $row;
}
if (in_array($section,['dashboard','clothes'])) {
    $r = $db->query("SELECT c.*, u.first_name, u.last_name FROM tblClothes c LEFT JOIN tblUser u ON c.seller_id=u.user_id ORDER BY c.clothes_id DESC");
    if ($r) while ($row = $r->fetch_assoc()) $clothes[] = $row;
}
if ($section==='dashboard') {
    $stats = [
        'users'        => $safeCount($db, "SELECT COUNT(*) c FROM tblUser"),
        'pending'      => $safeCount($db, "SELECT COUNT(*) c FROM tblUser WHERE user_status='pending'"),
        'items'        => $safeCount($db, "SELECT COUNT(*) c FROM tblClothes"),
        'orders'       => $safeCount($db, "SELECT COUNT(*) c FROM tblOrder"),
        'sell_pending' => $safeCount($db, "SELECT COUNT(*) c FROM tblSellRequest WHERE status='pending'"),
    ];
}
if ($section==='orders') {
    $r = $db->query("SELECT o.order_id, o.order_ref, o.total_price, o.status, o.payment_status, o.created_at,
                            u.first_name, u.last_name, u.username,
                            COUNT(oi.item_id) AS item_count
                     FROM tblOrder o
                     JOIN tblUser u ON o.user_id = u.user_id
                     JOIN tblOrderItem oi ON o.order_id = oi.order_id
                     GROUP BY o.order_id
                     ORDER BY o.created_at DESC");
    if ($r) while ($row = $r->fetch_assoc()) $orders[] = $row;
}
if ($section==='sell_requests') {
    $db->query("CREATE TABLE IF NOT EXISTS tblSellRequest (
        request_id   INT AUTO_INCREMENT PRIMARY KEY,
        user_id      INT NOT NULL,
        brand        VARCHAR(100) NOT NULL,
        item_name    VARCHAR(255) NOT NULL,
        description  TEXT DEFAULT NULL,
        size         VARCHAR(20) NOT NULL,
        condition_   ENUM('Excellent','Very Good','Good') NOT NULL DEFAULT 'Good',
        ask_price    DECIMAL(10,2) NOT NULL,
        category     VARCHAR(60) NOT NULL,
        image_file   VARCHAR(255) DEFAULT NULL,
        status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_sr_user FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $r = $db->query("SELECT r.*, u.first_name, u.last_name, u.username
                     FROM tblSellRequest r
                     JOIN tblUser u ON r.user_id = u.user_id
                     ORDER BY r.submitted_at DESC");
    if ($r) while ($row = $r->fetch_assoc()) $sellRequests[] = $row;
}
if ($section==='edit_user' && isset($_GET['uid'])) {
    $uid = (int)$_GET['uid'];
    $st  = $db->prepare("SELECT * FROM tblUser WHERE user_id=?");
    if ($st) {
        $st->bind_param('i', $uid); $st->execute();
        $res2 = $st->get_result();
        if ($res2) $editUser = $res2->fetch_assoc();
        $st->close();
    }
}
if ($section==='edit_clothes' && isset($_GET['cid'])) {
    $cid = (int)$_GET['cid'];
    $st  = $db->prepare("SELECT * FROM tblClothes WHERE clothes_id=?");
    if ($st) {
        $st->bind_param('i', $cid); $st->execute();
        $res2 = $st->get_result();
        if ($res2) $editClothes = $res2->fetch_assoc();
        $st->close();
    }
}

$imgFiles = array_map('basename', glob(__DIR__.'/images/*.jpg') ?: []);
$db->close();

include 'includes/header.php';
?>
<div class="admin-layout">

  
  <aside class="admin-sidebar">
    <p class="nav-section">Overview</p>
    <a href="admin.php?section=dashboard" class="<?= $section==='dashboard'?'active':'' ?>">
      <i data-lucide="layout-dashboard" class="icon icon-sm"></i> Dashboard
    </a>
    <p class="nav-section">Manage</p>
    <a href="admin.php?section=users" class="<?= $section==='users'?'active':'' ?>">
      <i data-lucide="users" class="icon icon-sm"></i> All Users
    </a>
    <a href="admin.php?section=clothes" class="<?= $section==='clothes'?'active':'' ?>">
      <i data-lucide="shirt" class="icon icon-sm"></i> Clothing Items
    </a>
    <a href="admin.php?section=orders" class="<?= $section==='orders'?'active':'' ?>">
      <i data-lucide="package" class="icon icon-sm"></i> Orders
    </a>
    <a href="admin.php?section=sell_requests" class="<?= $section==='sell_requests'?'active':'' ?>">
      <i data-lucide="send" class="icon icon-sm"></i> Sell Requests
    </a>
    <p class="nav-section">Create</p>
    <a href="admin.php?section=add_user" class="<?= $section==='add_user'?'active':'' ?>">
      <i data-lucide="user-plus" class="icon icon-sm"></i> Add User
    </a>
    <a href="admin.php?section=add_clothes" class="<?= $section==='add_clothes'?'active':'' ?>">
      <i data-lucide="plus-circle" class="icon icon-sm"></i> Add Clothing
    </a>
    <hr>
    <a href="index.php">
      <i data-lucide="store" class="icon icon-sm"></i> View Store
    </a>
    <a href="admin_logout.php" class="danger">
      <i data-lucide="log-out" class="icon icon-sm"></i> Log out
    </a>
  </aside>

  
  <main class="admin-main">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType==='error'?'error':'success' ?>">
      <i data-lucide="<?= $msgType==='error'?'alert-circle':'check-circle' ?>" class="icon"></i>
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif ?>

    
    <?php if ($section==='dashboard'): ?>
    <h2><i data-lucide="layout-dashboard" class="icon"></i> Dashboard</h2>
    <div class="stat-cards">
      <div class="stat-card"><div class="num"><?= $stats['users']   ?></div><div class="lbl">Total users</div></div>
      <div class="stat-card" style="border-color:#fde68a;"><div class="num" style="color:#d97706;"><?= $stats['pending'] ?></div><div class="lbl">Pending approval</div></div>
      <div class="stat-card"><div class="num"><?= $stats['items']   ?></div><div class="lbl">Clothing items</div></div>
      <div class="stat-card"><div class="num"><?= $stats['orders']  ?></div><div class="lbl">Orders placed</div></div>
      <div class="stat-card" style="border-color:#fde68a;"><div class="num" style="color:#d97706;"><?= $stats['sell_pending'] ?></div><div class="lbl">Sell requests</div></div>
    </div>

    <?php $pending = array_filter($users, fn($u) => $u['user_status']==='pending'); ?>
    <?php if ($pending): ?>
    <h3 style="margin-bottom:.875rem;font-size:1rem;">
      <i data-lucide="clock" class="icon icon-sm"></i> Pending Verifications (<?= count($pending) ?>)
    </h3>
    <table class="data-table">
      <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($pending as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
        <td><span class="badge badge-pending">Pending</span></td>
        <td>
          <form method="POST" action="admin.php" style="display:inline;">
            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="uid"    value="<?= $u['user_id'] ?>">
            <button type="submit" class="btn btn-success btn-sm">
              <i data-lucide="check" class="icon icon-sm"></i> Verify
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="alert alert-success"><i data-lucide="check-circle" class="icon"></i> No pending verifications.</div>
    <?php endif ?>

    
    <?php elseif ($section==='users'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
      <h2 style="margin:0;"><i data-lucide="users" class="icon"></i> All Users</h2>
      <a href="admin.php?section=add_user" class="btn btn-dark btn-sm">
        <i data-lucide="user-plus" class="icon icon-sm"></i> Add User
      </a>
    </div>
    <table class="data-table">
      <thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['user_id'] ?></td>
        <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
        <td><span class="badge badge-<?= $u['user_status'] ?>"><?= ucfirst($u['user_status']) ?></span></td>
        <td style="font-size:.75rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        <td style="display:flex;gap:.3rem;flex-wrap:wrap;padding:.5rem 1rem;">
          <?php if ($u['user_status']==='pending'): ?>
          <form method="POST" action="admin.php" style="display:inline;">
            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="uid" value="<?= $u['user_id'] ?>">
            <button class="btn btn-success btn-sm"><i data-lucide="check" class="icon icon-sm"></i> Verify</button>
          </form>
          <?php endif ?>
          <a href="admin.php?section=edit_user&uid=<?= $u['user_id'] ?>" class="btn btn-outline btn-sm">
            <i data-lucide="pencil" class="icon icon-sm"></i> Edit
          </a>
          <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Delete this user?');">
            <input type="hidden" name="action" value="del_user">
            <input type="hidden" name="uid" value="<?= $u['user_id'] ?>">
            <button class="btn btn-danger btn-sm"><i data-lucide="trash-2" class="icon icon-sm"></i> Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
      </tbody>
    </table>

    
    <?php elseif ($section==='edit_user' && $editUser): ?>
    <h2><i data-lucide="pencil" class="icon"></i> Edit User: <?= htmlspecialchars($editUser['first_name']) ?></h2>
    <form method="POST" action="admin.php?section=users" style="max-width:500px;">
      <input type="hidden" name="action" value="upd_user">
      <input type="hidden" name="uid"    value="<?= $editUser['user_id'] ?>">
      <div class="form-row">
        <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?= htmlspecialchars($editUser['first_name']) ?>" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?= htmlspecialchars($editUser['last_name']) ?>" required></div>
      </div>
      <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>"></div>
      <div class="form-group"><label>Delivery Address</label><input type="text" name="address" value="<?= htmlspecialchars($editUser['address'] ?? '') ?>"></div>
      <div class="form-group">
        <label>Status</label>
        <select name="user_status">
          <?php foreach(['pending','active','seller'] as $s): ?>
          <option value="<?= $s ?>" <?= $editUser['user_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:.5rem;">
        <button type="submit" class="btn btn-dark"><i data-lucide="save" class="icon icon-sm"></i> Save Changes</button>
        <a href="admin.php?section=users" class="btn btn-outline"><i data-lucide="x" class="icon icon-sm"></i> Cancel</a>
      </div>
    </form>

    
    <?php elseif ($section==='clothes'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
      <h2 style="margin:0;"><i data-lucide="shirt" class="icon"></i> Clothing Items</h2>
      <a href="admin.php?section=add_clothes" class="btn btn-dark btn-sm">
        <i data-lucide="plus" class="icon icon-sm"></i> Add Item
      </a>
    </div>
    <table class="data-table">
      <thead><tr><th>Img</th><th>Brand</th><th>Name</th><th>Size</th><th>Cond.</th><th>Price</th><th>Cat.</th><th>Qty</th><th>Seller</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($clothes as $c): ?>
      <tr>
        <td><img src="images/<?= htmlspecialchars($c['image_file'] ?? '') ?>" alt=""></td>
        <td><?= htmlspecialchars($c['brand']) ?></td>
        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($c['item_name']) ?>"><?= htmlspecialchars($c['item_name']) ?></td>
        <td><?= htmlspecialchars($c['size']) ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace(' ','-',$c['condition_'])) ?>"><?= htmlspecialchars($c['condition_']) ?></span></td>
        <td>R<?= number_format($c['sell_price'],2) ?></td>
        <td><?= htmlspecialchars($c['category']) ?></td>
        <td><?= $c['stock_qty'] ?></td>
        <td style="font-size:.75rem;"><?= $c['first_name'] ? htmlspecialchars($c['first_name'].' '.$c['last_name']) : '—' ?></td>
        <td style="display:flex;gap:.3rem;padding:.5rem 1rem;">
          <a href="admin.php?section=edit_clothes&cid=<?= $c['clothes_id'] ?>" class="btn btn-outline btn-sm">
            <i data-lucide="pencil" class="icon icon-sm"></i>
          </a>
          <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Remove this item?');">
            <input type="hidden" name="action" value="del_clothes">
            <input type="hidden" name="cid"    value="<?= $c['clothes_id'] ?>">
            <button class="btn btn-danger btn-sm"><i data-lucide="trash-2" class="icon icon-sm"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
      </tbody>
    </table>

    
    <?php elseif ($section==='edit_clothes' && $editClothes): ?>
    <h2><i data-lucide="pencil" class="icon"></i> Edit Clothing Item</h2>
    <form method="POST" action="admin.php?section=clothes" style="max-width:540px;">
      <input type="hidden" name="action" value="upd_clothes">
      <input type="hidden" name="cid"    value="<?= $editClothes['clothes_id'] ?>">
      <div class="form-row">
        <div class="form-group"><label>Brand *</label><input type="text" name="brand" value="<?= htmlspecialchars($editClothes['brand']) ?>" required></div>
        <div class="form-group"><label>Size *</label>
          <select name="size">
            <?php foreach(['XS','S','M','L','XL','XXL','6','7','8','9','10','11'] as $sz): ?>
            <option value="<?= $sz ?>" <?= $editClothes['size']===$sz?'selected':'' ?>><?= $sz ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Item Name *</label><input type="text" name="item_name" value="<?= htmlspecialchars($editClothes['item_name']) ?>" required></div>
      <div class="form-group"><label>Description</label><textarea name="description"><?= htmlspecialchars($editClothes['description'] ?? '') ?></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Condition</label>
          <select name="condition_">
            <?php foreach(['Excellent','Very Good','Good'] as $c): ?>
            <option value="<?= $c ?>" <?= $editClothes['condition_']===$c?'selected':'' ?>><?= $c ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="form-group"><label>Category</label>
          <select name="category">
            <?php foreach(['Sneakers','Dresses','Knitwear','Tees','Outerwear'] as $c): ?>
            <option value="<?= $c ?>" <?= $editClothes['category']===$c?'selected':'' ?>><?= $c ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Sell Price (R) *</label><input type="number" step="0.01" name="sell_price" value="<?= $editClothes['sell_price'] ?>" required></div>
        <div class="form-group"><label>Stock Qty</label><input type="number" name="stock_qty" min="0" value="<?= $editClothes['stock_qty'] ?>"></div>
      </div>
      <div class="form-group"><label>Image File</label>
        <select name="image_file">
          <?php foreach($imgFiles as $img): ?>
          <option value="<?= $img ?>" <?= $editClothes['image_file']===$img?'selected':'' ?>><?= $img ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:.5rem;">
        <button type="submit" class="btn btn-dark"><i data-lucide="save" class="icon icon-sm"></i> Save Changes</button>
        <a href="admin.php?section=clothes" class="btn btn-outline"><i data-lucide="x" class="icon icon-sm"></i> Cancel</a>
      </div>
    </form>

    
    <?php elseif ($section==='add_user'): ?>
    <h2><i data-lucide="user-plus" class="icon"></i> Add New User</h2>
    <form method="POST" action="admin.php" style="max-width:500px;">
      <input type="hidden" name="action" value="add_user">
      <div class="form-row">
        <div class="form-group"><label>First Name *</label><input type="text" name="first_name" required></div>
        <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" required></div>
      </div>
      <div class="form-group"><label>Username *</label><input type="text" name="username" required></div>
      <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" placeholder="082 123 4567"></div>
      <div class="form-group"><label>Address</label><input type="text" name="address"></div>
      <div class="form-group"><label>Password * (min 8 chars)</label><input type="password" name="password" minlength="8" required></div>
      <div class="form-group"><label>Status</label>
        <select name="user_status">
          <option value="active">Active</option>
          <option value="pending">Pending</option>
          <option value="seller">Seller</option>
        </select>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:.5rem;">
        <button type="submit" class="btn btn-dark"><i data-lucide="user-plus" class="icon icon-sm"></i> Add User</button>
        <a href="admin.php?section=users" class="btn btn-outline"><i data-lucide="x" class="icon icon-sm"></i> Cancel</a>
      </div>
    </form>

    
    <?php elseif ($section==='orders'): ?>
    <h2><i data-lucide="package" class="icon"></i> All Orders</h2>
    <?php if (empty($orders)): ?>
    <div class="alert alert-info"><i data-lucide="info" class="icon"></i> No orders have been placed yet.</div>
    <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Ref</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o):
        $statusColor = match($o['status']) {
          'paid'      => 'badge-active',
          'shipped'   => 'badge-active',
          'cancelled' => 'badge-rejected',
          default     => 'badge-pending',
        };
      ?>
      <tr>
        <td style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($o['order_ref']) ?></td>
        <td>
          <?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?><br>
          <span style="font-size:.72rem;opacity:.6;"><?= htmlspecialchars($o['username']) ?></span>
        </td>
        <td style="text-align:center;"><?= (int)$o['item_count'] ?></td>
        <td><strong>R<?= number_format($o['total_price'],2) ?></strong></td>
        <td><span class="badge <?= $statusColor ?>"><?= ucfirst($o['status']) ?></span></td>
        <td><span class="badge <?= $o['payment_status']==='paid'?'badge-active':'badge-pending' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
        <td style="font-size:.75rem;"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
      </tr>
      <?php endforeach ?>
      </tbody>
    </table>
    <?php endif ?>

    
    <?php elseif ($section==='sell_requests'): ?>
    <h2><i data-lucide="send" class="icon"></i> Sell Requests</h2>
    <?php if (empty($sellRequests)): ?>
    <div class="alert alert-info"><i data-lucide="info" class="icon"></i> No sell requests submitted yet.</div>
    <?php else: ?>
    <table class="data-table">
      <thead><tr><th>ID</th><th>Seller</th><th>Item</th><th>Category</th><th>Size</th><th>Cond.</th><th>Ask Price</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($sellRequests as $sr):
        $badgeMap = ['pending'=>'badge-pending','approved'=>'badge-approved','rejected'=>'badge-rejected'];
        $badge = $badgeMap[$sr['status']] ?? 'badge-pending';
      ?>
      <tr>
        <td><?= $sr['request_id'] ?></td>
        <td>
          <?= htmlspecialchars($sr['first_name'].' '.$sr['last_name']) ?><br>
          <span style="font-size:.72rem;opacity:.6;"><?= htmlspecialchars($sr['username']) ?></span>
        </td>
        <td>
          <strong><?= htmlspecialchars($sr['brand']) ?></strong><br>
          <span style="font-size:.75rem;opacity:.7;"><?= htmlspecialchars($sr['item_name']) ?></span>
        </td>
        <td><?= htmlspecialchars($sr['category']) ?></td>
        <td><?= htmlspecialchars($sr['size']) ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace(' ','-',$sr['condition_'])) ?>"><?= htmlspecialchars($sr['condition_']) ?></span></td>
        <td>R<?= number_format($sr['ask_price'],2) ?></td>
        <td><span class="badge <?= $badge ?>"><?= ucfirst($sr['status']) ?></span></td>
        <td style="font-size:.75rem;"><?= date('d M Y', strtotime($sr['submitted_at'])) ?></td>
        <td style="display:flex;gap:.3rem;padding:.5rem 1rem;">
          <?php if ($sr['status']==='pending'): ?>
          <form method="POST" action="admin.php" style="display:inline;">
            <input type="hidden" name="action" value="approve_sell">
            <input type="hidden" name="rid"    value="<?= $sr['request_id'] ?>">
            <button class="btn btn-success btn-sm"><i data-lucide="check" class="icon icon-sm"></i> Approve</button>
          </form>
          <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Reject this sell request?');">
            <input type="hidden" name="action" value="reject_sell">
            <input type="hidden" name="rid"    value="<?= $sr['request_id'] ?>">
            <button class="btn btn-danger btn-sm"><i data-lucide="x" class="icon icon-sm"></i> Reject</button>
          </form>
          <?php else: ?>
          <span style="font-size:.75rem;opacity:.5;">—</span>
          <?php endif ?>
        </td>
      </tr>
      <?php endforeach ?>
      </tbody>
    </table>
    <?php endif ?>

    
    <?php elseif ($section==='add_clothes'): ?>
    <h2><i data-lucide="plus-circle" class="icon"></i> Add Clothing Item</h2>
    <form method="POST" action="admin.php" style="max-width:540px;">
      <input type="hidden" name="action" value="add_clothes">
      <div class="form-row">
        <div class="form-group"><label>Brand *</label><input type="text" name="brand" required></div>
        <div class="form-group"><label>Size *</label>
          <select name="size">
            <?php foreach(['XS','S','M','L','XL','XXL','6','7','8','9','10','11'] as $sz): ?>
            <option value="<?= $sz ?>"><?= $sz ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Item Name *</label><input type="text" name="item_name" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Condition</label>
          <select name="condition_"><option>Excellent</option><option>Very Good</option><option>Good</option></select>
        </div>
        <div class="form-group"><label>Category</label>
          <select name="category"><option>Sneakers</option><option>Dresses</option><option>Knitwear</option><option>Tees</option><option>Outerwear</option></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Sell Price (R) *</label><input type="number" step="0.01" name="sell_price" required></div>
        <div class="form-group"><label>Stock Qty</label><input type="number" name="stock_qty" value="1" min="1"></div>
      </div>
      <div class="form-group"><label>Image File</label>
        <select name="image_file">
          <?php foreach($imgFiles as $img): ?>
          <option value="<?= $img ?>"><?= $img ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:.5rem;">
        <button type="submit" class="btn btn-dark"><i data-lucide="plus" class="icon icon-sm"></i> Add Item</button>
        <a href="admin.php?section=clothes" class="btn btn-outline"><i data-lucide="x" class="icon icon-sm"></i> Cancel</a>
      </div>
    </form>
    <?php endif ?>

  </main>
</div>

<?php include 'includes/footer.php'; ?>
