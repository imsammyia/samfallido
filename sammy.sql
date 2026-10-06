CREATE TABLE IF NOT EXISTS conversaciones (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    titulo VARCHAR(255) NOT NULL DEFAULT 'Nueva conversación',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_conversaciones_usuario_id (usuario_id),
    KEY idx_conversaciones_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    conversacion_id INT(11) NOT NULL,
    tipo ENUM('user', 'bot') NOT NULL,
    contenido TEXT NOT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mensajes_conversacion_id (conversacion_id),
    CONSTRAINT fk_sammy_mensajes_conversacion
        FOREIGN KEY (conversacion_id) REFERENCES conversaciones (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
