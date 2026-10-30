# KaAklat progress

Updated: 2026-10-30 (Asia/Manila)

Status vocabulary: Planned; In progress; Implemented but unverified; Verified; Blocked.
Verified applies only to the named evidence, not to the entire application.

## Current status

| Task | Status | Evidence | Known issues | Next action |
| --- | --- | --- | --- | --- |
| Capture project direction | In progress | User confirmed librarian-recorded pickup/return; five-book allowance counts approved pickups and active loans; login-only catalog; librarian account activation; no ISBN; title/authors/description/cover/copy-count entry. Stack A selected. Presentation approximately November 2026; PDF is the required template; planned 20 students and a couple of librarians. | Exact deadline, evaluation instrument and librarian count unresolved. Pending cap, duplicate handling, 48-hour pickup expiry and 1-30-day range are recorded as proposed defaults, not school policies. | Review/confirm proposed defaults before changing them in code. |
| Review Chapters 1 and 2 | Verified | Read all 16 PDF pages as text; rendered and inspected PDF p. 5 Figure 1; findings in RESEARCH_REVISION_PLAN.md. User confirms this PDF is the required template. | Bibliography claims have not been independently audited. | Resolve evaluation requirements; revise after scope agreement. |
| Inspect local tools | Verified | Terminal PHP 8.4.14 with PDO and pdo_mysql; Git 2.47.1; XAMPP PHP 8.2.12; XAMPP database client reports MariaDB 10.4.32. Commands executed 2026-10-29. | Database client version does not prove running server version. No server connection, Apache test, or browser test performed. | Choose explicit executable paths; later verify server version and storage engines. |
| Evaluate free hosting | Verified | Official provider documentation checked on 2026-10-29; sources and limits recorded in STACK_AND_HOSTING_REVIEW.md. InfinityFree is recommended; AwardSpace free SSL restriction disqualifies it. | This verifies the source review only. No account created or deployment tested. | Select stack/host; perform early hosting smoke test. |
| Approve stack and workflow | In progress | Plain PHP 8.4/PDO/custom CSS/minimal JS selected; core approval/pickup/return workflow confirmed. SRS.md records proposed defaults. | User has not explicitly approved the complete SRS/design for implementation. Hosting account/runtime not selected/tested. | Review SRS.md and implementation plan; confirm design before application code. |
| SRS and normalized schema | Verified | User imported schema.sql and seed.sql through local XAMPP/phpMyAdmin into new database `kaaklat_dev` on 2026-10-29. Aggregate count query returned 33 users, 100 books, 300 copies, 17 requests and 14 loans, matching the fictional seed. | Import/count verification does not prove application workflows, transactions, every database constraint, backup restoration or hosted compatibility. | Begin application foundation only after design approval; later run constraint and concurrency checks. |
| Development documentation | Implemented but unverified | AGENTS.md, WORKFLOW.md, prompt.md, CHANGELOG.md, TEST_PLAN.md, DEPLOYMENT.md, research plan and implementation plan exist. | Documents require review and later updates based on real implementation/runtime evidence. | Review documents, then update per increment. |
| Application implementation | In progress | First PHP foundation and student registration/login/logout routes exist. PHP syntax lint, unit tests, and integration test pass. Local CLI bootstrap script `bin/set_user_password.php` implemented and used to set password for `demo_admin`. Fixed literal newline SQL syntax errors across `app/auth.php`. User browser checks confirmed `health.php` ready, `test_student_04` registration, sign-in, and dashboard. | Librarian activation, catalog, circulation, covers and deployment remain to be implemented. | Verify web login with `demo_admin`, then implement librarian account activation and catalog features. |
| Deployment and evaluation | Planned | User requires hosted demonstration. | No deployed system or evaluator results. | Test a minimal hosted page/database connection early; evaluate complete workflows later. |

## Confirmed workflow decisions

- Login-required catalog; students register and need librarian activation before borrowing.
- Requests, approvals and physical pickup are distinct stages; only librarians record pickup and return.
- Approved pickups and active loans together consume the five-book allowance.
- Catalog fields: title, multiple author names, brief description, cover and copy count; no ISBN required. Test data will use well-known book titles.

These are agreed demonstration requirements, not evidence of existing institutional policy.

## Working proposals, not yet confirmed

- At most five pending requests; one pending/approved/active request per student/title; one request receives one copy on approval.
- Approved copies are held for 48 hours. A new request is required after expiry. Student may cancel pending/uncollected request; librarian may cancel an uncollected approval with a reason.
- Requested and approved durations are whole days 1-30. Due date begins at physical pickup in Asia/Manila; students can see the final approved terms.
- No reservations/FIFO waitlists, renewals, external messages, imports or advanced reports in the initial MVP unless required by the adviser.
- Book-cover uploads are now required. Proposal: staff-only JPEG/PNG, size/dimension limits, decoded and re-encoded images with random filenames and no script execution in the image directory. No arbitrary file uploads.
- Fictional student accounts and simulated loan records; book metadata may use well-known titles.

## Resume here

Verify web login with `demo_admin` (or other seeded accounts using `bin/set_user_password.php`). Next implementation target is librarian account activation of pending students, followed by catalog browsing and management. Keep actual test/deployment/research evidence separate from proposals. The original PDF remains unchanged.

