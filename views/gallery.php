<?php
// views/gallery.php

// 1) jogosultság, upload könyvtár
$canUpload = isset($_SESSION['user']);
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 2) TÖRLÉS kezelése (még a HTML előtt!)
if ($canUpload && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $file = basename($_POST['delete']);
    $path = $uploadDir . $file;
    if (is_file($path)) {
        unlink($path);
    }
    // vissza a galériára
    header('Location: ?page=gallery');
    exit;
}

// 3) FELTÖLTÉS kezelése
$errors = [];
if ($canUpload && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['image']) && !isset($_POST['delete'])) {
    $file = $_FILES['image'];
    $allowed = ['image/jpeg','image/png','image/gif'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Hiba a fájl feltöltése közben.';
    } elseif (!in_array(mime_content_type($file['tmp_name']), $allowed, true)) {
        $errors[] = 'Csak JPG, PNG vagy GIF fájlok engedélyezettek.';
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('img_') . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
        } else {
            $errors[] = 'Nem sikerült elmenteni a fájlt.';
        }
    }
}

// 4) képek beolvasása
$images = array_values(array_filter(scandir($uploadDir), function($f) use ($uploadDir) {
    return is_file($uploadDir . $f) && preg_match('/\.(jpe?g|png|gif)$/i', $f);
}));
?>
<article class="gallery container">
  <h1>Galéria</h1>

  <?php if ($canUpload): ?>
    <section class="upload-form">
      <h2>Kép feltöltése</h2>
      <?php if ($errors): ?>
        <ul class="errors">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" class="btn btn-primary">Feltöltés</button>
      </form>
    </section>
  <?php else: ?>
    <p>Kérlek,
      <a href="#" data-bs-toggle="modal" data-bs-target="#authModal" class="btn btn-link p-0 align-baseline">
        jelentkezz be
      </a>
      a kép feltöltéséhez.
    </p>
  <?php endif; ?>

  <section class="image-slider-section">
    <h2>Elérhető képek</h2>

    <?php if (empty($images)): ?>
      <p>Még nincs egyetlen kép sem.</p>
    <?php else: ?>
      <div class="image-slider">
        <button class="slider-nav prev" aria-label="Előző kép">&larr;</button>
        <div class="slides">
          <?php foreach ($images as $img): ?>
            <div class="slide">
              <img src="uploads/<?= rawurlencode($img) ?>" alt="">
            </div>
          <?php endforeach; ?>
        </div>
        <button class="slider-nav next" aria-label="Következő kép">&rarr;</button>
      </div>

     <?php if ($canUpload): ?>
        <form id="deleteForm" method="post" class="my-4">
          <input type="hidden" name="delete" id="deleteInput" value="">
          <button type="submit" class="btn btn-danger">Törlés</button>
        </form>
     <?php endif; ?>
    <?php endif; ?>
  </section>
</article>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const slider = document.querySelector('.image-slider');
  if (!slider) return;

  const slides = slider.querySelector('.slides');
  const prevBtn = slider.querySelector('.slider-nav.prev');
  const nextBtn = slider.querySelector('.slider-nav.next');
  const images = <?= json_encode($images) ?>;
  const deleteInput = document.getElementById('deleteInput');

  let idx = 0;
  function update() {
    slides.style.transform = `translateX(-${idx * 100}%)`;
    deleteInput.value = images[idx];
  }

  prevBtn.addEventListener('click', () => {
    idx = (idx - 1 + images.length) % images.length;
    update();
  });
  nextBtn.addEventListener('click', () => {
    idx = (idx + 1) % images.length;
    update();
  });

  update();
});
</script>
