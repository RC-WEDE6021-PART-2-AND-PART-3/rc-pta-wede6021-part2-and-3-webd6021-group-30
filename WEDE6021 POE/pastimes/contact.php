<?php
$pageTitle = 'Contact';
require_once 'includes/session.php';
$sent = false; $name=$email=$message=''; $errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name=$_POST['name']??''; $email=$_POST['email']??''; $message=$_POST['message']??'';
  if (!$name)   $errors[]='Name required.';
  if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Valid email required.';
  if (!$message) $errors[]='Message required.';
  if (!$errors) { $sent=true; $name=$email=$message=''; }
}
include 'includes/header.php';
?>
<section class="section">
  <p class="eyebrow">Get in touch</p>
  <h1 class="section-title" style="margin-top:.5rem;">Contact Pastimes</h1>
  <p class="text-sm text-muted mt-3" style="max-width:36rem;">Questions about buying, selling or a specific piece? Our team responds within one business day.</p>
</section>
<section style="padding:0 1.5rem 3rem;max-width:640px;">
  <?php if ($sent): ?><div class="alert alert-success"><i data-lucide="check-circle" class="icon"></i> Message sent! We will be in touch soon.</div><?php endif ?>
  <?php foreach($errors as $e): ?><div class="alert alert-error"><i data-lucide="alert-circle" class="icon"></i><?= htmlspecialchars($e) ?></div><?php endforeach ?>
  <form method="POST" style="display:grid;gap:1rem;">
    <div class="form-row">
      <div class="form-group" style="margin:0;"><label><i data-lucide="user" class="icon icon-sm"></i> Name *</label><input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required></div>
      <div class="form-group" style="margin:0;"><label><i data-lucide="mail" class="icon icon-sm"></i> Email *</label><input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></div>
    </div>
    <div class="form-group" style="margin:0;"><label><i data-lucide="message-square" class="icon icon-sm"></i> Message *</label><textarea name="message" rows="6" required><?= htmlspecialchars($message) ?></textarea></div>
    <div><button type="submit" class="btn btn-dark"><i data-lucide="send" class="icon icon-sm"></i> Send Message</button></div>
  </form>
  <div style="margin-top:1.5rem;border-top:1px solid var(--border);padding-top:1.5rem;display:flex;flex-direction:column;gap:.5rem;">
    <p class="text-sm"><i data-lucide="mail" class="icon icon-sm"></i> <strong>Email:</strong> hello@pastimes.co.za</p>
    <p class="text-sm"><i data-lucide="phone" class="icon icon-sm"></i> <strong>Phone:</strong> 011 123 4567</p>
    <p class="text-sm"><i data-lucide="globe" class="icon icon-sm"></i> <strong>Online:</strong> Available 24/7 &mdash; South Africa&rsquo;s pre-loved fashion platform</p>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
