<?php
declare(strict_types=1);
$post = $post ?? [];
$eval = $eval ?? null;
$errors = $errors ?? [];
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
$c = ['c1'=>0,'c2'=>0,'c3'=>0];
if ($eval && !empty($eval['criterios_json'])) {
  $parsed = json_decode((string)$eval['criterios_json'], true);
  if (is_array($parsed)) {
    $c['c1'] = (float)($parsed['criterio_1'] ?? 0);
    $c['c2'] = (float)($parsed['criterio_2'] ?? 0);
    $c['c3'] = (float)($parsed['criterio_3'] ?? 0);
  }
}
?>

<div class="topbar">
  <div class="who"><?= h((string)($_SESSION['user']['nombre'] ?? '')) ?> — Evaluador</div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Evaluar postulante</div>
  <div class="small">Postulación #<?= (int)($post['id'] ?? 0) ?> — Estado: <strong><?= h((string)($post['estado'] ?? '')) ?></strong></div>

  <?php if (!empty($errors)): ?>
    <div class="error">
      <div><strong>Validación:</strong></div>
      <ul class="small">
        <?php foreach ($errors as $msg): ?><li><?= h((string)$msg) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo $base_path; ?>/evaluacion/guardar">
    <input type="hidden" name="id_post" value="<?= (int)($post['id'] ?? 0) ?>">

    <div class="grid-3">
      <div class="field">
        <div class="small">Criterio 1 (0-100)</div>
        <input class="input" type="number" name="c1" min="0" max="100" step="0.01" value="<?= h((string)$c['c1']) ?>">
      </div>
      <div class="field">
        <div class="small">Criterio 2 (0-100)</div>
        <input class="input" type="number" name="c2" min="0" max="100" step="0.01" value="<?= h((string)$c['c2']) ?>">
      </div>
      <div class="field">
        <div class="small">Criterio 3 (0-100)</div>
        <input class="input" type="number" name="c3" min="0" max="100" step="0.01" value="<?= h((string)$c['c3']) ?>">
      </div>
    </div>

    <div class="field">
      <div class="small">Observaciones</div>
      <input class="input" name="observaciones" value="<?= h((string)($eval['observaciones'] ?? '')) ?>">
    </div>

    <div class="grid">
      <div class="field">
        <div class="small">Guardar como</div>
        <select class="input" name="estado">
          <option value="final" <?= (($eval['estado'] ?? '') === 'final') ? 'selected' : '' ?>>Final</option>
          <option value="borrador" <?= (($eval['estado'] ?? '') === 'borrador') ? 'selected' : '' ?>>Borrador</option>
        </select>
        <div class="small">Si es “Final”, exige criterios completos.</div>
      </div>
      <div class="field">
        <div class="small">Resultado preliminar</div>
        <div class="alert">Puntaje = C1 + C2 + C3</div>
      </div>
    </div>

    <div class="actions" style="margin-top:14px">
      <button class="btn" type="submit">Guardar evaluación</button>
      <a class="a-btn" href="<?php echo $base_path; ?>/evaluacion">Cancelar</a>
    </div>
  </form>
</div>

