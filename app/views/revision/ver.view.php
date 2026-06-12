<?php
declare(strict_types=1);
$post = $post ?? [];
$requisitos = $requisitos ?? [];
$docMap = $docMap ?? [];
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="topbar">
  <div class="who"><?= h((string)($_SESSION['user']['nombre'] ?? '')) ?> — Revisor</div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Revisar documentación</div>
  <div class="small">Postulación #<?= (int)($post['id'] ?? 0) ?> — Estado actual: <strong><?= h((string)($post['estado'] ?? '')) ?></strong></div>

  <div class="actions">
    <a class="a-btn" href="<?php echo $base_path; ?>/revision">Volver</a>
  </div>

  <form method="post" action="<?php echo $base_path; ?>/revision/guardar">
    <input type="hidden" name="id_post" value="<?= (int)($post['id'] ?? 0) ?>">

    <table class="table" style="margin-top:10px">
      <thead>
        <tr>
          <th>Requisito</th>
          <th>Archivo</th>
          <th>Decisión</th>
          <th>Observación</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requisitos as $r): ?>
          <?php
            $rid = (int)$r['id'];
            $doc = $docMap[$rid] ?? null;
            $ruta = (string)($doc['ruta_archivo'] ?? '');
            $estado = (string)($doc['estado'] ?? 'recibido');
          ?>
          <tr>
            <td><?= h((string)$r['descripcion']) ?></td>
            <td>
              <?php if ($ruta !== ''): ?>
                <a class="a-btn" href="<?= h($ruta) ?>" target="_blank" rel="noreferrer">Ver</a>
              <?php else: ?>
                <span class="small">Faltante</span>
              <?php endif; ?>
            </td>
            <td>
              <select class="input" name="doc_estado[<?= $rid ?>]">
                <?php foreach (['valido'=>'Válido','observado'=>'Observado','rechazado'=>'Rechazado'] as $k=>$lbl): ?>
                  <option value="<?= h($k) ?>" <?= $estado === $k ? 'selected' : '' ?>><?= h($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td>
              <input class="input" name="doc_obs[<?= $rid ?>]" value="<?= h((string)($doc['observacion'] ?? '')) ?>" placeholder="Opcional">
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="actions" style="margin-top:14px">
      <button class="btn" type="submit">Guardar revisión</button>
    </div>
  </form>
</div>

