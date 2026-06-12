<?php
declare(strict_types=1);
?>

<div class="topbar">
  <div class="who"><?= htmlspecialchars((string)($_SESSION['user']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — Revisor</div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Bandeja de revisión</div>

  <?php if (!empty($flash)): ?><div class="alert"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <?php if (!empty($flash_error)): ?><div class="error"><?= htmlspecialchars((string)$flash_error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/dashboard">Menú</a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Estudiante</th>
        <th>Convocatoria</th>
        <th>Estado</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)): ?>
        <tr><td colspan="5" class="small">No hay postulaciones pendientes.</td></tr>
      <?php else: ?>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= (int)$it['id'] ?></td>
            <td><?= htmlspecialchars((string)$it['estudiante_codigo'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string)$it['estudiante_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)$it['convocatoria_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge"><?= htmlspecialchars((string)$it['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="a-btn" href="<?php echo $base_path; ?>/revision/ver?id_post=<?= (int)$it['id'] ?>">Revisar</a></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

