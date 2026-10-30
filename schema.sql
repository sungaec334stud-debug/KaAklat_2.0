-- KaAklat proposed demo schema, 2026-10-29. See SCHEMA.md before importing.
-- Select a NEW, EMPTY database supplied by your host/phpMyAdmin first.
-- Target: MariaDB 10.4+ / MySQL 8.0 family (8.0.16+ required for CHECK enforcement).
-- No CREATE DATABASE, destructive DDL, triggers, events, or privileged server settings.
-- DDL commits implicitly: an interrupted import is NOT an atomic migration.
-- Plain CREATE TABLE deliberately fails when tables already exist; do not force past errors.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET SESSION time_zone = '+00:00';
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION';

CREATE TABLE users (
    user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    role ENUM('student','librarian','administrator') NOT NULL DEFAULT 'student',
    account_status ENUM('pending','active','suspended','archived') NOT NULL DEFAULT 'pending',
    session_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username),
    KEY ix_users_role_status (role, account_status, user_id),
    CONSTRAINT ck_users_username CHECK (username REGEXP '^[a-z0-9_]{3,32}$'),
    CONSTRAINT ck_users_name CHECK (CHAR_LENGTH(TRIM(display_name)) BETWEEN 1 AND 100),
    CONSTRAINT ck_users_pending_role CHECK (account_status <> 'pending' OR role = 'student'),
    CONSTRAINT ck_users_session_version CHECK (session_version >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE books (
    book_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    cover_path VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    archived_at DATETIME NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (book_id),
    KEY ix_books_catalog (archived_at, title),
    CONSTRAINT ck_books_title CHECK (CHAR_LENGTH(TRIM(title)) BETWEEN 1 AND 255),
    CONSTRAINT ck_books_description CHECK (CHAR_LENGTH(TRIM(description)) BETWEEN 1 AND 2000),
    CONSTRAINT ck_books_archive_time CHECK (archived_at IS NULL OR archived_at >= created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE authors (
    author_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    author_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (author_id),
    KEY ix_authors_name (author_name),
    CONSTRAINT ck_authors_name CHECK (CHAR_LENGTH(TRIM(author_name)) BETWEEN 1 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE book_authors (
    book_id BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    credit_order SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (book_id, author_id),
    UNIQUE KEY uq_book_authors_order (book_id, credit_order),
    KEY ix_book_authors_author (author_id, book_id),
    CONSTRAINT ck_book_authors_order CHECK (credit_order >= 1),
    CONSTRAINT fk_book_authors_book FOREIGN KEY (book_id) REFERENCES books (book_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_book_authors_author FOREIGN KEY (author_id) REFERENCES authors (author_id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE book_copies (
    copy_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    copy_code VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    archived_at DATETIME NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (copy_id),
    UNIQUE KEY uq_book_copies_code (copy_code),
    KEY ix_book_copies_book (book_id, archived_at, copy_id),
    CONSTRAINT ck_book_copies_code CHECK (copy_code REGEXP '^[A-Z0-9][A-Z0-9-]{2,31}$'),
    CONSTRAINT ck_book_copies_archive_time CHECK (archived_at IS NULL OR archived_at >= created_at),
    CONSTRAINT fk_book_copies_book FOREIGN KEY (book_id) REFERENCES books (book_id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE borrow_requests (
    request_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    requested_days TINYINT UNSIGNED NOT NULL,
    request_reason VARCHAR(500) NOT NULL,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decision ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    approved_days TINYINT UNSIGNED NULL DEFAULT NULL,
    decided_at DATETIME NULL DEFAULT NULL,
    decided_by BIGINT UNSIGNED NULL DEFAULT NULL,
    decision_note VARCHAR(500) NULL DEFAULT NULL,
    pending_book_id BIGINT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN decision = 'pending' THEN book_id ELSE NULL END) STORED,
    PRIMARY KEY (request_id),
    UNIQUE KEY uq_requests_one_pending_title (student_id, pending_book_id),
    KEY ix_requests_student_title (student_id, book_id, decision),
    KEY ix_requests_queue (decision, requested_at, request_id),
    KEY ix_requests_expiry (decision, decided_at, request_id),
    KEY ix_requests_book (book_id),
    KEY ix_requests_decider (decided_by),
    CONSTRAINT ck_requests_duration CHECK (requested_days BETWEEN 1 AND 30),
    CONSTRAINT ck_requests_reason CHECK (CHAR_LENGTH(TRIM(request_reason)) BETWEEN 10 AND 500),
    CONSTRAINT ck_requests_decision CHECK (
        (decision = 'pending' AND approved_days IS NULL AND decided_at IS NULL AND decided_by IS NULL AND decision_note IS NULL)
        OR (decision = 'approved' AND approved_days IS NOT NULL AND approved_days BETWEEN 1 AND 30 AND decided_at IS NOT NULL AND decided_by IS NOT NULL)
        OR (decision IN ('rejected','cancelled') AND approved_days IS NULL AND decided_at IS NOT NULL AND decided_by IS NOT NULL)
    ),
    CONSTRAINT ck_requests_note CHECK (
        (decision <> 'rejected' AND (decision <> 'approved' OR approved_days = requested_days))
        OR (decision_note IS NOT NULL AND CHAR_LENGTH(TRIM(decision_note)) BETWEEN 1 AND 500)
    ),
    CONSTRAINT ck_requests_decision_time CHECK (decided_at IS NULL OR decided_at >= requested_at),
    CONSTRAINT fk_requests_student FOREIGN KEY (student_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_requests_book FOREIGN KEY (book_id) REFERENCES books (book_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_requests_decider FOREIGN KEY (decided_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A loan row is created AT APPROVAL, even before physical checkout.
-- Do not repeat book_id/student_id/approved_days here: request_id supplies them.
CREATE TABLE loans (
    loan_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_id BIGINT UNSIGNED NOT NULL,
    copy_id BIGINT UNSIGNED NOT NULL,
    checked_out_at DATETIME NULL DEFAULT NULL,
    checked_out_by BIGINT UNSIGNED NULL DEFAULT NULL,
    due_date DATE NULL DEFAULT NULL,
    returned_at DATETIME NULL DEFAULT NULL,
    returned_by BIGINT UNSIGNED NULL DEFAULT NULL,
    cancelled_at DATETIME NULL DEFAULT NULL,
    cancellation_kind ENUM('student','librarian','expired') NULL DEFAULT NULL,
    cancelled_by BIGINT UNSIGNED NULL DEFAULT NULL,
    cancellation_note VARCHAR(500) NULL DEFAULT NULL,
    active_copy_id BIGINT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN returned_at IS NULL AND cancelled_at IS NULL THEN copy_id ELSE NULL END) STORED,
    PRIMARY KEY (loan_id),
    UNIQUE KEY uq_loans_request (request_id),
    UNIQUE KEY uq_loans_active_copy (active_copy_id),
    KEY ix_loans_copy_history (copy_id, loan_id),
    KEY ix_loans_outstanding (returned_at, cancelled_at, due_date),
    KEY ix_loans_checkout_actor (checked_out_by),
    KEY ix_loans_return_actor (returned_by),
    KEY ix_loans_cancel_actor (cancelled_by),
    CONSTRAINT ck_loans_checkout CHECK (
        (checked_out_at IS NULL AND checked_out_by IS NULL AND due_date IS NULL)
        OR (checked_out_at IS NOT NULL AND checked_out_by IS NOT NULL AND due_date IS NOT NULL)
    ),
    CONSTRAINT ck_loans_due_window CHECK (
        checked_out_at IS NULL OR due_date BETWEEN
            DATE_ADD(DATE(DATE_ADD(checked_out_at, INTERVAL 8 HOUR)), INTERVAL 1 DAY)
            AND DATE_ADD(DATE(DATE_ADD(checked_out_at, INTERVAL 8 HOUR)), INTERVAL 30 DAY)
    ),
    CONSTRAINT ck_loans_return CHECK (
        (returned_at IS NULL AND returned_by IS NULL)
        OR (returned_at IS NOT NULL AND returned_by IS NOT NULL AND checked_out_at IS NOT NULL AND returned_at >= checked_out_at AND cancelled_at IS NULL)
    ),
    CONSTRAINT ck_loans_cancel CHECK (
        (cancelled_at IS NULL AND cancellation_kind IS NULL AND cancelled_by IS NULL AND cancellation_note IS NULL)
        OR (cancelled_at IS NOT NULL AND checked_out_at IS NULL AND returned_at IS NULL AND cancellation_kind IS NOT NULL
            AND ((cancellation_kind = 'expired' AND cancelled_by IS NULL)
              OR (cancellation_kind IN ('student','librarian') AND cancelled_by IS NOT NULL)))
    ),
    CONSTRAINT ck_loans_cancel_note CHECK (
        cancellation_kind IS NULL OR cancellation_kind <> 'librarian'
        OR (cancellation_note IS NOT NULL AND CHAR_LENGTH(TRIM(cancellation_note)) BETWEEN 1 AND 500)
    ),
    CONSTRAINT fk_loans_request FOREIGN KEY (request_id) REFERENCES borrow_requests (request_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_loans_copy FOREIGN KEY (copy_id) REFERENCES book_copies (copy_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_loans_checkout_actor FOREIGN KEY (checked_out_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_loans_return_actor FOREIGN KEY (returned_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_loans_cancel_actor FOREIGN KEY (cancelled_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Unknown usernames must be throttleable: intentionally no users FK.
-- This stable bucket row serializes attempts even for nonexistent accounts.
CREATE TABLE login_throttle (
    username_key VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (username_key),
    CONSTRAINT ck_login_throttle_key CHECK (username_key REGEXP '^[a-z0-9_]{3,32}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Keep individual failures to count an exact rolling 15-minute window.
CREATE TABLE login_failures (
    attempt_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username_key VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attempt_id),
    KEY ix_login_failures_window (username_key, attempted_at),
    KEY ix_login_failures_cleanup (attempted_at),
    CONSTRAINT fk_login_failures_bucket FOREIGN KEY (username_key) REFERENCES login_throttle (username_key) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One current reset per account. Replace only inside a locked transaction.
-- Consumption removes this short-lived row; the audit holds non-secret metadata.
CREATE TABLE password_resets (
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash BINARY(32) NOT NULL,
    issued_by BIGINT UNSIGNED NOT NULL,
    issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_password_resets_hash (token_hash),
    KEY ix_password_resets_issuer (issued_by),
    KEY ix_password_resets_expiry (expires_at),
    CONSTRAINT ck_password_resets_expiry CHECK (expires_at > issued_at AND expires_at <= DATE_ADD(issued_at, INTERVAL 30 MINUTE)),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_password_resets_issuer FOREIGN KEY (issued_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_events (
    audit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id BIGINT UNSIGNED NULL DEFAULT NULL,
    event_code VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    entity_type VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    entity_id BIGINT UNSIGNED NULL DEFAULT NULL,
    event_summary VARCHAR(255) NOT NULL,
    occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (audit_id),
    KEY ix_audit_time (occurred_at, audit_id),
    KEY ix_audit_actor (actor_user_id, occurred_at),
    KEY ix_audit_entity (entity_type, entity_id, occurred_at),
    CONSTRAINT ck_audit_code CHECK (event_code REGEXP '^[a-z][a-z0-9._-]{1,63}$'),
    CONSTRAINT ck_audit_summary CHECK (CHAR_LENGTH(TRIM(event_summary)) BETWEEN 1 AND 255),
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
