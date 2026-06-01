-- ==========================================================
-- SISTEMA DE CONTROL ESCOLAR - PRIMARIA RURAL (MÉXICO)
-- Modelo de Base de Datos
-- MySQL 8.0+
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================================
-- 1. CATÁLOGOS BASE
-- ==========================================================

CREATE TABLE grados (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    numero      TINYINT UNSIGNED NOT NULL COMMENT '1-6',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_grados_numero UNIQUE (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ciclos_escolares (
    id            BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre        VARCHAR(20) NOT NULL COMMENT 'ej. 2024-2025',
    fecha_inicio  DATE NOT NULL,
    fecha_fin     DATE NOT NULL,
    activo        BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Solo 1 activo a la vez',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_ciclos_nombre UNIQUE (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE periodos_evaluacion (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    ciclo_escolar_id BIGINT NOT NULL,
    numero          TINYINT UNSIGNED NOT NULL COMMENT '1, 2 o 3',
    nombre          VARCHAR(50) NOT NULL COMMENT 'ej. 1er Periodo',
    fecha_inicio    DATE NULL,
    fecha_fin       DATE NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_periodo_ciclo_numero UNIQUE (ciclo_escolar_id, numero),
    CONSTRAINT fk_periodos_ciclo FOREIGN KEY (ciclo_escolar_id) REFERENCES ciclos_escolares(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materias (
    id            BIGINT PRIMARY KEY AUTO_INCREMENT,
    grado_id      BIGINT NOT NULL,
    clave_materia VARCHAR(20) NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_materia_grado_clave UNIQUE (grado_id, clave_materia),
    CONSTRAINT uq_materia_grado_nombre UNIQUE (grado_id, nombre),
    CONSTRAINT fk_materias_grado FOREIGN KEY (grado_id) REFERENCES grados(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 2. USUARIOS (Laravel Breeze + campo foto_perfil)
-- ==========================================================

CREATE TABLE users (
    id                BIGINT PRIMARY KEY AUTO_INCREMENT,
    name              VARCHAR(255) NOT NULL,
    email             VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password          VARCHAR(255) NOT NULL,
    foto_perfil       VARCHAR(255) NULL,
    remember_token    VARCHAR(100) NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    email      VARCHAR(255) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    CONSTRAINT pk_password_reset_tokens PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_access_tokens (
    id             BIGINT PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id   BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(255) NOT NULL,
    token          VARCHAR(64) NOT NULL,
    abilities      TEXT NULL,
    last_used_at   TIMESTAMP NULL,
    expires_at     TIMESTAMP NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_pat_token UNIQUE (token),
    INDEX idx_pat_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas de Spatie Laravel Permission

CREATE TABLE permissions (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    guard_name  VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_permissions_name_guard UNIQUE (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    guard_name  VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_roles_name_guard UNIQUE (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE model_has_roles (
    role_id      BIGINT UNSIGNED NOT NULL,
    model_type   VARCHAR(255) NOT NULL,
    model_id     BIGINT UNSIGNED NOT NULL,
    CONSTRAINT pk_model_has_roles PRIMARY KEY (role_id, model_id, model_type),
    CONSTRAINT fk_mhr_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX idx_mhr_model (model_type, model_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type    VARCHAR(255) NOT NULL,
    model_id      BIGINT UNSIGNED NOT NULL,
    CONSTRAINT pk_model_has_permissions PRIMARY KEY (permission_id, model_id, model_type),
    CONSTRAINT fk_mhp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX idx_mhp_model (model_type, model_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id       BIGINT UNSIGNED NOT NULL,
    CONSTRAINT pk_role_has_permissions PRIMARY KEY (permission_id, role_id),
    CONSTRAINT fk_rhp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_rhp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 3. ESTRUCTURA ESCOLAR
-- ==========================================================

CREATE TABLE grupos (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    grado_id        BIGINT NOT NULL,
    ciclo_escolar_id BIGINT NOT NULL,
    docente_id      BIGINT NULL COMMENT 'FK -> users (rol Docente)',
    nombre          VARCHAR(5) NOT NULL COMMENT 'A o B',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_grupo_grado_ciclo_nombre UNIQUE (grado_id, ciclo_escolar_id, nombre),
    CONSTRAINT fk_grupos_grado FOREIGN KEY (grado_id) REFERENCES grados(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_grupos_ciclo FOREIGN KEY (ciclo_escolar_id) REFERENCES ciclos_escolares(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_grupos_docente FOREIGN KEY (docente_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 4. ALUMNOS Y FAMILIAS
-- ==========================================================

CREATE TABLE alumnos (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    curp            VARCHAR(18) NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    primer_apellido VARCHAR(100) NOT NULL,
    segundo_apellido VARCHAR(100) NULL,
    sexo            ENUM('M', 'F') NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    estado          ENUM('activo', 'baja', 'egresado') NOT NULL DEFAULT 'activo',
    grupo_id        BIGINT NOT NULL,
    ciclo_escolar_id BIGINT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_alumno_curp_ciclo UNIQUE (curp, ciclo_escolar_id),
    CONSTRAINT fk_alumnos_grupo FOREIGN KEY (grupo_id) REFERENCES grupos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_alumnos_ciclo FOREIGN KEY (ciclo_escolar_id) REFERENCES ciclos_escolares(id)
        ON DELETE CASCADE,
    INDEX idx_alumnos_curp (curp),
    INDEX idx_alumnos_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personas (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre          VARCHAR(100) NOT NULL,
    primer_apellido VARCHAR(100) NOT NULL,
    segundo_apellido VARCHAR(100) NULL,
    telefono_1      VARCHAR(15) NOT NULL,
    telefono_2      VARCHAR(15) NULL,
    correo          VARCHAR(255) NULL,
    fecha_nacimiento DATE NOT NULL,
    domicilio       TEXT NOT NULL,
    user_id         BIGINT NULL COMMENT 'Solo si es tutor (tiene cuenta en el sistema)',
    foto_perfil     VARCHAR(255) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_personas_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alumno_familia (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    alumno_id   BIGINT NOT NULL,
    persona_id  BIGINT NOT NULL,
    parentesco  ENUM('padre', 'madre', 'tutor_legal') NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_alumno_familia UNIQUE (alumno_id, persona_id),
    CONSTRAINT fk_af_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_af_persona FOREIGN KEY (persona_id) REFERENCES personas(id)
        ON DELETE CASCADE,
    INDEX idx_af_persona (persona_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 5. CALIFICACIONES
-- ==========================================================

CREATE TABLE calificaciones (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    alumno_id   BIGINT NOT NULL,
    materia_id  BIGINT NOT NULL,
    periodo     TINYINT UNSIGNED NOT NULL COMMENT '1, 2 o 3',
    calificacion DECIMAL(4,2) NOT NULL COMMENT '0.00 - 10.00',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_calificacion_alumno_materia_periodo UNIQUE (alumno_id, materia_id, periodo),
    CONSTRAINT fk_calificaciones_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_calificaciones_materia FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE CASCADE,
    INDEX idx_calificaciones_alumno (alumno_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE calificacion_logs (
    id               BIGINT PRIMARY KEY AUTO_INCREMENT,
    calificacion_id  BIGINT NOT NULL,
    user_id          BIGINT NOT NULL,
    valor_anterior   DECIMAL(4,2) NULL COMMENT 'NULL en operacion=creacion',
    valor_nuevo      DECIMAL(4,2) NOT NULL,
    operacion        ENUM('creacion', 'modificacion') NOT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cl_calificacion FOREIGN KEY (calificacion_id) REFERENCES calificaciones(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_cl_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_cl_calificacion (calificacion_id),
    INDEX idx_cl_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 6. ASISTENCIA
-- ==========================================================

CREATE TABLE asistencias (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    alumno_id   BIGINT NOT NULL,
    fecha       DATE NOT NULL,
    estado      ENUM('presente', 'ausente', 'justificado') NOT NULL,
    created_by  BIGINT NOT NULL COMMENT 'Docente que pasó lista',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_asistencia_alumno_fecha UNIQUE (alumno_id, fecha),
    CONSTRAINT fk_asistencias_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_asistencias_created_by FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_asistencias_fecha (fecha),
    INDEX idx_asistencias_alumno (alumno_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE justificantes (
    id            BIGINT PRIMARY KEY AUTO_INCREMENT,
    asistencia_id BIGINT NOT NULL,
    archivo       VARCHAR(255) NOT NULL COMMENT 'Ruta al archivo (foto/PDF)',
    descripcion   TEXT NULL COMMENT 'Motivo opcional escrito',
    validado_por  BIGINT NULL COMMENT 'Director que validó',
    validado_en   DATETIME NULL,
    estado        ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_justificante_asistencia UNIQUE (asistencia_id),
    CONSTRAINT fk_justificantes_asistencia FOREIGN KEY (asistencia_id) REFERENCES asistencias(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_justificantes_validador FOREIGN KEY (validado_por) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 7. BOLETA SEP
-- ==========================================================

CREATE TABLE boleta_observaciones (
    id           BIGINT PRIMARY KEY AUTO_INCREMENT,
    alumno_id    BIGINT NOT NULL,
    ciclo_id     BIGINT NOT NULL,
    observacion  TEXT NOT NULL,
    created_by   BIGINT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bo_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_bo_ciclo FOREIGN KEY (ciclo_id) REFERENCES ciclos_escolares(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_bo_created_by FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_bo_alumno_ciclo (alumno_id, ciclo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 8. SESIONES (Laravel)
-- ==========================================================

CREATE TABLE sessions (
    id            VARCHAR(255) NOT NULL,
    user_id       BIGINT UNSIGNED NULL,
    ip_address    VARCHAR(45) NULL,
    user_agent    TEXT NULL,
    payload       LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    CONSTRAINT pk_sessions PRIMARY KEY (id),
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 9. CACHE (Laravel)
-- ==========================================================

CREATE TABLE cache (
    key         VARCHAR(255) NOT NULL,
    value       MEDIUMTEXT NOT NULL,
    expiration  INT NOT NULL,
    CONSTRAINT pk_cache PRIMARY KEY (key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    key         VARCHAR(255) NOT NULL,
    owner       VARCHAR(255) NOT NULL,
    expiration  INT NOT NULL,
    CONSTRAINT pk_cache_locks PRIMARY KEY (key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 10. JOBS (Laravel)
-- ==========================================================

CREATE TABLE jobs (
    id           BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue        VARCHAR(255) NOT NULL,
    payload      LONGTEXT NOT NULL,
    attempts     TINYINT UNSIGNED NOT NULL,
    reserved_at  INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at   INT UNSIGNED NOT NULL,
    INDEX idx_jobs_queue (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id             VARCHAR(255) NOT NULL,
    name           VARCHAR(255) NOT NULL,
    total_jobs     INT NOT NULL,
    pending_jobs   INT NOT NULL,
    failed_jobs    INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options        MEDIUMTEXT NULL,
    cancelled_at   INT NULL,
    created_at     INT NOT NULL,
    finished_at    INT NULL,
    CONSTRAINT pk_job_batches PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- RESUMEN DE TABLAS
-- ==========================================================
-- Dominio propio (11):
--   grados, ciclos_escolares, periodos_evaluacion,
--   materias, grupos, alumnos, personas, alumno_familia,
--   calificaciones, calificacion_logs, asistencias,
--   justificantes, boleta_observaciones
--
-- Laravel Breeze (4):
--   users, password_reset_tokens, personal_access_tokens, sessions
--
-- Spatie (5):
--   permissions, roles, model_has_roles, model_has_permissions,
--   role_has_permissions
--
-- Laravel infra (3):
--   cache, cache_locks, jobs, job_batches
--
-- Total: ~25 tablas
-- ==========================================================
