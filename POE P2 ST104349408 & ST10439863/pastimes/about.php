<?php
$pageTitle = 'About Us';
require_once 'includes/session.php';
include 'includes/header.php';
?>
<section class="section">
  <p class="eyebrow">Our story</p>
  <h1 class="section-title" style="margin-top:.5rem;max-width:600px;">
    Pre-loved fashion that <em style="font-weight:300;font-style:normal;color:var(--muted)">tells a story.</em>
  </h1>
  <p class="text-sm text-muted mt-4" style="max-width:44rem;line-height:1.8;">
    Pastimes connects South African buyers with verified sellers of premium branded second-hand clothing.
    Every piece is condition-rated by our team — Excellent, Very Good, or Good — so you know exactly what you're getting.
  </p>
</section>
<section style="padding:0 1.5rem 3rem;">
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;">
    <?php foreach(['adidas_samba.jpg','zara_floraldress.jpg','tommy_polo.jpg','puma_speedcat.jpg'] as $img): ?>
    <div style="border-radius:var(--radius);overflow:hidden;aspect-ratio:4/5;">
      <img src="images/<?= $img ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
    </div>
    <?php endforeach ?>
  </div>
</section>
<div class="stats-strip" style="margin:0 1.5rem 3rem;border-radius:var(--radius);border:1px solid var(--border);">
  <div><div class="stat-big">300+</div><div class="stat-lbl">Happy buyers</div></div>
  <div><div class="stat-big">620+</div><div class="stat-lbl">Pieces sold</div></div>
  <div><div class="stat-big">60+</div><div class="stat-lbl">Verified sellers</div></div>
  <div><div class="stat-big">4.8&#9733;</div><div class="stat-lbl">Average rating</div></div>
</div>
<?php include 'includes/footer.php'; ?>
