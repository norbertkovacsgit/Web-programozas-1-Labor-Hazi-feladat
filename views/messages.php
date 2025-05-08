<?php
// views/messages.php

$dbFile = __DIR__ . '/../data/contacts.db';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("
  SELECT id, name, email, subject, message, is_guest, created_at
    FROM contacts
   ORDER BY created_at DESC
");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<article class="messages">
  <h1>Beérkezett üzenetek</h1>
  <?php if ($messages): ?>
    <div class="message-grid">
      <?php foreach ($messages as $msg):
        $displayName = htmlspecialchars($msg['name']       ?? '');
        $isGuest     = (int)($msg['is_guest'] ?? 0) === 1;
        $date        = htmlspecialchars($msg['created_at'] ?? '');
        $subject     = htmlspecialchars($msg['subject']    ?? '');
        $body        = nl2br(htmlspecialchars($msg['message'] ?? ''));
        $email       = htmlspecialchars($msg['email']      ?? '');
      ?>
      <div class="message-card">
        <div class="message-header">
          <div class="meta">
            <?= $date ?> &bull; <?= $displayName ?>
            <?php if ($isGuest): ?><small> (Vendég)</small><?php endif; ?>
          </div>
          <h2><?= $subject ?></h2>
        </div>
        <div class="message-body">
          <p><?= $body ?></p>
        </div>
        <div class="message-footer">
          <a href="mailto:<?= $email ?>"><?= $email ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="text-align:center; color: var(--text-color);">
      Még egyetlen üzenet sem érkezett.
    </p>
  <?php endif; ?>
</article>

