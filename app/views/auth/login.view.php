<?php
declare(strict_types=1);
?>
<div class="page">
  <div class="brand" aria-label="Universidad">
    <div class="logo">U</div>
    <div class="name">
      Universidad Autónoma<br>
      Gabriel René Moreno
    </div>
  </div>

  <div class="card" role="region" aria-label="Inicio de sesión">
    <div class="card-top">Inicio de sesión</div>
    <div class="card-body">
      <div class="subtitle">Administrativos</div>

      <?php if (!empty($error)): ?>
        <div class="alert"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="post" action="<?php echo $base_path; ?>/auth/login" autocomplete="off">
        <div class="field">
          <input class="input" name="codigo" placeholder="Código" required>
        </div>
        <div class="field">
          <input class="input" name="password" type="password" placeholder="Contraseña" required>
        </div>
        <button class="btn" type="submit">Iniciar Sesión</button>
      </form>

      <a class="link" href="#" onclick="return false;">¿Olvidaste tu contraseña?</a>
    </div>
  </div>
</div>

