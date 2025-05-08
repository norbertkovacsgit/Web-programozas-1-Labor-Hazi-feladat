<?php
// views/login_form.php

// 1) Adatbázis-csatlakozás és tábla létrehozása
$dbFile = __DIR__ . '/../data/users.db';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("
  CREATE TABLE IF NOT EXISTS users (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    login        TEXT UNIQUE,
    password     TEXT,
    family_name  TEXT,
    given_name   TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
  )
");

// 2) Bejelentkezés feldolgozása
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'login') {
    $login = trim($_POST['login'] ?? '');
    $pw    = $_POST['password'] ?? '';
    if ($login === '') {
        $errors['login'] = 'Add meg a felhasználóneved.';
    }
    if ($pw === '') {
        $errors['password'] = 'Add meg a jelszavad.';
    }
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ?');
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($pw, $user['password'])) {
            // Sikeres belépés → session beállítása, maradunk a főoldalon
            $_SESSION['user'] = [
                'id'     => $user['id'],
                'login'  => $user['login'],
                'family' => $user['family_name'],
                'given'  => $user['given_name'],
            ];
        } else {
            $errors['general'] = 'Hibás belépési adatok.';
        }
    }
}
?>

<form method="post" action="?page=login" novalidate>
  <input type="hidden" name="do" value="login">

  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
  <?php endif; ?>

  <div class="mb-3">
    <label for="login" class="form-label">Felhasználónév</label>
    <input
      type="text"
      class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>"
      id="login"
      name="login"
      value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
    <?php if (isset($errors['login'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars($errors['login']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Jelszó</label>
    <input
      type="password"
      class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
      id="password"
      name="password">
    <?php if (isset($errors['password'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-primary w-100">Belépek</button>
</form>


