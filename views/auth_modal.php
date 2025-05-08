<?php
// views/auth_modal.php
?>
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="authModalLabel">Belépés / Regisztráció</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <ul class="nav nav-tabs mb-3" id="authTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#loginPane" type="button" role="tab">Belépés</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#registerPane" type="button" role="tab">Regisztráció</button>
          </li>
        </ul>
        <div class="tab-content" id="authTabContent">
          <div class="tab-pane fade show active" id="loginPane" role="tabpanel">
            <?php include __DIR__ . '/login_form.php'; ?>
          </div>
          <div class="tab-pane fade" id="registerPane" role="tabpanel">
            <?php include __DIR__ . '/register_form.php'; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
