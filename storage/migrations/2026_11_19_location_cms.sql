-- Location CMS (2026-11-19)
-- 1) location_pages: created only if missing (production may already have it).
-- 2) New optional columns on location_pages: phone, map_url, hero_image, sort_order.
--    The site and admin work without them; once added, they become editable in Admin > Location CMS.
--    If a column already exists, MySQL reports "Duplicate column name" for that line - skip it.
-- 3) location_product_templates: admin-editable content for product + city pages.
-- After running this, seed the data with:  php storage/maintenance/seed_location_data.php --apply
-- Take a database backup first.

CREATE TABLE IF NOT EXISTS location_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    title VARCHAR(191) NOT NULL,
    meta_title VARCHAR(191) NULL,
    meta_description VARCHAR(320) NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'service-area',
    intro TEXT NULL,
    local_note TEXT NULL,
    address VARCHAR(255) NULL,
    areas TEXT NULL,
    scenarios TEXT NULL,
    nearby TEXT NULL,
    faqs MEDIUMTEXT NULL,
    product_content MEDIUMTEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_location_slug (slug),
    KEY idx_location_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE location_pages ADD COLUMN phone VARCHAR(30) NULL AFTER address;
ALTER TABLE location_pages ADD COLUMN map_url VARCHAR(500) NULL AFTER phone;
ALTER TABLE location_pages ADD COLUMN hero_image VARCHAR(255) NULL AFTER map_url;
ALTER TABLE location_pages ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER status;

CREATE TABLE IF NOT EXISTS location_product_templates (
    product_key VARCHAR(60) NOT NULL PRIMARY KEY,
    content MEDIUMTEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    updated_by VARCHAR(191) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
