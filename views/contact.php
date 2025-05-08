<?php
// views/contact.php

$errors   = $_REQUEST['_contact_errors'] ?? [];
$name     = $_REQUEST['_contact_old']['name']    ?? '';
$email    = $_REQUEST['_contact_old']['email']   ?? '';
$subject  = $_REQUEST['_contact_old']['subject'] ?? '';
$message  = $_REQUEST['_contact_old']['message'] ?? '';
?>

<article class="contact-form">
  <h1>Kapcsolat</h1>

  <?php if (!empty($errors)): ?>
    <div class="error-summary">Kérjük, javítsd a jelzett hibákat!</div>
  <?php endif; ?>

  <form id="contactForm" method="post" novalidate>
    <input type="hidden" name="do" value="contact">

    <div class="form-group">
      <label for="name">Név</label>
      <input
        type="text"
        id="name"
        name="name"
        value="<?= htmlspecialchars($name) ?>"
      >
      <?php if (isset($errors['name'])): ?>
        <div class="error"><?= htmlspecialchars($errors['name']) ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="email">E-mail</label>
      <input
        type="text"
        id="email"
        name="email"
        value="<?= htmlspecialchars($email) ?>"
      >
      <?php if (isset($errors['email'])): ?>
        <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="subject">Tárgy</label>
      <input
        type="text"
        id="subject"
        name="subject"
        value="<?= htmlspecialchars($subject) ?>"
      >
      <?php if (isset($errors['subject'])): ?>
        <div class="error"><?= htmlspecialchars($errors['subject']) ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="message">Üzenet</label>
      <textarea id="message" name="message"><?= htmlspecialchars($message) ?></textarea>
      <?php if (isset($errors['message'])): ?>
        <div class="error"><?= htmlspecialchars($errors['message']) ?></div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Küldés</button>
  </form>
</article>

<script>
// kliens-oldali validáció
document.getElementById('contactForm').addEventListener('submit', function(e) {
  let errs = {};
  const get = id => document.getElementById(id).value.trim();

  if (!get('name'))    errs.name    = 'A név megadása kötelező.';
  if (!/^[^@]+@[^@]+\.[^@]+$/.test(get('email'))) errs.email = 'Érvényes e-mail szükséges.';
  if (!get('subject')) errs.subject = 'A tárgy megadása kötelező.';
  if (!get('message')) errs.message = 'Az üzenet megadása kötelező.';

  document.querySelectorAll('.error').forEach(el=>el.remove());
  if (Object.keys(errs).length) {
    e.preventDefault();
    for (let k in errs) {
      const grp = document.getElementById(k).closest('.form-group');
      const div = document.createElement('div');
      div.className = 'error';
      div.textContent = errs[k];
      grp.append(div);
    }
  }
});
</script>
