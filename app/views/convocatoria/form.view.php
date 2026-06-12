<?php
declare(strict_types=1);

$c = $convocatoria ?? [];
$errors = $errors ?? [];
$requisitos = $requisitos ?? [];
$mode = $mode ?? 'create';
$id = $id ?? null;

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="topbar">
  <div class="who">
    <?= h((string)($_SESSION['user']['nombre'] ?? '')) ?> — Administrador
  </div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1"><?= h((string)$title) ?></div>

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

  <form method="post" action="<?php echo $base_path; ?><?= $mode === 'edit' ? '/convocatoria/update' : '/convocatoria/store' ?>">
    <?php if ($mode === 'edit'): ?>
      <input type="hidden" name="id" value="<?= (int)$id ?>">
    <?php endif; ?>

    <div class="grid-3">
      <div class="field">
        <div class="small">Nombre</div>
        <input class="input" name="nombre" value="<?= h((string)($c['nombre'] ?? '')) ?>" required>
      </div>
      <div class="field">
        <div class="small">Gestión</div>
        <input class="input" name="gestion" type="number" min="2000" max="2100" value="<?= (int)($c['gestion'] ?? (int)date('Y')) ?>" required>
      </div>
      <div class="field">
        <div class="small">Tipo de beca</div>
        <input class="input" name="tipo_beca" value="<?= h((string)($c['tipo_beca'] ?? '')) ?>" placeholder="Ej. Alimentación" required>
      </div>
    </div>

    <div class="grid">
      <div class="field">
        <div class="small">Fecha inicio</div>
        <input class="input" name="fecha_inicio" type="date" value="<?= h((string)($c['fecha_inicio'] ?? '')) ?>" required>
      </div>
      <div class="field">
        <div class="small">Fecha fin</div>
        <input class="input" name="fecha_fin" type="date" value="<?= h((string)($c['fecha_fin'] ?? '')) ?>" required>
      </div>
    </div>

    <div class="field">
      <div class="small">Estado</div>
      <select class="input" name="estado">
        <?php
          $estado = (string)($c['estado'] ?? 'borrador');
          foreach (['borrador'=>'Borrador','abierta'=>'Abierta','cerrada'=>'Cerrada'] as $k=>$label):
        ?>
          <option value="<?= h($k) ?>" <?= $estado === $k ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="small">Política: no permitir “Abierta” si la fecha fin ya venció.</div>
    </div>

    <div class="h1" style="margin-top:16px">Requisitos / documentos exigidos</div>
    <div class="small">Debe existir al menos uno y al menos uno marcado como obligatorio.</div>

    <div id="req-list">
      <?php foreach ($requisitos as $i => $r): ?>
        <div class="req-item">
          <input class="input" name="req_descripcion[]" value="<?= h((string)($r['descripcion'] ?? '')) ?>" placeholder="Descripción (ej. Certificado de notas)" required>
          <select class="input" name="req_obligatorio[<?= (int)$i ?>]">
            <option value="1" <?= !empty($r['obligatorio']) ? 'selected' : '' ?>>Obligatorio</option>
            <option value="0" <?= empty($r['obligatorio']) ? 'selected' : '' ?>>Opcional</option>
          </select>
          <button class="icon-btn" type="button" onclick="removeReq(this)" aria-label="Quitar">✕</button>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="actions" style="margin-top:10px">
      <button class="a-btn" type="button" onclick="addReq()">Agregar requisito</button>
    </div>

    <div class="actions" style="margin-top:14px">
      <button class="btn" type="submit"><?= $mode === 'edit' ? 'Guardar cambios' : 'Guardar convocatoria' ?></button>
      <a class="a-btn" href="<?php echo $base_path; ?>/convocatoria">Cancelar</a>
    </div>
  </form>
</div>

<script>
function addReq() {
  const list = document.getElementById('req-list');
  const idx = list.children.length;
  const row = document.createElement('div');
  row.className = 'req-item';
  row.innerHTML = `
    <input class="input" name="req_descripcion[]" placeholder="Descripción (ej. Certificado de notas)" required>
    <select class="input" name="req_obligatorio[${idx}]">
      <option value="1" selected>Obligatorio</option>
      <option value="0">Opcional</option>
    </select>
    <button class="icon-btn" type="button" onclick="removeReq(this)" aria-label="Quitar">✕</button>
  `;
  list.appendChild(row);
}
function removeReq(btn) {
  const row = btn.closest('.req-item');
  if (!row) return;
  const list = document.getElementById('req-list');
  if (list.children.length <= 1) return;
  row.remove();
}
</script>

