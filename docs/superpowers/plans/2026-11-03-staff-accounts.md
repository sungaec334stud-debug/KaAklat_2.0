# Staff management — 2026-11-03
Approved scope: admin-only staff create/list/role/status management; NULL-password creation; CLI-only bootstrap; private interactive password assignment; no recovery/circulation/hosting/commit.
1. tests/staff_accounts_test.php: temporary fixtures for roles/session/CSRF, validation, last usable admin, idempotency, reset revocation and audit rollback before service implementation.
2. app/staff_accounts.php: shared database-specific advisory guard before transaction, numeric user locks, active actor/session recheck, create/update/bootstrap and bounded listing. Keep student roles inaccessible and no physical deletion. Guard serializes empty bootstrap and last-admin decisions, released in finally.
3. public/staff.php, views/layout.php, public/index.php: existing style/forms/error summaries, retained inputs, expected session version for stale updates, PRG and immediate self-change logout.
4. bin/bootstrap_admin.php plus private interactive input helper; extend set_user_password.php without breaking old callers. Never run bootstrap on demo DB. Password secrets never argv for documented workflow.
5. Independent-process scratch concurrency and HTTP/security checks, regression/lint; record actual limitations, commands, handoff and prompt. Browser acceptance only if observed. Next staff-assisted recovery.
