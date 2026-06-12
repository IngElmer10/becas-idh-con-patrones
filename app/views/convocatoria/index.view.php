<?php
declare(strict_types=1);
?>

<div class="topbar">
  <div class="who">
    <?= htmlspecialchars((string)($_SESSION['user']['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> —
    Administrador
  </div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Gestionar convocatorias</div>

  <?php if (!empty($flash)): ?>
    <div class="alert"><?= htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if (!empty($flash_error)): ?>
    <div class="error"><?= htmlspecialchars((string)$flash_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/convocatoria/create">Nueva convocatoria</a>
    <a class="a-btn" href="<?php echo $base_path; ?>/dashboard">Volver al menú</a>
  </div>

  <table class="table" aria-label="Listado de convocatorias">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Gestión</th>
        <th>Tipo</th>
        <th>Fechas</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($items)): ?>
      <tr><td colspan="7" class="small">Sin convocatorias registradas.</td></tr>
    <?php else: ?>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= (int)$it['id'] ?></td>
          <td><?= htmlspecialchars((string)$it['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int)$it['gestion'] ?></td>
          <td><?= htmlspecialchars((string)$it['tipo_beca'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <?= htmlspecialchars((string)$it['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?>
            —
            <?= htmlspecialchars((string)$it['fecha_fin'], ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td><span class="badge"><?= htmlspecialchars((string)$it['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
          <td>
            <div class="row">
              <a class="a-btn" href="<?php echo $base_path; ?>/convocatoria/edit?id=<?= (int)$it['id'] ?>">Editar</a>
              <?php if (($it['estado'] ?? '') !== 'cerrada'): ?>
                <form method="post" action="<?php echo $base_path; ?>/convocatoria/close" onsubmit="return confirm('¿Cerrar convocatoria?');">
                  <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                  <button class="a-btn" type="submit">Cerrar</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

