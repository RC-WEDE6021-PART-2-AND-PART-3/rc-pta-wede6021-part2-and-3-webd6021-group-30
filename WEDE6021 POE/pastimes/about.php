<?php
// about.php — About Us, Terms, Privacy, Dispute Policy, Staying Safe
// Student:  Vukosi Rikhotso       Student No: ST10439408
// Partner:  Theo Golele           Student No: ST10439863
// Group:    Code Couture (Group 02)   Module: WEDE6021
// Institution: IIE Rosebank College, Pretoria
$pageTitle = 'About Us';
require_once 'includes/session.php';
include 'includes/header.php';

// Smooth-scroll to anchor from footer links
?>

<!-- ── About Section ──────────────────────────────────────────────────────── -->
<section id="about" class="section">
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

<?php
// Reusable policy card renderer
function policySection(string $id, string $icon, string $title, string $date, array $sections): void {
    echo "<div id=\"$id\" style=\"scroll-margin-top:5rem;padding:3rem 1.5rem;border-top:1px solid var(--border);\">";
    echo "<div style=\"max-width:760px;\">";
    echo "<div style=\"display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;\">";
    echo "<span style=\"background:var(--surface);border:1px solid var(--border);border-radius:.5rem;padding:.5rem;display:flex;\">
            <i data-lucide=\"$icon\" class=\"icon\" style=\"width:1.25rem;height:1.25rem;\"></i></span>";
    echo "<h2 style=\"font-size:1.3rem;font-weight:700;margin:0;\">$title</h2></div>";
    echo "<p style=\"font-size:.75rem;color:var(--muted);margin-bottom:1.5rem;\">Last updated: $date</p>";
    foreach ($sections as [$heading, $body]) {
        echo "<div style=\"margin-bottom:1.25rem;\">";
        echo "<h3 style=\"font-size:.95rem;font-weight:700;margin-bottom:.4rem;\">$heading</h3>";
        echo "<p style=\"font-size:.875rem;line-height:1.85;color:var(--muted);\">$body</p>";
        echo "</div>";
    }
    echo "</div></div>";
}
?>

<!-- ── Staying Safe ─────────────────────────────────────────────────────────── -->
<?php policySection('safety', 'shield-check', 'Staying Safe on Pastimes', 'June 2026', [
  ['Verified Accounts Only',
   'Every buyer and seller must register and be verified by our admin team before they can trade. We confirm identity through our internal review process, so you always know you are dealing with a real, approved person.'],
  ['Never Share Personal Details',
   'Do not share your phone number, home address, banking details or passwords with other users through our chat system. All payments must go through the official Pastimes checkout — never outside the platform.'],
  ['Condition-Rated Listings',
   'Every listing is reviewed and condition-rated (Excellent, Very Good, or Good) by our team before it goes live. If an item arrives and does not match its description, report it immediately via our Dispute process.'],
  ['Secure Checkout',
   'All transactions are processed through our secure checkout page. Card details are validated client-side and never stored in our database. For extra peace of mind, use the EFT option with a traceable reference.'],
  ['Report Suspicious Activity',
   'If a user pressures you to pay outside the platform, makes threats, or behaves suspiciously, use the Report button on their profile or email us at safety@pastimes.co.za. We take every report seriously.'],
]) ?>

<!-- ── Terms & Conditions ─────────────────────────────────────────────────── -->
<?php policySection('terms', 'file-text', 'Terms &amp; Conditions', 'June 2026', [
  ['1. Acceptance of Terms',
   'By accessing or using Pastimes you agree to be bound by these Terms. If you do not agree, please do not use the platform. We may update these Terms at any time and will notify registered users by email.'],
  ['2. User Accounts',
   'You must be 18 years or older to create an account. You are responsible for keeping your login credentials confidential. Each account is for one individual only — sharing accounts is prohibited.'],
  ['3. Buying &amp; Selling',
   'Sellers warrant that all items listed are genuine branded clothing in the condition stated. Pastimes acts as an intermediary and is not the seller of record. Final sale prices are binding once checkout is completed.'],
  ['4. Prohibited Items',
   'Counterfeit, stolen, or hazardous items are strictly prohibited. Pastimes reserves the right to remove any listing and suspend any account found to be in violation, without prior notice.'],
  ['5. Payments',
   'All payments must be processed through the Pastimes checkout. We accept credit/debit cards, EFT, and cash on delivery. Pastimes is not responsible for payments made outside our platform.'],
  ['6. Returns &amp; Refunds',
   'Returns are handled on a case-by-case basis through our Dispute Policy. Pastimes will mediate between buyer and seller. Refunds, where approved, are issued within 5–10 business days.'],
  ['7. Limitation of Liability',
   'Pastimes is not liable for indirect, incidental, or consequential damages arising from the use of our platform. Our maximum liability to any user shall not exceed the value of the transaction in dispute.'],
]) ?>

<!-- ── Privacy Policy ─────────────────────────────────────────────────────── -->
<?php policySection('privacy', 'lock', 'Privacy Policy', 'June 2026', [
  ['What We Collect',
   'We collect your name, email address, phone number, delivery address, and password (stored as an MD5 hash) when you register. We also log order history, messages exchanged on the platform, and session identifiers.'],
  ['How We Use Your Data',
   'Your data is used solely to operate the Pastimes platform: to verify your account, process orders, facilitate buyer–seller communication, and improve the service. We do not sell your data to third parties.'],
  ['POPIA Compliance',
   'Pastimes complies with the Protection of Personal Information Act (POPIA), Act 4 of 2013. You have the right to access, correct, or request deletion of your personal information at any time by emailing privacy@pastimes.co.za.'],
  ['Data Security',
   'Access to your personal data is restricted to authorised Pastimes administrators. We use prepared statements and parameterised queries to protect against SQL injection. Passwords are never stored in plain text.'],
  ['Cookies',
   'We use PHP session cookies to maintain your login state and shopping cart across page loads. These cookies expire when you close your browser or log out. We do not use tracking or advertising cookies.'],
  ['Third-Party Links',
   'Our platform may contain links to third-party websites. We are not responsible for their privacy practices. Please review the privacy policy of any external site you visit.'],
]) ?>

<!-- ── Dispute Policy ─────────────────────────────────────────────────────── -->
<?php policySection('disputes', 'alert-triangle', 'Dispute Policy', 'June 2026', [
  ['When to Raise a Dispute',
   'Raise a dispute if: (a) an item you received does not match its listed condition or description; (b) an item was not delivered within the agreed timeframe; or (c) a seller refuses to resolve a legitimate complaint.'],
  ['How to Raise a Dispute',
   'Go to My Orders, find the relevant order, and click "Raise Dispute". Describe the issue clearly and attach photos if applicable. Our admin team will acknowledge your dispute within 24 hours.'],
  ['Resolution Process',
   'Pastimes will contact both the buyer and seller within 48 hours of receiving a dispute. We aim to reach a resolution within 7 business days. If no agreement is reached, Pastimes\' decision is final.'],
  ['Outcomes',
   'Depending on the investigation, outcomes may include: a full or partial refund to the buyer; the item being returned to the seller; or account suspension of the party found at fault.'],
  ['Abuse of the Dispute System',
   'Filing false or malicious disputes is prohibited and may result in account suspension. Pastimes reserves the right to charge an administration fee for disputes found to be without merit.'],
]) ?>

<div style="padding:2rem 1.5rem 4rem;">
  <a href="index.php" class="btn btn-outline">
    <i data-lucide="arrow-left" class="icon icon-sm"></i> Back to Home
  </a>
</div>

<?php include 'includes/footer.php'; ?>
