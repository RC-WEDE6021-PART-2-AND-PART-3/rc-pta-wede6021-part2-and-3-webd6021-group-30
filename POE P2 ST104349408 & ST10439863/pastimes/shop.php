<?php
// shop.php — product catalogue and cart entry point for Pastimes
// Supports keyword search and category/brand/condition/price filters via GET.
// POST adds a single item to the session cart, then redirects to preserve filters.

$pageTitle = 'Shop';
require_once 'includes/db.php';
require_once 'includes/session.php';

$db = getDB();

// Handle "Add to Cart" POST; redirect back to maintain GET filter state
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $cid = (int)($_POST['clothes_id'] ?? 0);
    if ($cid > 0) {
        $st = $db->prepare("SELECT clothes_id,brand,item_name,sell_price,image_file,size FROM tblClothes WHERE clothes_id=? AND stock_qty>0");
        $st->bind_param('i', $cid);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        if ($row) {
            addToCart($row);
            $_SESSION['cart_flash'] = [
                'name'  => $row['brand'] . ' ' . $row['item_name'],
                'price' => 'R' . number_format($row['sell_price'], 2),
            ];
        }
    }
    
    $qs = http_build_query(array_filter([
        'q'      => $_GET['q']    ?? '',
        'cat'    => $_GET['cat']  ?? '',
        'brand'  => $_GET['brand']?? '',
        'cond'   => $_GET['cond'] ?? '',
        'price'  => $_GET['price']?? '',
        'detail' => $_GET['detail']?? '',
    ]));
    header("Location: shop.php" . ($qs ? "?$qs" : ''));
    exit;
}

$flash = $_SESSION['cart_flash'] ?? null;
unset($_SESSION['cart_flash']);

$fQ     = trim($_GET['q']     ?? '');
$fCat   = trim($_GET['cat']   ?? '');
$fBrand = trim($_GET['brand'] ?? '');
$fCond  = trim($_GET['cond']  ?? '');
$fPrice = trim($_GET['price'] ?? '');
$detailId = (int)($_GET['detail'] ?? 0);

$where  = ['stock_qty >= 0'];
$params = [];
$types  = '';

if ($fQ) {
    $like = "%$fQ%";
    $where[] = "(brand LIKE ? OR item_name LIKE ? OR description LIKE ?)";
    $params  = array_merge($params, [$like, $like, $like]);
    $types  .= 'sss';
}
if ($fCat)   { $where[] = "category = ?";    $params[] = $fCat;   $types .= 's'; }
if ($fBrand) { $where[] = "brand = ?";       $params[] = $fBrand; $types .= 's'; }
if ($fCond)  { $where[] = "condition_ = ?";  $params[] = $fCond;  $types .= 's'; }
if ($fPrice === '0-500')     { $where[] = "sell_price <= 500"; }
elseif ($fPrice === '500-1500') { $where[] = "sell_price BETWEEN 500 AND 1500"; }
elseif ($fPrice === '1500+')    { $where[] = "sell_price > 1500"; }

$sql  = "SELECT c.*, u.first_name, u.last_name FROM tblClothes c LEFT JOIN tblUser u ON c.seller_id=u.user_id WHERE " . implode(' AND ', $where) . " ORDER BY c.clothes_id DESC";
$items = [];
$stmt = $db->prepare($sql);
if ($stmt) {
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($r = $res->fetch_assoc()) $items[] = $r;   
    }
    $stmt->close();
}

$detailItem = null;
if ($detailId > 0) {
    $dst = $db->prepare("SELECT c.*, u.first_name, u.last_name, u.username FROM tblClothes c LEFT JOIN tblUser u ON c.seller_id=u.user_id WHERE c.clothes_id=?");
    if ($dst) {
        $dst->bind_param('i', $detailId);
        $dst->execute();
        $res2 = $dst->get_result();
        if ($res2) $detailItem = $res2->fetch_assoc();
        $dst->close();
    }
}

$db->close();

$cats   = ['All','Sneakers','Dresses','Knitwear','Tees','Outerwear','Trousers','Jeans','Accessories'];
$brands = ['Nike','Adidas','Puma','Zara','Tommy Hilfiger','Calvin Klein','Guess','H&M','Woolworths','Wrangler','Next','Dickies','L.L.Bean','Mocome','Generic'];
$sizes  = ['XS','S','M','L','XL','XXL','6','7','8','9','10','11'];

include 'includes/header.php';
?>

<?php if ($flash): ?>
<div class="toast" id="cartToast">
  <div style="display:flex;align-items:center;gap:.5rem;">
    <i data-lucide="check-circle" class="icon"></i>
    <div>
      <strong><?= htmlspecialchars($flash['name']) ?></strong> added to cart<br>
      <span style="opacity:.7;font-size:.75rem;"><?= htmlspecialchars($flash['price']) ?></span>
      &nbsp;&mdash; <a href="cart.php">View cart</a>
    </div>
  </div>
</div>
<script>setTimeout(()=>{ const el=document.getElementById('cartToast'); if(el) el.style.display='none'; }, 3500);</script>
<?php endif ?>

<section class="section-xs" style="padding-bottom:0;">
  <p class="eyebrow">The catalogue</p>
  <h1 class="section-title" style="margin-top:.35rem;">
    Pre-loved pieces, ready to be <em style="font-weight:300;font-style:normal;color:var(--muted)">re-worn.</em>
  </h1>
  <p class="text-sm text-muted" style="margin-top:.5rem;">
    <?= count($items) ?> piece<?= count($items)!==1?'s':'' ?> available &mdash; every listing condition-rated by the Pastimes team.
  </p>
</section>

<div class="section-xs" style="padding-top:.875rem;">
  <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--bg);padding:1rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;">
    
    <div style="display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;">
      <?php foreach ($cats as $cat): ?>
      <a href="shop.php?<?= http_build_query(array_merge(array_filter(['q'=>$fQ,'brand'=>$fBrand,'cond'=>$fCond,'price'=>$fPrice]), ['cat'=>$cat==='All'?'':$cat])) ?>"
         class="chip <?= ($fCat===($cat==='All'?'':$cat)) || ($cat==='All'&&!$fCat) ? 'chip-active' : '' ?>"
         style="cursor:pointer;text-decoration:none;font-size:.75rem;">
        <?= htmlspecialchars($cat) ?>
      </a>
      <?php endforeach ?>
    </div>
    
    <form method="GET" action="shop.php" style="display:flex;gap:.5rem;align-items:center;">
      <?php if ($fCat):  ?><input type="hidden" name="cat"   value="<?= htmlspecialchars($fCat) ?>"><?php endif ?>
      <?php if ($fBrand):?><input type="hidden" name="brand" value="<?= htmlspecialchars($fBrand) ?>"><?php endif ?>
      <?php if ($fCond): ?><input type="hidden" name="cond"  value="<?= htmlspecialchars($fCond) ?>"><?php endif ?>
      <input name="q" value="<?= htmlspecialchars($fQ) ?>" placeholder="Search brand or piece…"
             style="border-radius:9999px;border:1.5px solid var(--border);background:var(--surface);padding:.45rem 1rem;font-size:.83rem;outline:none;width:200px;">
      <button type="submit" class="btn btn-dark btn-sm">
        <i data-lucide="search" class="icon icon-sm"></i> Search
      </button>
      <?php if ($fQ || $fCat || $fBrand || $fCond || $fPrice): ?>
        <a href="shop.php" class="btn btn-outline btn-sm">
          <i data-lucide="x" class="icon icon-sm"></i> Clear
        </a>
      <?php endif ?>
    </form>
  </div>
</div>

<section class="section-xs" style="padding-top:.875rem;">
  <?php if (empty($items)): ?>
    <div class="text-center" style="padding:4rem 0;">
      <i data-lucide="search-x" class="icon" style="width:3rem;height:3rem;opacity:.3;margin-bottom:1rem;"></i>
      <p class="text-muted">No pieces match your search. <a href="shop.php" style="color:var(--text);font-weight:500;">Clear filters</a></p>
    </div>
  <?php else: ?>
  <div class="product-grid">
    <?php foreach ($items as $item):
      $condClass = match($item['condition_']) {
        'Excellent' => 'badge-excellent',
        'Very Good' => 'badge-very-good',
        default     => 'badge-good',
      };
      $sellerName = $item['first_name'] ? trim($item['first_name'].' '.$item['last_name']) : '';
    ?>
    <article class="product-card">
      <div class="product-img">
        <img src="images/<?= htmlspecialchars($item['image_file'] ?? 'nike_airmax90.jpg') ?>"
             alt="<?= htmlspecialchars($item['brand'].' '.$item['item_name']) ?>"
             loading="lazy">
        <span class="badge <?= $condClass ?> product-cond"><?= htmlspecialchars($item['condition_']) ?></span>
      </div>
      <div class="product-body">
        <p class="product-brand"><?= htmlspecialchars($item['brand']) ?></p>
        <h3 class="product-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;" title="<?= htmlspecialchars($item['item_name']) ?>">
          <?= htmlspecialchars($item['item_name']) ?>
        </h3>
        <p class="product-size">
          Size <?= htmlspecialchars($item['size']) ?>
          <?php if ($sellerName): ?>
            &nbsp;&middot; <span style="color:var(--muted)">by <?= htmlspecialchars($sellerName) ?></span>
          <?php endif ?>
        </p>
        <div class="product-price-row">
          <div>
            <p class="product-from">From</p>
            <p class="product-price">R<?= number_format($item['sell_price'],0) ?></p>
          </div>
          <span style="font-size:.72rem;color:var(--muted);">Qty: <?= $item['stock_qty'] ?></span>
        </div>
        <div class="product-actions">
          
          <a href="shop.php?detail=<?= $item['clothes_id'] ?>&<?= http_build_query(array_filter(['q'=>$fQ,'cat'=>$fCat,'brand'=>$fBrand,'cond'=>$fCond,'price'=>$fPrice])) ?>"
             class="btn btn-outline btn-sm">
            <i data-lucide="eye" class="icon icon-sm"></i> Details
          </a>
          
          <form method="POST" style="flex:1;">
            <?php foreach(['q'=>$fQ,'cat'=>$fCat,'brand'=>$fBrand,'cond'=>$fCond,'price'=>$fPrice] as $k=>$v): if($v): ?>
              <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
            <?php endif; endforeach ?>
            <input type="hidden" name="clothes_id" value="<?= $item['clothes_id'] ?>">
            <button type="submit" name="add_to_cart" value="1"
                    class="btn btn-dark btn-sm w-full"
                    <?= $item['stock_qty'] < 1 ? 'disabled style="opacity:.4;cursor:not-allowed;"' : '' ?>>
              <i data-lucide="shopping-cart" class="icon icon-sm"></i>
              <?= $item['stock_qty'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
            </button>
          </form>
        </div>
      </div>
    </article>
    <?php endforeach ?>
  </div>
  <?php endif ?>
</section>

<?php if ($detailItem): ?>
<div class="modal-bg" id="detailModal" onclick="if(event.target===this) closeModal()">
  <div class="modal-card">
    <button class="modal-close" onclick="closeModal()" aria-label="Close">
      <i data-lucide="x" class="icon icon-sm"></i>
    </button>
    <div class="modal-body">
      
      <div class="modal-img">
        <img src="images/<?= htmlspecialchars($detailItem['image_file'] ?? 'nike_airmax90.jpg') ?>"
             alt="<?= htmlspecialchars($detailItem['item_name']) ?>">
      </div>
      
      <div class="modal-info">
        <div>
          <p class="modal-brand"><?= htmlspecialchars($detailItem['brand']) ?></p>
          <h2 class="modal-name"><?= htmlspecialchars($detailItem['item_name']) ?></h2>
        </div>

        <p class="modal-price">R<?= number_format($detailItem['sell_price'], 2) ?></p>

        <?php if ($detailItem['description']): ?>
        <p class="modal-desc"><?= htmlspecialchars($detailItem['description']) ?></p>
        <?php endif ?>

        
        <div style="display:flex;flex-direction:column;gap:.5rem;">
          <div class="meta-row">
            <i data-lucide="ruler" class="icon icon-sm text-muted"></i>
            <span class="lbl">Size</span>
            <strong><?= htmlspecialchars($detailItem['size']) ?></strong>
          </div>
          <div class="meta-row">
            <i data-lucide="star" class="icon icon-sm text-muted"></i>
            <span class="lbl">Condition</span>
            <span class="badge badge-<?= strtolower(str_replace(' ','-',$detailItem['condition_'])) ?>">
              <?= htmlspecialchars($detailItem['condition_']) ?>
            </span>
          </div>
          <div class="meta-row">
            <i data-lucide="tag" class="icon icon-sm text-muted"></i>
            <span class="lbl">Category</span>
            <span><?= htmlspecialchars($detailItem['category']) ?></span>
          </div>
          <div class="meta-row">
            <i data-lucide="package" class="icon icon-sm text-muted"></i>
            <span class="lbl">Stock</span>
            <span><?= $detailItem['stock_qty'] ?> available</span>
          </div>
          <?php if ($detailItem['first_name']): ?>
          <div class="meta-row">
            <i data-lucide="user" class="icon icon-sm text-muted"></i>
            <span class="lbl">Seller</span>
            <span><?= htmlspecialchars($detailItem['first_name'].' '.$detailItem['last_name']) ?></span>
          </div>
          <?php endif ?>
        </div>

        
        <div>
          <p style="font-size:.8rem;font-weight:600;margin-bottom:.5rem;">Select size</p>
          <div class="size-grid">
            <?php
            
            $shoeSizes = ['6','7','8','9','10','11'];
            $clothSizes = ['XS','S','M','L','XL','XXL'];
            $isShoe = in_array($detailItem['category'], ['Sneakers']);
            $sizeList = $isShoe ? $shoeSizes : $clothSizes;
            $currentSize = $detailItem['size'];
            foreach ($sizeList as $sz):
            ?>
            <button type="button" class="size-btn <?= $sz===$currentSize?'selected':'' ?>"
                    onclick="selectSize(this,'<?= $sz ?>')">
              <?= $sz ?>
            </button>
            <?php endforeach ?>
          </div>
        </div>

        
        <?php if ($detailItem['stock_qty'] > 0): ?>
        <form method="POST" action="shop.php">
          <?php foreach(['q'=>$fQ,'cat'=>$fCat,'brand'=>$fBrand,'cond'=>$fCond,'price'=>$fPrice] as $k=>$v): if($v): ?>
            <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
          <?php endif; endforeach ?>
          <input type="hidden" name="clothes_id" value="<?= $detailItem['clothes_id'] ?>">
          <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <button type="submit" name="add_to_cart" value="1" class="btn btn-dark" style="flex:1;justify-content:center;">
              <i data-lucide="shopping-cart" class="icon icon-sm"></i> Add to Cart — R<?= number_format($detailItem['sell_price'],2) ?>
            </button>
            <a href="shop.php?<?= http_build_query(array_filter(['q'=>$fQ,'cat'=>$fCat,'brand'=>$fBrand,'cond'=>$fCond,'price'=>$fPrice])) ?>"
               class="btn btn-outline">
              <i data-lucide="arrow-left" class="icon icon-sm"></i> Back
            </a>
          </div>
        </form>
        <?php else: ?>
        <div class="alert alert-warn" style="margin:0;">
          <i data-lucide="alert-triangle" class="icon"></i> This item is currently out of stock.
        </div>
        <?php endif ?>

        
        <?php if (!empty($detailItem['seller_id']) && isLoggedIn() && (int)($_SESSION['user_id'] ?? 0) !== (int)$detailItem['seller_id']): ?>
        <a href="messages.php?to=<?= (int)$detailItem['seller_id'] ?>&item=<?= (int)$detailItem['clothes_id'] ?>"
           class="btn btn-outline" style="width:100%;justify-content:center;margin-top:.25rem;">
          <i data-lucide="message-circle" class="icon icon-sm"></i> Contact Seller
        </a>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
<script>
function closeModal() {
  document.getElementById('detailModal').style.display = 'none';
  // Remove detail param from URL without reload
  const url = new URL(window.location);
  url.searchParams.delete('detail');
  history.replaceState({}, '', url);
}
function selectSize(btn, size) {
  document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
}
</script>
<?php endif ?>

<?php include 'includes/footer.php'; ?>
