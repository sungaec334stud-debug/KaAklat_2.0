<?php
// Upgrade connection-local fixtures only; invoked immediately after CREATE TEMPORARY books.
function metadata_fixture_columns(PDO $pdo): void
{
    $pdo->exec('CREATE TEMPORARY TABLE categories (category_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_name VARCHAR(60) UNIQUE) ENGINE=InnoDB');
    $pdo->exec('ALTER TABLE books ADD COLUMN category_id BIGINT UNSIGNED NULL, ADD COLUMN publication_year SMALLINT UNSIGNED NULL');
}
