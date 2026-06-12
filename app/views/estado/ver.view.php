<?php
declare(strict_types=1);
$post = $post ?? [];
$conv = $conv ?? [];
$requisitos = $requisitos ?? [];
$docMap = $docMap ?? [];
$resultado = $resultado ?? null;
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="topbar">
  <div class="who"><?= h((string)($_SESSION['user']['nombre'] ?? '')) ?> — Estudiante</div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Estado de postulación</div>

  <div class="card" style="max-width:none; box-shadow:none; margin:10px 0; border:1px solid var(--border);">
    <div class="card-body">
      <div><strong>Convocatoria:</strong> <?= h((string)($conv['nombre'] ?? '')) ?></div>
      <div class="small"><?= (int)($conv['gestion'] ?? 0) ?> — <?= h((string)($conv['tipo_beca'] ?? '')) ?></div>
      <div class="small"><strong>Estado postulación:</strong> <?= h((string)($post['estado'] ?? '')) ?></div>
    </div>
  </div>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/documento/upload?id_post=<?= (int)($post['id'] ?? 0) ?>">Documentos</a>
    <a class="a-btn" href="<?php echo $base_path; ?>/estado">Volver</a>
  </div>

  <div class="h1" style="margin-top:16px">Documentos</div>
  <table class="table">
    <thead>
      <tr><th>Requisito</th><th>Obligatorio</th><th>Estado</th><th>Observación</th></tr>
    </thead>
    <tbody>
      <?php foreach ($requisitos as $r): ?>
        <?php $rid = (int)$r['id']; $doc = $docMap[$rid] ?? null; ?>
        <tr>
          <td><?= h((string)$r['descripcion']) ?></td>
          <td><?= !empty($r['obligatorio']) ? 'Sí' : 'No' ?></td>
          <td><span class="badge"><?= h((string)($doc['estado'] ?? 'pendiente')) ?></span></td>
          <td class="small"><?= h((string)($doc['observacion'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="h1" style="margin-top:16px">Resultado</div>
  <?php if ($resultado): ?>
    <div class="alert">
      Estado final: <strong><?= h((string)$resultado['estado_final']) ?></strong> —
      Puntaje final: <strong><?= h((string)$resultado['puntaje_final']) ?></strong>
    </div>
  <?php else: ?>
    <div class="small">La convocatoria aún no tiene resultados publicados, o tu postulación no fue publicada.</div>
  <?php endif; ?>
</div>

