<?php
declare(strict_types=1);
?>

<div class="topbar">
  <div class="who">
    <?= htmlspecialchars((string)($_SESSION['user']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — Estudiante
  </div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Documentación</div>

  <?php if (!empty($flash)): ?>
    <div class="alert"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if (!empty($flash_error)): ?>
    <div class="error"><?= htmlspecialchars((string)$flash_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/postulacion">Convocatorias</a>
    <a class="a-btn" href="<?php echo $base_path; ?>/estado">Mis postulaciones</a>
    <a class="a-btn" href="<?php echo $base_path; ?>/dashboard">Menú</a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Convocatoria</th>
        <th>Estado</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)): ?>
        <tr><td colspan="3" class="small">Aún no tienes postulaciones.</td></tr>
      <?php else: ?>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars((string)$it['convocatoria_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge"><?= htmlspecialchars((string)$it['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="a-btn" href="<?php echo $base_path; ?>/documento/upload?id_post=<?= (int)$it['id'] ?>">Cargar / Ver</a></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

