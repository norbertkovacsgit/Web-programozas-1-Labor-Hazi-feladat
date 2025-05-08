<?php
declare(strict_types=1);
session_start();

// === 1) KONFIGURÁCIÓ – MINDIG EZ JÖJJÖN LEGTETEJÉRE ===
$config = [
    'site_name'   => 'Web-programozás 1 - Beadandó feladat',
    'menu'        => [
        'home'      => 'Kezdőlap',
        'gallery'   => 'Galéria',
        'contact'   => 'Kapcsolat',
        'messages'  => 'Üzenetek',
    ],
    'footer_menu' => [
        'jogi'      => 'Jogi nyilatkozat',
        'cookie'    => 'Cookie-Kezelés',
    ],
    'default_page' => 'home',
    'extra_pages'  => ['contact_result','logout'],
];

// --- 2) LOGOUT ---
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    session_destroy();
    header('Location:?page=home');
    exit;
}

// --- 3) Ha GET vagy POST érkezik login/register-hez, modalTab, de háttér mindig 'home' ---
$modalTab = null;
// POST login/register esetén (és hibák esetén is marad a modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['do'] ?? '', ['login','register'], true)) {
    $modalTab = $_POST['do'];
    $page     = $config['default_page'];
}
// GET ?page=login vagy ?page=register esetén
elseif (in_array($_GET['page'] ?? '', ['login','register'], true)) {
    $modalTab = $_GET['page'];
    $page     = $config['default_page'];
}
// egyébként sima page routing
else {
    $page = $_GET['page'] ?? $config['default_page'];
}

// === 4) EGYBÉ POST-FELDOLGOZÁSOK (register, login, contact, gallery…) ===
// --- 1) REGISTER ---
$registerErrors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='register') {
    // users.db és tábla létrehozása
    $dbFile = __DIR__.'/data/users.db';
    $pdo = new PDO('sqlite:'.$dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        login TEXT UNIQUE,
        password TEXT,
        family_name TEXT,
        given_name TEXT
      )
    ");

    // validálás
    $login  = trim($_POST['login'] ?? '');
    $pw     = $_POST['password'] ?? '';
    $family = trim($_POST['family'] ?? '');
    $given  = trim($_POST['given'] ?? '');
    if ($login==='')   $registerErrors['login']='Kötelező.';
    if (strlen($pw)<6) $registerErrors['password']='Min.6 karakter.';
    if ($family==='')  $registerErrors['family']='Kötelező.';
    if ($given==='')   $registerErrors['given']='Kötelező.';

    if (empty($registerErrors)) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users(login,password,family_name,given_name) VALUES(?,?,?,?)');
        try {
            $stmt->execute([$login,$hash,$family,$given]);
            header('Location:?page=home&tab=login');
            exit;
        } catch (PDOException $e) {
            $registerErrors['login']='Ez a név foglalt.';
        }
    }
}

// --- 2) LOGIN ---
$loginErrors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='login') {
    $dbFile = __DIR__.'/data/users.db';
    $pdo = new PDO('sqlite:'.$dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $login = trim($_POST['login'] ?? '');
    $pw    = $_POST['password'] ?? '';
    if ($login==='')   $loginErrors['login']='Add meg a nevet.';
    if ($pw==='')      $loginErrors['password']='Add meg a jelszót.';
    if (empty($loginErrors)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE login=?');
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($pw,$user['password'])) {
            $_SESSION['user']=[
                'id'=>$user['id'],
                'login'=>$user['login'],
                'family'=>$user['family_name'],
                'given'=>$user['given_name']
            ];
            header('Location:?page=home');
            exit;
        } else {
            $loginErrors['general']='Hibás név vagy jelszó.';
        }
    }
}

// --- 3) CONTACT ---
$contactErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'contact') {
    // 1) POST-mezők beolvasása + validálás
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '')    $contactErrors['name']    = 'A név megadása kötelező.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $contactErrors['email'] = 'Érvényes e-mail címet adj meg.';
    if ($subject === '') $contactErrors['subject'] = 'A tárgy megadása kötelező.';
    if ($message === '') $contactErrors['message'] = 'Az üzenet megadása kötelező.';

    if (empty($contactErrors)) {
        // 2) DB init + tábla létrehozás
        $dbFile = __DIR__ . '/data/contacts.db';
        if (!is_dir(dirname($dbFile))) mkdir(dirname($dbFile), 0755, true);
        $pdo = new PDO('sqlite:'.$dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ha már létezik a tábla, csak ne hozzuk újjá, de ha nincs
        $pdo->exec("
          CREATE TABLE IF NOT EXISTS contacts (
            id          INTEGER PRIMARY KEY,
            name        TEXT,
            email       TEXT,
            subject     TEXT,
            message     TEXT,
            is_guest    INTEGER DEFAULT 1,
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
          )
        ");

        // 3) Migráció: ha régebbi tábla van, adjuk hozzá az is_guest oszlopot
        try {
          $pdo->exec("ALTER TABLE contacts ADD COLUMN is_guest INTEGER DEFAULT 1");
        } catch (PDOException $e) {
          // ha már ott van, ezt a hibát eldobjuk
        }

        // 4) Vendég vagy bejelentkezett
        if (isset($_SESSION['user'])) {
            $nameToSave = $_SESSION['user']['family'] . ' ' . $_SESSION['user']['given'];
            $isGuest    = 0;
        } else {
            $nameToSave = $name;
            $isGuest    = 1;
        }

        // 5) Beszúrás
        $stmt = $pdo->prepare("
          INSERT INTO contacts
            (name, email, subject, message, is_guest)
          VALUES (?,?,?,?,?)
        ");
        $stmt->execute([
          $nameToSave,
          $email,
          $subject,
          $message,
          $isGuest
        ]);

        // 6) Eredmény session-ben
        $_SESSION['contact_data'] = [
          'name'    => $nameToSave,
          'email'   => $email,
          'subject' => $subject,
          'message' => $message,
          'is_guest'=> $isGuest
        ];

        header('Location:?page=contact_result');
        exit;
    }
}


// --- 4) GALLERY UPLOAD / DELETE ---
$galleryErrors = [];
if (isset($_GET['page']) && $_GET['page']==='gallery') {
    $uploadDir=__DIR__.'/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

    // törlés
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete']) && !empty($_SESSION['user'])) {
        $file=basename($_POST['delete']);
        @unlink($uploadDir.$file);
        header('Location:?page=gallery');
        exit;
    }
    // feltöltés
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['image']) && !empty($_SESSION['user'])) {
        $f=$_FILES['image'];
        $allowed=['image/jpeg','image/png','image/gif'];
        if ($f['error']!==UPLOAD_ERR_OK) {
            $galleryErrors[]='Feltöltési hiba.';
        } elseif (!in_array(mime_content_type($f['tmp_name']),$allowed,true)) {
            $galleryErrors[]='Csak JPG/PNG/GIF.';
        } else {
            $ext=pathinfo($f['name'],PATHINFO_EXTENSION);
            $n=uniqid('img_').".$ext";
            if (move_uploaded_file($f['tmp_name'],$uploadDir.$n)) {
                header('Location:?page=gallery');
                exit;
            }
            $galleryErrors[]='Mentési hiba.';
        }
    }
}

// === 5) ROUTING ===
$allowed = array_merge(
    array_keys($config['menu']),
    array_keys($config['footer_menu']),
    $config['extra_pages']
);
// Ha a $page épp nem engedélyezett (és nem volt login/register), 404
if (!in_array($page, $allowed, true)) {
    http_response_code(404);
    $page = '404';
}

// --- 6) Oldalcím beállítása ---
if (isset($config['menu'][$page])) {
    $pageTitle = $config['menu'][$page];
} elseif (isset($config['footer_menu'][$page])) {
    $pageTitle = $config['footer_menu'][$page];
} elseif ($page === 'contact_result') {
    $pageTitle = 'Üzenet elküldve';
} else {
    $pageTitle = 'Oldal nem található';
}

?><!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – <?= htmlspecialchars($config['site_name']) ?></title>
    
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/legal.css">
    <link rel="stylesheet" href="assets/css/cookie.css">
    <link rel="stylesheet" href="assets/css/slider.css">
	<link rel="stylesheet" href="assets/css/contact.css">
	<link rel="stylesheet" href="assets/css/messages.css">
	<link rel="stylesheet" href="assets/css/auth.css">
  <!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($modalTab !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var m = new bootstrap.Modal(document.getElementById('authModal'));
  m.show();
  // kiválasztjuk a tab-ot
  var selector = (<?= json_encode($modalTab) ?> === 'login')
    ? '#login-tab'
    : '#register-tab';
  document.querySelector(selector).click();
});
</script>
<?php endif; ?>
	
	<?php if ($page === 'gallery'): ?>
  <link rel="stylesheet" href="assets/css/gallery.css">
<?php endif; ?>

<?php if ($page === 'messages'): ?>
  <link rel="stylesheet" href="assets/css/messages.css">
<?php endif; ?>

  <?php if (in_array($page, ['login','register','logout'], true)): ?>
  <link rel="stylesheet" href="assets/css/auth.css">
<?php endif; ?>
</head>
<body>
<header>
  <nav class="main-nav">
    <ul>
      <?php foreach ($config['menu'] as $key => $label): ?>
        <li class="<?= $key === $page ? 'active' : '' ?>">
          <a href="?page=<?= urlencode($key) ?>">
            <?= htmlspecialchars($label) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="auth-buttons">
      <?php if (!empty($_SESSION['user'])): ?>
        <span class="user-info">
          Bejelentkezett:
          <?= htmlspecialchars($_SESSION['user']['family'] . ' ' . $_SESSION['user']['given']) ?>
          (<?= htmlspecialchars($_SESSION['user']['login']) ?>)
        </span>
        <a href="?page=logout" class="btn btn-secondary">Kilépés</a>
      <?php else: ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authModal">
          Belépés
        </button>
      <?php endif; ?>
    </div>
  </nav>
</header>
<?php include __DIR__ . '/views/auth_modal.php'; ?>

    <main>
        <?php
$viewFile = __DIR__ . '/views/' . $page . '.php';
if (is_file($viewFile)) {
    include $viewFile;
} else {
    echo '<h1>404 – Az oldal nem található</h1>';
}
        ?>
    </main>

    <footer>
        <!-- Eredeti lábléc -->
        <p><a href="https://vaszilijedc.hu" target="_blank" rel="noopener">Vissza a vaszilijedc.hu-ra</a></p>

        <!-- Új footer menü -->
        <nav class="footer-nav">
            <ul>
                <?php foreach ($config['footer_menu'] as $key => $label): ?>
                <li>
                    <a href="?page=<?= urlencode($key) ?>"><?= htmlspecialchars($label) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </footer>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

