CREATE TABLE IF NOT EXISTS organizations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_user_id INT(11) NOT NULL,
    name VARCHAR(140) NOT NULL,
    organization_type ENUM('school', 'business', 'mixed') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_organizations_owner (owner_user_id),
    CONSTRAINT fk_organizations_owner
        FOREIGN KEY (owner_user_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_members (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id INT(11) NOT NULL,
    role ENUM('owner', 'admin', 'staff') NOT NULL DEFAULT 'staff',
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_organization_member (organization_id, user_id),
    KEY idx_organization_members_user (user_id),
    CONSTRAINT fk_organization_members_org
        FOREIGN KEY (organization_id) REFERENCES organizations (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_organization_members_user
        FOREIGN KEY (user_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_invitations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    invited_email VARCHAR(150) NOT NULL,
    invited_role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    token_hash CHAR(64) NOT NULL,
    status ENUM('pending', 'accepted', 'cancelled') NOT NULL DEFAULT 'pending',
    invited_by INT(11) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    accepted_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_organization_invitation_token (token_hash),
    KEY idx_organization_invitation_email (invited_email, status),
    KEY idx_organization_invitation_org (organization_id, status),
    CONSTRAINT fk_organization_invitations_org
        FOREIGN KEY (organization_id) REFERENCES organizations (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_organization_invitations_inviter
        FOREIGN KEY (invited_by) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(80) NULL,
    name VARCHAR(140) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    unit VARCHAR(24) NOT NULL DEFAULT 'unidad',
    quantity DECIMAL(12, 2) NOT NULL DEFAULT 0,
    minimum_quantity DECIMAL(12, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inventory_product_sku (organization_id, sku),
    KEY idx_inventory_products_org_name (organization_id, name),
    CONSTRAINT fk_inventory_products_org
        FOREIGN KEY (organization_id) REFERENCES organizations (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_movements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    user_id INT(11) NOT NULL,
    movement_type ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(12, 2) NOT NULL,
    note VARCHAR(300) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inventory_movements_product_date (product_id, created_at),
    KEY idx_inventory_movements_org_date (organization_id, created_at),
    CONSTRAINT fk_inventory_movements_org
        FOREIGN KEY (organization_id) REFERENCES organizations (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_inventory_movements_product
        FOREIGN KEY (product_id) REFERENCES inventory_products (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_inventory_movements_user
        FOREIGN KEY (user_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    student_code VARCHAR(64) NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_organization_student_code (organization_id, student_code),
    KEY idx_organization_students_org_group (organization_id, group_name, full_name),
    CONSTRAINT fk_organization_students_org
        FOREIGN KEY (organization_id) REFERENCES organizations (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_records (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    marked_by INT(11) NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    note VARCHAR(300) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_attendance_date (student_id, attendance_date),
    KEY idx_attendance_org_date (organization_id, attendance_date),
    CONSTRAINT fk_attendance_org
        FOREIGN KEY (organization_id) REFERENCES organizations (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_attendance_student
        FOREIGN KEY (student_id) REFERENCES organization_students (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_attendance_marker
        FOREIGN KEY (marked_by) REFERENCES usuarios (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
