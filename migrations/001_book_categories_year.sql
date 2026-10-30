-- Additive migration for an existing database. Run once, after a private backup.
-- MariaDB 10.4+ / MySQL 8.0.16+. DDL commits implicitly. Inspect partial state on failure.
CREATE TABLE categories (
    category_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(60) NOT NULL,
    UNIQUE KEY uq_categories_name (category_name),
    CONSTRAINT ck_category_name CHECK (CHAR_LENGTH(TRIM(category_name)) BETWEEN 1 AND 60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE books
    ADD COLUMN category_id BIGINT UNSIGNED NULL,
    ADD COLUMN publication_year SMALLINT UNSIGNED NULL,
    ADD KEY ix_books_category (category_id),
    ADD KEY ix_books_year (publication_year),
    ADD CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
    ADD CONSTRAINT ck_books_year CHECK (publication_year IS NULL OR publication_year BETWEEN 1 AND 9999);
