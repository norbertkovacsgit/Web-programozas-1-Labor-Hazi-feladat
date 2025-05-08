<?php
// views/contact_result.php

$data = $_SESSION['contact_data'] ?? [];

$name    = $data['name']    ?? '';
$email   = $data['email']   ?? '';
$subject = $data['subject'] ?? '';
$message = $data['message'] ?? '';
?>
<article class="contact-result">
  <h1>Üzeneted elküldve</h1>
  <?php if (!$name && !$email && !$subject && !$message): ?>
    <p>Nincs megjeleníthető adat. Kérlek, próbáld újra!</p>
  <?php else: ?>
    <div class="result-box">
      <p><strong>Név:</strong>     <?= htmlspecialchars($name)    ?></p>
      <p><strong>E-mail:</strong>  <?= htmlspecialchars($email)   ?></p>
      <p><strong>Tárgy:</strong>   <?= htmlspecialchars($subject) ?></p>
      <p><strong>Üzenet:</strong><br><?= nl2br(htmlspecialchars($message)) ?></p>
    </div>
    <?php unset($_SESSION['contact_data']); ?>
  <?php endif; ?>
</article>
