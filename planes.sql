CREATE TABLE IF NOT EXISTS ordenes_plan (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    plan_code VARCHAR(32) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    provider VARCHAR(24) NOT NULL DEFAULT 'test',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_plan_orders_user_status (user_id, status),
    CONSTRAINT fk_plan_orders_user
        FOREIGN KEY (user_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plan_usuario (
    user_id INT(11) NOT NULL,
    plan_code VARCHAR(32) NOT NULL DEFAULT 'free',
    status ENUM('active', 'expired') NOT NULL DEFAULT 'active',
    started_at DATETIME NOT NULL,
    expires_at DATETIME NULL,
    order_id BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    KEY idx_user_plans_order (order_id),
    CONSTRAINT fk_user_plans_user
        FOREIGN KEY (user_id) REFERENCES usuarios (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_user_plans_order
        FOREIGN KEY (order_id) REFERENCES ordenes_plan (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;