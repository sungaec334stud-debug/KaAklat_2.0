-- FICTIONAL DEMO DATA ONLY. No real students, staff, authors or publications.
-- Import once after schema.sql into a NEW, EMPTY database, stopping on any error.
-- All 33 password_hash values are NULL: these rows CANNOT authenticate.
-- Never turn NULL into a shared/default password. Bootstrap separately with
-- PHP password_hash(), a private individual secret and an audited local process.
-- This is NOT a production seed or evidence of a completed participant study.
-- Rerun: deliberate duplicate-key failure; no IGNORE, REPLACE, upsert or deletion.
-- If any statement fails, ROLLBACK in that connection; do not continue to COMMIT.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET SESSION time_zone = '+00:00';
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION';
SET @seed_now = UTC_TIMESTAMP();
SET @seed_today_manila = DATE(DATE_ADD(@seed_now, INTERVAL 8 HOUR));
SET @seed_created = DATE_SUB(@seed_now, INTERVAL 120 DAY);

START TRANSACTION;

INSERT INTO users (user_id, username, display_name, password_hash, role, account_status, created_at, updated_at) VALUES
(1, 'demo_admin', 'Demo Administrator', NULL, 'administrator', 'active', @seed_created, @seed_created),
(2, 'demo_librarian_a', 'Demo Librarian A', NULL, 'librarian', 'active', @seed_created, @seed_created),
(3, 'demo_librarian_b', 'Demo Librarian B', NULL, 'librarian', 'active', @seed_created, @seed_created);

INSERT INTO users (user_id, username, display_name, password_hash, role, account_status, created_at, updated_at)
SELECT 100 + n.student_no, CONCAT('demo_student', LPAD(n.student_no, 2, '0')),
       CONCAT('Demo Learner ', LPAD(n.student_no, 2, '0')), NULL, 'student',
       CASE WHEN n.student_no = 29 THEN 'pending' WHEN n.student_no = 30 THEN 'suspended' ELSE 'active' END,
       @seed_created, @seed_created
FROM (
    SELECT tens.d * 10 + units.d + 1 AS student_no
    FROM (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2) AS tens
    CROSS JOIN (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS units
) AS n;

INSERT INTO authors (author_id, author_name) VALUES
(1, 'Fictional Author Aster Vale'), (2, 'Fictional Author Mira Quill'),
(3, 'Fictional Author Rowan Gleam'), (4, 'Fictional Author Lio Fern'),
(5, 'Fictional Author Sera Brook'), (6, 'Fictional Author Tala Wisp'),
(7, 'Fictional Author Neri Moss'), (8, 'Fictional Author Ivo Reed');

INSERT INTO books (book_id, title, description, created_at, updated_at) VALUES
(1, 'The Lantern Valley Mapmakers', 'An invented story about young mapmakers charting a valley of paper lanterns. Fictional demo publication.', @seed_created, @seed_created),
(2, 'A Small Atlas of Imaginary Islands', 'An invented illustrated atlas for practicing descriptive reading. Fictional demo publication.', @seed_created, @seed_created),
(3, 'The Clockwork Mango Garden', 'An invented adventure about a garden whose trees keep time. Fictional demo publication.', @seed_created, @seed_created),
(4, 'Letters from the Cloud Library', 'An invented collection of letters exchanged through a floating library. Fictional demo publication.', @seed_created, @seed_created),
(5, 'The Quiet River Experiments', 'An invented school science notebook set beside an imaginary river. Fictional demo publication.', @seed_created, @seed_created),
(6, 'Counting Stars with Paper Boats', 'An invented collection of mathematics puzzles in a story setting. Fictional demo publication.', @seed_created, @seed_created),
(7, 'The Village of Unfinished Songs', 'An invented story about friends completing a village songbook. Fictional demo publication.', @seed_created, @seed_created),
(8, 'When the Moon Borrowed a Pencil', 'An invented fantasy about writing and shared responsibility. Fictional demo publication.', @seed_created, @seed_created);

INSERT INTO books (book_id, title, description, archived_at, created_at, updated_at)
SELECT n.book_no, CONCAT('The Lantern Valley Notebooks ', LPAD(n.book_no, 3, '0')),
       CONCAT('Invented practice reading volume ', n.book_no, ' for the KaAklat demonstration. This is not a real publication.'),
       CASE WHEN n.book_no = 100 THEN DATE_SUB(@seed_now, INTERVAL 1 DAY) ELSE NULL END,
       @seed_created, @seed_created
FROM (
    SELECT tens.d * 10 + units.d + 1 AS book_no
    FROM (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS tens
    CROSS JOIN (SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS units
) AS n
WHERE n.book_no BETWEEN 9 AND 100;

INSERT INTO book_authors (book_id, author_id, credit_order)
SELECT book_id, MOD(book_id - 1, 8) + 1, 1 FROM books WHERE book_id BETWEEN 1 AND 100;
INSERT INTO book_authors (book_id, author_id, credit_order) VALUES (1, 2, 2), (2, 3, 2);

INSERT INTO book_copies (copy_id, book_id, copy_code, archived_at, created_at)
SELECT (b.book_id - 1) * 3 + c.copy_no, b.book_id,
       CONCAT('DEMO-', LPAD(b.book_id, 3, '0'), '-', c.copy_no),
       CASE WHEN b.book_id = 100 THEN DATE_SUB(@seed_now, INTERVAL 1 DAY) ELSE NULL END,
       @seed_created
FROM books AS b
CROSS JOIN (SELECT 1 AS copy_no UNION ALL SELECT 2 UNION ALL SELECT 3) AS c
WHERE b.book_id BETWEEN 1 AND 100;

INSERT INTO borrow_requests
(request_id, student_id, book_id, requested_days, request_reason, requested_at, decision, approved_days, decided_at, decided_by, decision_note) VALUES
(1, 101, 1, 7, 'Fictional request for a reading exercise.', DATE_SUB(@seed_now, INTERVAL 1 HOUR), 'pending', NULL, NULL, NULL, NULL),
(2, 102, 2, 7, 'Fictional request for an atlas activity.', DATE_SUB(@seed_now, INTERVAL 2 HOUR), 'approved', 5, DATE_SUB(@seed_now, INTERVAL 1 HOUR), 2, 'Five days approved for this fictional exercise.'),
(3, 103, 3, 7, 'Fictional request for a classroom discussion.', DATE_SUB(@seed_now, INTERVAL 4 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 3 DAY), 2, NULL),
(4, 104, 4, 7, 'Fictional request for a letter writing exercise.', DATE_SUB(@seed_now, INTERVAL 12 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 11 DAY), 2, NULL),
(5, 105, 5, 7, 'Fictional request for a science reading task.', DATE_SUB(@seed_now, INTERVAL 13 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 12 DAY), 3, NULL),
(6, 106, 6, 7, 'Fictional request for a mathematics activity.', DATE_SUB(@seed_now, INTERVAL 2 DAY), 'rejected', NULL, DATE_SUB(@seed_now, INTERVAL 1 DAY), 2, 'Fictional rejection to demonstrate the decision screen.'),
(7, 107, 7, 7, 'Fictional request for a short reading activity.', DATE_SUB(@seed_now, INTERVAL 6 HOUR), 'cancelled', NULL, DATE_SUB(@seed_now, INTERVAL 5 HOUR), 107, NULL),
(8, 108, 8, 7, 'Fictional request used to show pickup expiry.', DATE_SUB(@seed_now, INTERVAL 5 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 4 DAY), 2, NULL),
(9, 109, 9, 7, 'Fictional request cancelled before physical pickup.', DATE_SUB(@seed_now, INTERVAL 4 HOUR), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 3 HOUR), 2, NULL),
(10, 110, 10, 7, 'Fictional request cancelled by library staff.', DATE_SUB(@seed_now, INTERVAL 4 HOUR), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 3 HOUR), 3, NULL),
(11, 111, 11, 7, 'Fictional cap fixture: first of five books.', DATE_SUB(@seed_now, INTERVAL 3 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 2 DAY), 2, NULL),
(12, 111, 12, 7, 'Fictional cap fixture: second of five books.', DATE_SUB(@seed_now, INTERVAL 3 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 2 DAY), 2, NULL),
(13, 111, 13, 7, 'Fictional cap fixture: third of five books.', DATE_SUB(@seed_now, INTERVAL 3 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 2 DAY), 3, NULL),
(14, 111, 14, 7, 'Fictional cap fixture: fourth of five books.', DATE_SUB(@seed_now, INTERVAL 3 HOUR), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 2 HOUR), 2, NULL),
(15, 111, 15, 7, 'Fictional cap fixture: fifth of five books.', DATE_SUB(@seed_now, INTERVAL 3 HOUR), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 2 HOUR), 3, NULL),
(16, 130, 16, 7, 'Fictional loan retained after account suspension.', DATE_SUB(@seed_now, INTERVAL 4 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 3 DAY), 2, NULL),
(17, 112, 17, 7, 'Fictional stale assignment for the cleanup test.', DATE_SUB(@seed_now, INTERVAL 4 DAY), 'approved', 7, DATE_SUB(@seed_now, INTERVAL 3 DAY), 2, NULL);

INSERT INTO loans
(loan_id, request_id, copy_id, checked_out_at, checked_out_by, due_date, returned_at, returned_by, cancelled_at, cancellation_kind, cancelled_by, cancellation_note) VALUES
(1, 2, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 3, 7, DATE_SUB(@seed_now, INTERVAL 2 DAY), 2, DATE_ADD(@seed_today_manila, INTERVAL 5 DAY), NULL, NULL, NULL, NULL, NULL, NULL),
(3, 4, 10, DATE_SUB(@seed_now, INTERVAL 10 DAY), 2, DATE_SUB(@seed_today_manila, INTERVAL 3 DAY), NULL, NULL, NULL, NULL, NULL, NULL),
(4, 5, 13, DATE_SUB(@seed_now, INTERVAL 11 DAY), 3, DATE_SUB(@seed_today_manila, INTERVAL 4 DAY), DATE_SUB(@seed_now, INTERVAL 5 DAY), 2, NULL, NULL, NULL, NULL),
(5, 8, 22, NULL, NULL, NULL, NULL, NULL, DATE_SUB(@seed_now, INTERVAL 2 DAY), 'expired', NULL, NULL),
(6, 9, 25, NULL, NULL, NULL, NULL, NULL, DATE_SUB(@seed_now, INTERVAL 1 HOUR), 'student', 109, NULL),
(7, 10, 28, NULL, NULL, NULL, NULL, NULL, DATE_SUB(@seed_now, INTERVAL 2 HOUR), 'librarian', 3, 'Fictional staff cancellation for the demo.'),
(8, 11, 31, DATE_SUB(@seed_now, INTERVAL 1 DAY), 2, DATE_ADD(@seed_today_manila, INTERVAL 6 DAY), NULL, NULL, NULL, NULL, NULL, NULL),
(9, 12, 34, DATE_SUB(@seed_now, INTERVAL 1 DAY), 2, DATE_ADD(@seed_today_manila, INTERVAL 6 DAY), NULL, NULL, NULL, NULL, NULL, NULL),
(10, 13, 37, DATE_SUB(@seed_now, INTERVAL 1 DAY), 3, DATE_ADD(@seed_today_manila, INTERVAL 6 DAY), NULL, NULL, NULL, NULL, NULL, NULL),
(11, 14, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 15, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 16, 46, DATE_SUB(@seed_now, INTERVAL 2 DAY), 2, DATE_ADD(@seed_today_manila, INTERVAL 5 DAY), NULL, NULL, NULL, NULL, NULL, NULL),
(14, 17, 49, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- Selected audit fixtures, not a reconstructed complete event history.
INSERT INTO audit_events (audit_id, actor_user_id, event_code, entity_type, entity_id, event_summary, occurred_at) VALUES
(1, NULL, 'demo.seed', 'demo', NULL, 'Fictional fixtures: 100 titles, 300 copies, 30 student accounts and 3 staff accounts; all logins disabled.', @seed_now),
(2, 2, 'request.approved', 'borrow_request', 2, 'Approved for 5 days; physical copy allocated.', DATE_SUB(@seed_now, INTERVAL 1 HOUR)),
(3, 2, 'loan.returned', 'loan', 4, 'Return recorded by librarian.', DATE_SUB(@seed_now, INTERVAL 5 DAY)),
(4, NULL, 'loan.expired', 'loan', 5, 'Uncollected allocation expired after the pickup deadline.', DATE_SUB(@seed_now, INTERVAL 2 DAY)),
(5, 1, 'user.suspended', 'user', 130, 'Account suspended; existing loan retained for return.', DATE_SUB(@seed_now, INTERVAL 1 DAY));

COMMIT;
