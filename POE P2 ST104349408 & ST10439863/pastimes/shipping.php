<?php

$pageTitle = 'Shipping & Returns';
require_once 'includes/session.php';
include 'includes/header.php';
?>

<section class="section" style="padding-bottom:0;">
  <p class="eyebrow">Policies</p>
  <h1 class="section-title" style="margin-top:.5rem;max-width:600px;">
    Shipping &amp; <em style="font-weight:300;font-style:normal;color:var(--muted)">Returns</em>
  </h1>
  <p class="text-sm text-muted mt-4" style="max-width:44rem;line-height:1.8;">
    Everything you need to know about how we deliver your order and what happens if something isn&rsquo;t right.
  </p>
</section>

<section style="padding:2rem 1.5rem 4rem;">
  <div style="max-width:720px;display:flex;flex-direction:column;gap:2rem;">

    
    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="package" class="icon" style="width:1.25rem;height:1.25rem;"></i>
        </span>
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Delivery Information</h2>
      </div>
      <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;line-height:1.75;color:var(--muted);">
        <p><strong style="color:var(--text);">How does delivery work?</strong><br>
          Pastimes is an online marketplace — once your order is confirmed, the seller packages and ships your item directly to you via a trusted courier service (e.g. Courier Guy, Dawn Wing, Pargo or similar South African couriers).</p>
        <p><strong style="color:var(--text);">Standard delivery (3–5 business days)</strong><br>
          Flat fee of <strong style="color:var(--text);">R99</strong> per order nationwide across South Africa. Available to all major cities and townships.</p>
        <p><strong style="color:var(--text);">Express delivery (1–2 business days)</strong><br>
          Available in Gauteng, Western Cape and KwaZulu-Natal at <strong style="color:var(--text);">R149</strong> per order.</p>
        <p><strong style="color:var(--text);">Free delivery</strong><br>
          Orders over <strong style="color:var(--text);">R1&nbsp;500</strong> qualify for free standard delivery.</p>
        <p><strong style="color:var(--text);">Pargo Pick-up Points</strong><br>
          Select a Pargo drop-off point near you at checkout for as little as <strong style="color:var(--text);">R65</strong>. Collect at your convenience.</p>
        <p><strong style="color:var(--text);">Tracking</strong><br>
          A tracking number will be emailed to you as soon as your order is dispatched. You can track your parcel directly on the courier&rsquo;s website.</p>
      </div>
    </div>

    
    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="clock" class="icon" style="width:1.25rem;height:1.25rem;"></i>
        </span>
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Processing Times</h2>
      </div>
      <div style="font-size:.875rem;line-height:1.75;color:var(--muted);">
        <p>Once payment is confirmed, sellers have <strong style="color:var(--text);">1–2 business days</strong> to pack and hand over your item to the courier. Orders placed before <strong style="color:var(--text);">12:00 SAST</strong> on a weekday are usually dispatched the same day.</p>
        <p style="margin-top:.75rem;">Weekend and public holiday orders are processed on the next available business day.</p>
      </div>
    </div>

    
    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="background:var(--bg);border:1px solid var(--border);border-radius:.5rem;padding:.55rem;display:flex;">
          <i data-lucide="rotate-ccw" class="icon" style="width:1.25rem;height:1.25rem;"></i>
        </span>
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Returns &amp; Disputes</h2>
      </div>
      <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;line-height:1.75;color:var(--muted);">
        <p><strong style="color:var(--text);">Our condition-rating guarantee</strong><br>
          Every item listed on Pastimes is condition-rated by our team — Excellent, Very Good, or Good — before going live. If an item does not match its listed condition, you are entitled to a full return.</p>
        <p><strong style="color:var(--text);">How to request a return</strong><br>
          Contact us at <strong style="color:var(--text);">hello@pastimes.co.za</strong> within <strong style="color:var(--text);">7 days</strong> of delivery. Include your order number, photos of the item, and a brief description of the issue. Our team will respond within one business day.</p>
        <p><strong style="color:var(--text);">Eligibility</strong><br>
          Returns are accepted if the item was misrepresented (wrong condition, wrong size, or not as described). Items must be returned in the same condition they were received, unworn and with original packaging where applicable.</p>
        <p><strong style="color:var(--text);">Change of mind</strong><br>
          Because Pastimes sells pre-loved items from individual verified sellers, we do not accept returns for change of mind. We encourage buyers to read listings carefully before purchasing.</p>
        <p><strong style="color:var(--text);">Refunds</strong><br>
          Approved refunds are processed back to the original payment method within <strong style="color:var(--text);">5–7 business days</strong>.</p>
      </div>
    </div>

    
    <div style="border:1px solid var(--border);border-radius:var(--radius);background:var(--bg);padding:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <p style="font-weight:600;margin:0 0 .25rem;">Still have questions?</p>
        <p class="text-sm text-muted" style="margin:0;">Our team is happy to help with any delivery or returns queries.</p>
      </div>
      <a href="contact.php" class="btn btn-dark">
        <i data-lucide="mail" class="icon icon-sm"></i> Contact us
      </a>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
