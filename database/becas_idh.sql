-- Sistema Becas IDH (UAGRM) - Esquema base (CU01 + CU02)
-- Motor recomendado: MariaDB/MySQL 10+

CREATE DATABASE IF NOT EXISTS becas_idh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE becas_idh;

-- Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('estudiante','revisor','evaluador','administrador') NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  password_expires_at DATETIME NULL,
  must_reset_password TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Convocatorias
CREATE TABLE IF NOT EXISTS convocatorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  gestion INT NOT NULL,
  tipo_beca VARCHAR(120) NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado ENUM('borrador','abierta','cerrada') NOT NULL DEFAULT 'borrador',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_convocatoria (nombre, gestion, tipo_beca)
);

-- Requisitos/documentos exigidos por convocatoria
CREATE TABLE IF NOT EXISTS requisitos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_convocatoria INT NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  obligatorio TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_req_conv FOREIGN KEY (id_convocatoria) REFERENCES convocatorias(id)
    ON DELETE CASCADE
);

-- Postulaciones (CU03)
CREATE TABLE IF NOT EXISTS postulaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_convocatoria INT NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  cuenta_bancaria VARCHAR(50) NOT NULL,
  estado ENUM(
    'pendiente_documentos',
    'en_revision_inicial',
    'observada',
    'doc_validada',
    'rechazada_doc',
    'apta_evaluacion',
    'evaluada',
    'seleccionado',
    'no_seleccionado'
  ) NOT NULL DEFAULT 'pendiente_documentos',
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_post_user FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
  CONSTRAINT fk_post_conv FOREIGN KEY (id_convocatoria) REFERENCES convocatorias(id),
  UNIQUE KEY uq_post_user_conv (id_usuario, id_convocatoria)
);

-- Documentos adjuntos (CU04 / CU05)
CREATE TABLE IF NOT EXISTS documentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_postulacion INT NOT NULL,
  id_requisito INT NOT NULL,
  ruta_archivo VARCHAR(255) NOT NULL,
  estado ENUM('recibido','valido','observado','rechazado') NOT NULL DEFAULT 'recibido',
  observacion VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_doc_post FOREIGN KEY (id_postulacion) REFERENCES postulaciones(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_doc_req FOREIGN KEY (id_requisito) REFERENCES requisitos(id)
    ON DELETE CASCADE,
  UNIQUE KEY uq_doc_post_req (id_postulacion, id_requisito)
);

-- Evaluaciones (CU06)
CREATE TABLE IF NOT EXISTS evaluaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_postulacion INT NOT NULL,
  id_evaluador INT NOT NULL,
  puntaje DECIMAL(6,2) NOT NULL DEFAULT 0,
  criterios_json JSON NOT NULL,
  observaciones VARCHAR(1000) NULL,
  estado ENUM('borrador','final') NOT NULL DEFAULT 'final',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_post FOREIGN KEY (id_postulacion) REFERENCES postulaciones(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_eval_user FOREIGN KEY (id_evaluador) REFERENCES usuarios(id),
  UNIQUE KEY uq_eval_post (id_postulacion)
);

-- Resultados (CU08)
CREATE TABLE IF NOT EXISTS resultados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_convocatoria INT NOT NULL,
  id_postulacion INT NOT NULL,
  puntaje_final DECIMAL(6,2) NOT NULL DEFAULT 0,
  estado_final ENUM('seleccionado','no_seleccionado') NOT NULL,
  publicado TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_res_conv FOREIGN KEY (id_convocatoria) REFERENCES convocatorias(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_res_post FOREIGN KEY (id_postulacion) REFERENCES postulaciones(id)
    ON DELETE CASCADE,
  UNIQUE KEY uq_res_post (id_postulacion)
);

-- Seed inicial: Admin (password: Admin123!)
-- Nota: por requerimiento, password_hash almacena el texto plano.
INSERT INTO usuarios (codigo, nombre, email, password_hash, rol, activo, password_expires_at, must_reset_password)
VALUES (
  'ADM001',
  'Administrador',
  'admin@uagrm.edu.bo',
  'Admin123!',
  'administrador',
  1,
  DATE_ADD(NOW(), INTERVAL 90 DAY),
  0
)
ON DUPLICATE KEY UPDATE codigo = codigo;

-- Seed: Estudiante (password: Estu123!)
INSERT INTO usuarios (codigo, nombre, email, password_hash, rol, activo, password_expires_at, must_reset_password)
VALUES (
  'STD001',
  'Estudiante Demo',
  'estudiante@uagrm.edu.bo',
  'Estu123!',
  'estudiante',
  1,
  DATE_ADD(NOW(), INTERVAL 90 DAY),
  0
)
ON DUPLICATE KEY UPDATE codigo = codigo;

-- Seed: Revisor (password: Rev123!)
INSERT INTO usuarios (codigo, nombre, email, password_hash, rol, activo, password_expires_at, must_reset_password)
VALUES (
  'REV001',
  'Revisor Demo',
  'revisor@uagrm.edu.bo',
  'Rev123!',
  'revisor',
  1,
  DATE_ADD(NOW(), INTERVAL 90 DAY),
  0
)
ON DUPLICATE KEY UPDATE codigo = codigo;

-- Seed: Evaluador (password: Eval123!)
INSERT INTO usuarios (codigo, nombre, email, password_hash, rol, activo, password_expires_at, must_reset_password)
VALUES (
  'EVA001',
  'Evaluador Demo',
  'evaluador@uagrm.edu.bo',
  'Eval123!',
  'evaluador',
  1,
  DATE_ADD(NOW(), INTERVAL 90 DAY),
  0
)
ON DUPLICATE KEY UPDATE codigo = codigo;

-- Inserts adicionales: Estudiantes (password: 12345678)
INSERT INTO usuarios (codigo, nombre, email, password_hash, rol, activo, password_expires_at, must_reset_password)
VALUES
  ('220164614','CLAROS CALICHO LUZ MARIA','220164614@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('221102655','MAMANI LAURA GRISELDA','221102655@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('220014280','LUNA GARCIA HILDA FATIMA','220014280@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('220109941','QUISPE SARMIENTO KEILA NARAI','220109941@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('220057087','CALLIZAYA MAMANI NADIA CAMILA','220057087@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('220054606','LUQUE ARGANDONA JORGE ANDRE','220054606@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('221063358','AGUILERA SUAREZ MARIA ANGELES','221063358@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('215080671','LEON LOAYZA JAMER','215080671@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('221036687','SANCHEZ AGUIRRE NATALIA ANDREA','221036687@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('222107375','ROJAS LINARES KAREN DAYANA','222107375@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0),
  ('220078831','CHARCA BLANCO ANGEL EUGENIO','220078831@uagrm.edu.bo','12345678','estudiante',1,DATE_ADD(NOW(), INTERVAL 90 DAY),0)
ON DUPLICATE KEY UPDATE codigo = codigo;

