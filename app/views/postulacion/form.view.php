<?php
declare(strict_types=1);
$conv = $conv ?? [];
$errors = $errors ?? [];
$data = $data ?? [];
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="topbar">
  <div class="who">
    <?= h((string)($_SESSION['user']['nombre'] ?? '')) ?> — Estudiante
  </div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Registrar postulación</div>

  <?php if (!empty($errors)): ?>
    <div class="error">
      <div><strong>Validación:</strong></div>
      <ul class="small">
        <?php foreach ($errors as $msg): ?>
          <li><?= h((string)$msg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card" style="max-width:none; box-shadow:none; margin:10px 0; border:1px solid var(--border);">
    <div class="card-body">
      <div class="small"><strong>Convocatoria</strong></div>
      <div><?= h((string)($conv['nombre'] ?? '')) ?></div>
      <div class="small"><?= (int)($conv['gestion'] ?? 0) ?> — <?= h((string)($conv['tipo_beca'] ?? '')) ?></div>
      <div class="small">Plazo: <?= h((string)($conv['fecha_inicio'] ?? '')) ?> — <?= h((string)($conv['fecha_fin'] ?? '')) ?></div>
    </div>
  </div>

  <form method="post" action="<?php echo $base_path; ?>/postulacion/store">
    <input type="hidden" name="id_convocatoria" value="<?= (int)($conv['id'] ?? 0) ?>">

    <div class="grid">
      <div class="field">
        <div class="small">Teléfono *</div>
        <input class="input" name="telefono" value="<?= h((string)($data['telefono'] ?? '')) ?>" required>
      </div>
      <div class="field">
        <div class="small">Cuenta bancaria *</div>
        <input class="input" name="cuenta_bancaria" value="<?= h((string)($data['cuenta_bancaria'] ?? '')) ?>" required>
      </div>
    </div>

    <div class="field">
      <div class="small">Dirección *</div>
      <input class="input" name="direccion" value="<?= h((string)($data['direccion'] ?? '')) ?>" required>
    </div>

    <div class="field">
      <label class="small">
        <input type="checkbox" name="confirm" value="1" <?= !empty($data['confirm']) ? 'checked' : '' ?>>
        Confirmo el envío de mi postulación (no podré duplicarla para esta convocatoria).
      </label>
    </div>

    <div class="actions">
      <button class="btn" type="submit">Enviar postulación</button>
      <a class="a-btn" href="<?php echo $base_path; ?>/postulacion">Cancelar</a>
    </div>
  </form>
</div>

