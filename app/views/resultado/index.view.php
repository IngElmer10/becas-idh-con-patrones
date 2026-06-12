<?php
declare(strict_types=1);
$convocatorias = $convocatorias ?? [];
?>

<div class="topbar">
  <div class="who"><?= htmlspecialchars((string)($_SESSION['user']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — Administrador</div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Publicar resultados</div>

  <?php if (!empty($flash)): ?><div class="alert"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <?php if (!empty($flash_error)): ?><div class="error"><?= htmlspecialchars((string)$flash_error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/dashboard">Menú</a>
  </div>

  <form method="post" action="<?php echo $base_path; ?>/resultado/publicar" class="card" style="max-width:none; box-shadow:none; border:1px solid var(--border);">
    <div class="card-body">
      <div class="grid">
        <div class="field">
          <div class="small">Convocatoria</div>
          <select class="input" name="id_convocatoria" required>
            <?php foreach ($convocatorias as $c): ?>
              <option value="<?= (int)$c['id'] ?>">
                #<?= (int)$c['id'] ?> — <?= htmlspecialchars((string)$c['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string)$c['estado'], ENT_QUOTES, 'UTF-8') ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="small">Requisito: convocatoria no “abierta” y todas las postulaciones evaluadas.</div>
        </div>
        <div class="field">
          <div class="small">Cupos (seleccionados)</div>
          <input class="input" type="number" name="cupos" min="1" value="10" required>
          <div class="small">Selecciona top N por puntaje.</div>
        </div>
      </div>

      <div class="actions">
        <button class="btn" type="submit" onclick="return confirm('¿Publicar resultados?');">Publicar</button>
      </div>
    </div>
  </form>
</div>

