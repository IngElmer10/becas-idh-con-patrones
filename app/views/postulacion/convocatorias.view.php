<?php
declare(strict_types=1);
?>

<div class="topbar">
  <div class="who">
    <?= htmlspecialchars((string)($_SESSION['user']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> —
    Estudiante
  </div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Convocatorias disponibles</div>

  <?php if (!empty($flash)): ?>
    <div class="alert"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if (!empty($flash_error)): ?>
    <div class="error"><?= htmlspecialchars((string)$flash_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/dashboard">Volver al menú</a>
    <a class="a-btn" href="<?php echo $base_path; ?>/estado">Mis postulaciones</a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Gestión</th>
        <th>Tipo</th>
        <th>Fechas</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)): ?>
        <tr><td colspan="5" class="small">No hay convocatorias abiertas en plazo.</td></tr>
      <?php else: ?>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars((string)$it['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$it['gestion'] ?></td>
            <td><?= htmlspecialchars((string)$it['tipo_beca'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)$it['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string)$it['fecha_fin'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><a class="a-btn" href="<?php echo $base_path; ?>/postulacion/form?id_conv=<?= (int)$it['id'] ?>">Postular</a></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

