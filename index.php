<?php

declare(strict_types=1);

// Front Controller (punto de entrada único)
require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/Session.php';
require __DIR__ . '/core/App.php';
require __DIR__ . '/core/Router.php';

// Controllers / Models (sin autoloader para mantenerlo simple)
require __DIR__ . '/app/controllers/auth/AuthController.php';
require __DIR__ . '/app/controllers/convocatoria/ConvocatoriaController.php';
require __DIR__ . '/app/controllers/postulacion/PostulacionController.php';
require __DIR__ . '/app/controllers/documento/DocumentoController.php';
require __DIR__ . '/app/controllers/revision/RevisionController.php';
require __DIR__ . '/app/controllers/evaluacion/EvaluacionController.php';
require __DIR__ . '/app/controllers/estado/EstadoController.php';
require __DIR__ . '/app/controllers/resultado/ResultadoController.php';
require __DIR__ . '/app/models/auth/UsuarioModel.php';
require __DIR__ . '/app/models/convocatoria/ConvocatoriaModel.php';
require __DIR__ . '/app/models/convocatoria/RequisitoModel.php';
require __DIR__ . '/app/models/postulacion/PostulacionModel.php';
require __DIR__ . '/app/models/documento/DocumentoModel.php';
require __DIR__ . '/app/models/evaluacion/EvaluacionModel.php';
require __DIR__ . '/app/models/resultado/ResultadoModel.php';

Session::start();

$router = new Router();

// CU01
$router->get('/', fn() => (new AuthController())->login());
$router->get('/auth/login', fn() => (new AuthController())->login());
$router->post('/auth/login', fn() => (new AuthController())->doLogin());
$router->post('/auth/logout', fn() => (new AuthController())->logout());

// Menú (post-login)
$router->get('/dashboard', fn() => (new AuthController())->dashboard());

// CU02 (solo Admin)
$router->get('/convocatoria', fn() => (new ConvocatoriaController())->index());
$router->get('/convocatoria/create', fn() => (new ConvocatoriaController())->create());
$router->post('/convocatoria/store', fn() => (new ConvocatoriaController())->store());
$router->get('/convocatoria/edit', fn() => (new ConvocatoriaController())->edit());
$router->post('/convocatoria/update', fn() => (new ConvocatoriaController())->update());
$router->post('/convocatoria/close', fn() => (new ConvocatoriaController())->close());

// CU03 - Estudiante
$router->get('/postulacion', fn() => (new PostulacionController())->convocatoriasDisponibles());
$router->get('/postulacion/form', fn() => (new PostulacionController())->form());
$router->post('/postulacion/store', fn() => (new PostulacionController())->store());

// CU04 - Documentos
$router->get('/documento', fn() => (new DocumentoController())->misPostulaciones());
$router->get('/documento/upload', fn() => (new DocumentoController())->upload());
$router->post('/documento/upload', fn() => (new DocumentoController())->doUpload());

// CU05 - Revisor
$router->get('/revision', fn() => (new RevisionController())->bandeja());
$router->get('/revision/ver', fn() => (new RevisionController())->ver());
$router->post('/revision/guardar', fn() => (new RevisionController())->guardar());

// CU06 - Evaluador
$router->get('/evaluacion', fn() => (new EvaluacionController())->bandeja());
$router->get('/evaluacion/evaluar', fn() => (new EvaluacionController())->evaluar());
$router->post('/evaluacion/guardar', fn() => (new EvaluacionController())->guardar());

// CU07 - Estado
$router->get('/estado', fn() => (new EstadoController())->misPostulaciones());
$router->get('/estado/ver', fn() => (new EstadoController())->ver());

// CU08 - Publicar resultados
$router->get('/resultado', fn() => (new ResultadoController())->index());
$router->post('/resultado/publicar', fn() => (new ResultadoController())->publicar());

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

