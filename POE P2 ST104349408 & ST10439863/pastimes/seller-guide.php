<?php

$pageTitle = 'Seller Guide';
require_once 'includes/session.php';
include 'includes/header.php';
?>

<section class="section" style="padding-bottom:0;">
  <p class="eyebrow">Sell on Pastimes</p>
  <h1 class="section-title" style="margin-top:.5rem;max-width:620px;">
    Your complete guide to <em style="font-weight:300;font-style:normal;color:var(--muted)">selling pre-loved fashion.</em>
  </h1>
  <p class="text-sm text-muted mt-4" style="max-width:44rem;line-height:1.8;">
    Pastimes makes it easy for South African sellers to list, sell and get paid for their premium branded clothing — safely and transparently.
  </p>
  <?php if (!isLoggedIn()): ?>
  <div class="flex gap-3 mt-6" style="padding-bottom:.5rem;">
    <a href="register.php" class="btn btn-dark">
      <i data-lucide="user-plus" class="icon icon-sm"></i> Create a free account
    </a>
    <a href="login.php" class="btn btn-outline">
      <i data-lucide="log-in" class="icon icon-sm"></i> Sign in
    </a>
  </div>
  <?php endif ?>
</section>

<section style="padding:2rem 1.5rem 0;">
  <p class="eyebrow">How it works</p>
  <h2 class="section-title" style="margin-top:.5rem;margin-bottom:1.5rem;">4 easy steps to start selling</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;max-width:900px;">
    <?php
    $steps = [
      ['user-plus',    '01', 'Register & apply', 'Create a free account and request seller status. Our admin team verifies your profile within 24 hours.'],
      ['camera',       '02', 'List your piece',  'Once approved, add your item — brand, size, condition and photos. Our team reviews every listing before it goes live.'],
      ['shopping-bag', '03', 'Buyer purchases',  'A verified buyer adds your piece to their cart and completes checkout. You\'ll receive an email notification instantly.'],
      ['banknote',     '04', 'You get paid',     'After successful delivery confirmation, your payout is processed within 3–5 business days via EFT.'],
    ];
    foreach ($steps as [$icon, $num, $title, $desc]):
    ?>
    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.25rem;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.875rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="<?= $icon ?>" class="icon" style="width:1.1rem;height:1.1rem;"></i>
        </span>
        <span style="font-size:1.5rem;font-weight:800;color:var(--border);"><?= $num ?></span>
      </div>
      <h3 style="font-size:.95rem;font-weight:700;margin:0 0 .4rem;"><?= $title ?></h3>
      <p class="text-sm text-muted" style="margin:0;line-height:1.7;"><?= $desc ?></p>
    </div>
    <?php endforeach ?>
  </div>
</section>

<section style="padding:2rem 1.5rem 0;">
  <div style="max-width:720px;display:flex;flex-direction:column;gap:1.25rem;">

    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="tag" class="icon" style="width:1.25rem;height:1.25rem;"></i>
        </span>
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">What you can sell</h2>
      </div>
      <div style="font-size:.875rem;line-height:1.75;color:var(--muted);">
        <p>Pastimes accepts pre-loved <strong style="color:var(--text);">branded clothing, footwear and accessories</strong> from well-known labels such as Nike, Adidas, Puma, Tommy Hilfiger, Calvin Klein, Guess, Zara, H&amp;M, Woolworths and similar premium brands.</p>
        <p style="margin-top:.75rem;">Items must be genuine, accurately described and fall into one of our condition categories:</p>
        <ul style="margin:.75rem 0 0 1rem;display:flex;flex-direction:column;gap:.3rem;">
          <li><strong style="color:var(--text);">Excellent</strong> — barely worn, no visible flaws, like new.</li>
          <li><strong style="color:var(--text);">Very Good</strong> — light signs of wear, no damage.</li>
          <li><strong style="color:var(--text);">Good</strong> — moderate wear, minor faults clearly disclosed.</li>
        </ul>
        <p style="margin-top:.75rem;">We do <strong style="color:var(--text);">not</strong> accept counterfeit, damaged beyond repair, or fast-fashion items without a recognised brand.</p>
      </div>
    </div>

    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="percent" class="icon" style="width:1.25rem;height:1.25rem;"></i>
        </span>
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Fees &amp; payouts</h2>
      </div>
      <div style="font-size:.875rem;line-height:1.75;color:var(--muted);">
        <p>Listing on Pastimes is <strong style="color:var(--text);">completely free</strong>. We take a small commission only when your item sells:</p>
        <div style="margin-top:.75rem;display:flex;flex-direction:column;gap:.5rem;">
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:.5rem;">
            <span>Sale price up to R500</span><strong style="color:var(--text);">12% commission</strong>
          </div>
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:.5rem;">
            <span>Sale price R501 – R1 500</span><strong style="color:var(--text);">10% commission</strong>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span>Sale price above R1 500</span><strong style="color:var(--text);">8% commission</strong>
          </div>
        </div>
        <p style="margin-top:.75rem;">Payouts are made via <strong style="color:var(--text);">EFT</strong> to your registered South African bank account within 3–5 business days after delivery is confirmed.</p>
      </div>
    </div>

    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="shield-check" class="icon" style="width:1.25rem;height:1.25rem;"></i>
        </span>
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Seller rules &amp; safety</h2>
      </div>
      <div style="font-size:.875rem;line-height:1.75;color:var(--muted);">
        <p>To keep Pastimes a trustworthy marketplace for everyone, all sellers must:</p>
        <ul style="margin:.75rem 0 0 1rem;display:flex;flex-direction:column;gap:.4rem;">
          <li>List items accurately — honest condition ratings and clear photos.</li>
          <li>Ship within 2 business days of a confirmed sale.</li>
          <li>Not communicate outside of the platform to avoid fraud.</li>
          <li>Accept disputes raised by buyers and cooperate with our team.</li>
          <li>Not list counterfeit or stolen goods (violations result in permanent bans).</li>
        </ul>
        <p style="margin-top:.75rem;">Sellers who repeatedly receive negative buyer feedback may have their listing privileges revoked.</p>
      </div>
    </div>

    
    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--bg);padding:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <p style="font-weight:600;margin:0 0 .25rem;">Ready to start selling?</p>
        <p class="text-sm text-muted" style="margin:0;">Create an account and apply for seller status — it only takes 2 minutes.</p>
      </div>
      <div class="flex gap-3">
        <a href="register.php" class="btn btn-dark">
          <i data-lucide="user-plus" class="icon icon-sm"></i> Register now
        </a>
        <a href="contact.php" class="btn btn-outline">
          <i data-lucide="mail" class="icon icon-sm"></i> Ask us
        </a>
      </div>
    </div>

  </div>
</section>

<section class="section" style="padding-top:2rem;">
  <div style="max-width:700px;margin:0 auto;">
    <p class="eyebrow text-center">Seller FAQs</p>
    <h2 class="section-title text-center" style="margin-top:.5rem;margin-bottom:2rem;">Common questions from sellers</h2>
    <?php
    $faqs = [
      ['How long does verification take?',
       'Admin verification typically takes less than 24 hours on weekdays. You will receive an email confirmation once your seller status is approved.'],
      ['Can I sell shoes as well as clothing?',
       'Yes — we accept footwear, clothing and accessories as long as they are branded and in one of our accepted conditions.'],
      ['What photos do I need for my listing?',
       'At least one clear photo of the item laid flat or on a hanger. Additional photos showing any wear marks, tags, or unique features help buyers trust your listing.'],
      ['What if a buyer claims my item did not arrive?',
       'Always retain your courier proof of postage. If a dispute is raised, share your tracking details with our team and we will mediate fairly.'],
      ['Can I set my own price?',
       'Yes — you set your own selling price. Our team may advise if a price seems inconsistent with market value, but the final decision is always yours.'],
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
