<?php

$pageTitle = 'Home';
require_once 'includes/db.php';
require_once 'includes/session.php';

$db = getDB();

$featured = fetchAll($db, "SELECT * FROM tblClothes ORDER BY clothes_id DESC LIMIT 6");

$heroImg = 'images/PAStimes.jpg';
$darkImg = 'images/adidas_samba2.jpg';
$db->close();

include 'includes/header.php';
?>

<section class="hero-grid">
  <div class="hero-img">
    <img src="<?= htmlspecialchars($heroImg) ?>" alt="Featured pre-loved piece" loading="eager">
    <div class="hero-chips">
      <span class="chip chip-active">Sneakers</span>
      <span class="chip" style="background:rgba(255,255,255,.92)">Dresses</span>
      <span class="chip" style="background:rgba(255,255,255,.92)">Knitwear</span>
    </div>
  </div>
  <div class="hero-right">
    <div>
      <p class="eyebrow">South Africa&#x27;s pre-loved platform</p>
      <h1 class="hero-title" style="margin-top:.5rem;">
        Discover your next favourite <em>piece</em>
      </h1>
      <div class="flex flex-wrap gap-2 mt-4">
        <span class="chip chip-active">Exclusive</span>
        <span class="chip">Branded</span>
        <span class="chip">Pre-loved</span>
        <span class="chip">Verified</span>
      </div>
      <p class="text-sm text-muted mt-4" style="max-width:380px;line-height:1.75;">
        Curated second-hand branded clothing — Nike, Adidas, Woolworths, Zara, Tommy Hilfiger and more. Condition-rated by our South African team.
      </p>
      <div class="flex gap-3 mt-6">
        <a href="shop.php"    class="btn btn-dark"><i data-lucide="shopping-bag" class="icon icon-sm"></i> Browse shop</a>
        <a href="register.php" class="btn btn-outline"><i data-lucide="user-plus" class="icon icon-sm"></i> Join for free</a>
      </div>
    </div>
    <div class="hero-dark">
      <div class="hero-dark-text">
        <span class="tag">Weekly drop</span>
        <h3>Curated Drops Every Thursday</h3>
        <p>Fresh verified pieces from South African sellers — rare colourways, vintage finds and designer labels.</p>
      </div>
      <div class="hero-dark-img">
        <img src="<?= htmlspecialchars($darkImg) ?>" alt="Weekly drop preview">
      </div>
    </div>
  </div>
</section>

<div class="search-bar" style="margin-top:1.25rem;">
  <h3><i data-lucide="search" class="icon icon-sm"></i> Find your fit</h3>
  <form action="shop.php" method="GET" style="margin-top:1rem;">
    <div class="search-fields">
      <div class="search-field">
        <label>Looking for</label>
        <input type="text" name="q" placeholder="Sneakers, dresses…">
      </div>
      <div class="search-field">
        <label>Price range</label>
        <select name="price">
          <option value="">R0 — R5 000</option>
          <option value="0-500">R0 — R500</option>
          <option value="500-1500">R500 — R1 500</option>
          <option value="1500+">R1 500+</option>
        </select>
      </div>
      <div class="search-field">
        <label>Brand</label>
        <select name="brand">
          <option value="">All brands</option>
          <?php foreach(['Nike','Adidas','Puma','Zara','Tommy Hilfiger','Calvin Klein','Guess','H&M','Woolworths'] as $b): ?>
            <option><?= $b ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="search-field">
        <label>Condition</label>
        <select name="cond">
          <option value="">Any condition</option>
          <option>Excellent</option>
          <option>Very Good</option>
          <option>Good</option>
        </select>
      </div>
    </div>
    <div class="search-foot">
      <div class="search-chips">
        <span class="text-xs text-muted" style="margin-right:.25rem;">Browse:</span>
        <?php foreach(['Sneakers','Knitwear','Dresses','Outerwear'] as $c): ?>
          <a href="shop.php?cat=<?= urlencode($c) ?>" class="chip" style="font-size:.72rem;"><?= $c ?></a>
        <?php endforeach ?>
      </div>
      <button type="submit" class="btn btn-dark">
        <i data-lucide="search" class="icon icon-sm"></i> Search shop
      </button>
    </div>
  </form>
</div>

<div class="about-grid">
  <div class="about-img">
    <img src="images/adidas_samba.jpg" alt="Pre-loved fashion South Africa">
  </div>
  <div>
    <p class="eyebrow">Who we are</p>
    <h2 class="section-title" style="margin-top:.5rem;max-width:440px;">
      Pre-loved fashion that <em style="font-weight:300;font-style:normal;color:var(--muted)">tells a story.</em>
    </h2>
    <p class="text-sm text-muted mt-4" style="max-width:400px;line-height:1.75;">
      Pastimes connects South African buyers with verified sellers of premium second-hand branded clothing.
      We condition-rate every piece so you shop with confidence — no surprises.
    </p>
    <a href="about.php" class="btn btn-dark mt-6">
      <i data-lucide="arrow-right" class="icon icon-sm"></i> Our story
    </a>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     eShop Type & Goals Panel — required by POE rubric
     ═══════════════════════════════════════════════════════════════ -->
<div style="margin:2rem 0;padding:2rem;border:2px solid var(--border);border-radius:var(--radius);
            background:var(--surface);">
  <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.25rem;">
    <i data-lucide="store" class="icon" style="width:1.5rem;height:1.5rem;color:var(--accent);"></i>
    <h2 style="font-size:1.1rem;font-weight:700;margin:0;">About This eShop</h2>
  </div>

  <!-- Type of eShop -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
    <div style="background:var(--bg);border:1px solid var(--border);border-radius:.75rem;padding:1rem;">
      <p style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
                color:var(--muted);margin-bottom:.4rem;">Type of eShop</p>
      <p style="font-size:.9rem;font-weight:600;margin:0;">Second-Hand Branded Clothing Marketplace</p>
      <p style="font-size:.78rem;color:var(--muted);margin:.3rem 0 0;">
        A peer-to-peer platform where South African buyers and sellers trade pre-owned branded fashion.
        Sellers submit items for admin verification before they go live; buyers browse, cart, and
        checkout securely through the platform.
      </p>
    </div>

    <!-- Goals -->
    <div style="background:var(--bg);border:1px solid var(--border);border-radius:.75rem;padding:1rem;">
      <p style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
                color:var(--muted);margin-bottom:.4rem;">Platform Goals</p>
      <ul style="font-size:.78rem;color:var(--muted);margin:0;padding-left:1.1rem;line-height:1.9;">
        <li>Enable buyers to discover and purchase verified pre-loved branded clothing.</li>
        <li>Empower sellers to list items and earn from their wardrobe.</li>
        <li>Reduce fashion waste through circular clothing trade.</li>
        <li>Provide secure, admin-verified listings to protect all users.</li>
        <li>Facilitate buyer–seller communication through an in-app messaging system.</li>
      </ul>
    </div>
  </div>

  <!-- Quick feature chips -->
  <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
    <?php
    $features = [
      ['store'       , 'PHP/MySQL e-commerce'],
      ['shield-check', 'Admin-verified listings'],
      ['shopping-cart', 'Session-based cart'],
      ['message-circle', 'Buyer–seller messaging'],
      ['user-check'  , 'Seller request workflow'],
      ['package'     , 'Order history & tracking'],
    ];
    foreach ($features as [$ico, $label]): ?>
    <span style="display:inline-flex;align-items:center;gap:.35rem;background:var(--bg);
                 border:1px solid var(--border);border-radius:9999px;padding:.3rem .75rem;
                 font-size:.75rem;font-weight:500;">
      <i data-lucide="<?= $ico ?>" class="icon" style="width:.9rem;height:.9rem;opacity:.6;"></i>
      <?= htmlspecialchars($label) ?>
    </span>
    <?php endforeach ?>
  </div>
</div>

<div class="stats-strip">
  <div><div class="stat-big">300+</div><div class="stat-lbl">Satisfied customers</div></div>
  <div><div class="stat-big">620+</div><div class="stat-lbl">Pieces sold</div></div>
  <div><div class="stat-big">60+</div><div class="stat-lbl">Verified sellers</div></div>
  <div><div class="stat-big">4.8★</div><div class="stat-lbl">Average rating</div></div>
</div>

<section class="section">
  <div class="flex justify-between items-center flex-wrap gap-3 mb-4">
    <div>
      <p class="eyebrow">What we offer</p>
      <h2 class="section-title">Recent Pieces</h2>
    </div>
    <a href="shop.php" class="btn btn-outline btn-sm">
      View all <i data-lucide="arrow-right" class="icon icon-sm"></i>
    </a>
  </div>
  <div class="product-grid">
    <?php if (empty($featured)): ?>
      
      <?php
      $fallback = [
        ['Nike','Nike Air Jordan 1 Retro','Sneakers','9','Excellent',2499,'nike_jordan1.jpg'],
        ['Adidas','Adidas Originals Samba OG','Sneakers','9','Excellent',950,'adidas_samba.jpg'],
        ['Tommy Hilfiger','Tommy Hilfiger Flag Polo','Tees','L','Very Good',399,'tommy_polo.jpg'],
        ['Zara','Zara Floral Midi Dress','Dresses','M','Excellent',350,'zara_floraldress.jpg'],
        ['Calvin Klein','Calvin Klein Graphic Tee','Tees','M','Excellent',450,'ck_graphictee.jpg'],
        ['Puma','Puma Speedcat OG','Sneakers','10','Excellent',749,'puma_speedcat.jpg'],
      ];
      foreach ($fallback as [$brand,$name,$cat,$size,$cond,$price,$img]):
        $condClass = match($cond) { 'Excellent'=>'badge-excellent','Very Good'=>'badge-very-good',default=>'badge-good' };
      ?>
      <article class="product-card">
        <div class="product-img">
          <img src="images/<?= $img ?>" alt="<?= $brand ?> <?= $name ?>" loading="lazy">
          <span class="badge <?= $condClass ?> product-cond"><?= $cond ?></span>
        </div>
        <div class="product-body">
          <p class="product-brand"><?= $brand ?></p>
          <h3 class="product-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $name ?></h3>
          <p class="product-size">Size <?= $size ?></p>
          <div class="product-price-row">
            <div><p class="product-from">From</p><p class="product-price">R<?= number_format($price,0) ?></p></div>
          </div>
          <div class="product-actions">
            <a href="shop.php" class="btn btn-outline">
              <i data-lucide="eye" class="icon icon-sm"></i> Details
            </a>
          </div>
        </div>
      </article>
      <?php endforeach ?>
    <?php else: ?>
    <?php foreach ($featured as $item): ?>
    <article class="product-card">
      <div class="product-img">
        <img src="images/<?= htmlspecialchars($item['image_file'] ?? 'nike_airmax90.jpg') ?>"
             alt="<?= htmlspecialchars($item['brand'] . ' ' . $item['item_name']) ?>"
             loading="lazy">
        <span class="badge badge-<?= strtolower(str_replace(' ','-',$item['condition_'])) ?> product-cond">
          <?= htmlspecialchars($item['condition_']) ?>
        </span>
      </div>
      <div class="product-body">
        <p class="product-brand"><?= htmlspecialchars($item['brand']) ?></p>
        <h3 class="product-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          <?= htmlspecialchars($item['item_name']) ?>
        </h3>
        <p class="product-size">Size <?= htmlspecialchars($item['size']) ?></p>
        <div class="product-price-row">
          <div><p class="product-from">From</p><p class="product-price">R<?= number_format($item['sell_price'],0) ?></p></div>
        </div>
        <div class="product-actions">
          <a href="shop.php?detail=<?= $item['clothes_id'] ?>" class="btn btn-outline">
            <i data-lucide="eye" class="icon icon-sm"></i> Details
          </a>
        </div>
      </div>
    </article>
    <?php endforeach ?>
    <?php endif ?>
  </div>
</section>

<section style="padding:0 0 3rem;">
  <div class="cta-banner">
    <img src="images/tommy_knit.jpg" alt="">
    <div class="cta-overlay"></div>
    <div class="cta-content">
      <h2>Ready to make pre-loved your next obsession?</h2>
      <p>Browse the latest drops or list your own pieces — Pastimes makes it simple and safe.</p>
      <div class="cta-btns">
        <a href="shop.php"    class="btn btn-white"><i data-lucide="shopping-bag" class="icon icon-sm"></i> Shop now</a>
        <a href="register.php" class="btn btn-outline" style="border-color:rgba(255,255,255,.4);color:#fff;">Join free</a>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div style="max-width:700px;margin:0 auto;">
    <p class="eyebrow text-center">FAQs</p>
    <h2 class="section-title text-center" style="margin-top:.5rem;margin-bottom:2rem;">Frequently asked questions</h2>
    <?php
    $faqs = [
      ['How do I know a piece is authentic?',
       'Every listing is condition-rated and verified by our team before going live. We check stitching, tags, and size labels against brand references.'],
      ['What brands do you carry?',
       'We stock Nike, Adidas, Puma, Zara, Tommy Hilfiger, Calvin Klein, Guess, H&M, Woolworths and many more SA-favourite labels.'],
      ['Do I need an account to buy?',
       'You can browse freely, but you\'ll need a registered and admin-verified account to add to cart and checkout.'],
      ['How do I sell a piece on Pastimes?',
       'Register, then request seller status. Our admin team verifies you within 24 hours. Once approved, you can list items.'],
      ['Can I return an item?',
       'We handle disputes case-by-case. Our condition-rating system is our quality guarantee — listings are accurate or we intervene.'],
    ];
    foreach ($faqs as $i => [$q, $a]):
    ?>
    <div class="faq-item">
      <button class="faq-q <?= $i===0?'open':'' ?>" data-answer="<?= htmlspecialchars($a) ?>" onclick="toggleFaq(this)">
        <span><span style="color:rgba(<?= $i===0?'255,255,255':'0,0,0' ?>,.35);margin-right:.5rem;"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?>.</span><?= htmlspecialchars($q) ?></span>
        <i data-lucide="<?= $i===0?'minus':'plus' ?>" class="icon"></i>
      </button>
      <?php if ($i===0): ?>
      <div class="faq-a"><?= htmlspecialchars($a) ?></div>
      <?php endif ?>
    </div>
    <?php endforeach ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
