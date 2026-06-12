<?php
declare(strict_types=1);
$rolLabel = [
  'estudiante' => 'Estudiante',
  'revisor' => 'Revisor',
  'evaluador' => 'Evaluador',
  'administrador' => 'Administrador',
][(string)($rol ?? '')] ?? 'Usuario';
?>

<div class="topbar">
  <div class="who">
    <?= htmlspecialchars((string)($user['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?> —
    <?= htmlspecialchars($rolLabel, ENT_QUOTES, 'UTF-8') ?>
  </div>
  <form method="post" action="<?php echo $base_path; ?>/auth/logout">
    <button class="logout" type="submit">Cerrar sesión</button>
  </form>
</div>

<div class="container">
  <div class="h1">Menú</div>

  <div class="actions">
    <?php if (($rol ?? '') === 'administrador'): ?>
      <a class="a-btn" href="<?php echo $base_path; ?>/convocatoria">Gestionar convocatorias</a>
      <a class="a-btn" href="<?php echo $base_path; ?>/resultado">Publicar resultados</a>
    <?php endif; ?>

    <?php if (($rol ?? '') === 'estudiante'): ?>
      <a class="a-btn" href="<?php echo $base_path; ?>/postulacion">Registrar postulación</a>
      <a class="a-btn" href="<?php echo $base_path; ?>/documento">Cargar documentación</a>
      <a class="a-btn" href="<?php echo $base_path; ?>/estado">Consultar estado</a>
    <?php endif; ?>

    <?php if (($rol ?? '') === 'revisor'): ?>
      <a class="a-btn" href="<?php echo $base_path; ?>/revision">Revisar documentación</a>
    <?php endif; ?>

    <?php if (($rol ?? '') === 'evaluador'): ?>
      <a class="a-btn" href="<?php echo $base_path; ?>/evaluacion">Evaluar postulante</a>
    <?php endif; ?>
  </div>
</div>

