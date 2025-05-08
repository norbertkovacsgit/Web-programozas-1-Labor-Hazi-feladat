<?php
// views/register_form.php

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

// 2) Regisztráció feldolgozása (nem léptet be automatikusan)
$errors  = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'register') {
    $login  = trim($_POST['login'] ?? '');
    $pw     = $_POST['password'] ?? '';
    $family = trim($_POST['family'] ?? '');
    $given  = trim($_POST['given'] ?? '');

    if ($login === '') {
        $errors['r_login'] = 'Kötelező a felhasználónév.';
    }
    if (strlen($pw) < 6) {
        $errors['r_password'] = 'Legalább 6 karakter.';
    }
    if ($family === '') {
        $errors['r_family'] = 'Kötelező a családnév.';
    }
    if ($given === '') {
        $errors['r_given'] = 'Kötelező az utónév.';
    }

    if (!$errors) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users(login,password,family_name,given_name) VALUES(?,?,?,?)');
        try {
            $stmt->execute([$login, $hash, $family, $given]);
            $success = 'Sikeres regisztráció! Kérlek, jelentkezz be.';
        } catch (PDOException $e) {
            $errors['r_login'] = 'Ez a felhasználónév már foglalt.';
        }
    }
}
?>
<form method="post" action="?page=login" novalidate>
  <input type="hidden" name="do" value="register">

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="mb-3">
    <label for="r_login" class="form-label">Felhasználónév</label>
    <input type="text"
           class="form-control <?= isset($errors['r_login']) ? 'is-invalid' : '' ?>"
           id="r_login" name="login"
           value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
    <?php if (isset($errors['r_login'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars($errors['r_login']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="r_password" class="form-label">Jelszó</label>
    <input type="password"
           class="form-control <?= isset($errors['r_password']) ? 'is-invalid' : '' ?>"
           id="r_password" name="password">
    <?php if (isset($errors['r_password'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars($errors['r_password']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="r_family" class="form-label">Családnév</label>
    <input type="text"
           class="form-control <?= isset($errors['r_family']) ? 'is-invalid' : '' ?>"
           id="r_family" name="family"
           value="<?= htmlspecialchars($_POST['family'] ?? '') ?>">
    <?php if (isset($errors['r_family'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars($errors['r_family']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="r_given" class="form-label">Utónév</label>
    <input type="text"
           class="form-control <?= isset($errors['r_given']) ? 'is-invalid' : '' ?>"
           id="r_given" name="given"
           value="<?= htmlspecialchars($_POST['given'] ?? '') ?>">
    <?php if (isset($errors['r_given'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars($errors['r_given']) ?></div>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-success w-100">Regisztrálok</button>
</form>

