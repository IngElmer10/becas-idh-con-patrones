<?php
declare(strict_types=1);

$post = $post ?? [];
$requisitos = $requisitos ?? [];
$docMap = $docMap ?? [];
$errors = $errors ?? [];
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
  <div class="h1">Cargar documentación</div>

  <?php if (!empty($flash)): ?>
    <div class="alert"><?= h((string)$flash) ?></div>
  <?php endif; ?>
  <?php if (!empty($flash_error)): ?>
    <div class="error"><?= h((string)$flash_error) ?></div>
  <?php endif; ?>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/documento">Mis postulaciones</a>
    <a class="a-btn" href="<?php echo $base_path; ?>/estado/ver?id_post=<?= (int)($post['id'] ?? 0) ?>">Ver estado</a>
  </div>

  <div class="small">Postulación #<?= (int)($post['id'] ?? 0) ?> — Estado: <strong><?= h((string)($post['estado'] ?? '')) ?></strong></div>

  <table class="table" style="margin-top:10px">
    <thead>
      <tr>
        <th>Requisito</th>
        <th>Obligatorio</th>
        <th>Estado</th>
        <th>Archivo</th>
        <th>Subir</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($requisitos as $r): ?>
        <?php
          $rid = (int)$r['id'];
          $doc = $docMap[$rid] ?? null;
        ?>
        <tr>
          <td><?= h((string)$r['descripcion']) ?></td>
          <td><?= !empty($r['obligatorio']) ? 'Sí' : 'No' ?></td>
          <td>
            <span class="badge">
              <?= h((string)($doc['estado'] ?? 'pendiente')) ?>
            </span>
            <?php if (!empty($doc['observacion'])): ?>
              <div class="small"><?= h((string)$doc['observacion']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($doc['ruta_archivo'])): ?>
              <a class="a-btn" href="<?= h((string)$doc['ruta_archivo']) ?>" target="_blank" rel="noreferrer">Ver</a>
            <?php else: ?>
              <span class="small">—</span>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" action="<?php echo $base_path; ?>/documento/upload" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center; margin:0;">
              <input type="hidden" name="id_post" value="<?= (int)($post['id'] ?? 0) ?>">
              <input type="hidden" name="id_requisito" value="<?= $rid ?>">
              <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required>
              <label class="small"><input type="checkbox" name="replace" value="1"> reemplazar</label>
              <button class="a-btn" type="submit">Subir</button>
            </form>
            <div class="small">Máx 5MB. Formatos: pdf/jpg/png.</div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

