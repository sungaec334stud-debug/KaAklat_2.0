# Full local verification — 2026-11-03

Fresh run against current working tree, including recovery/audit/admin student access. All executed automated checks PASS; this does not prove zero bugs.

- tests/run_regression.php: 21 scripts PASS.
- Seven independent-process concurrency suites: 17 scenarios PASS (request 2; decision 4; pickup 2; return 2; cancellation 3; staff 2; recovery 2). Shared scheduled starts, not deterministic lock barriers.
- tests/pickup_http_acceptance_test.php with broad/account-access helpers: 101 executed HTTP assertions PASS.
- 79 PHP files lint PASS across app/public/views/tests/bin; forms.js syntax PASS; git diff --check PASS (line-ending normalization warnings only).
- Actual local /health.php: HTTP200, status ready; MariaDB10.4.32.
- Read-only demo integrity counts all zero: loan/copy title mismatch, nonapproved request with loan, duplicate unresolved copy, checkout due-date mismatch against approved Manila duration, approved request without allocation.

Coverage includes existing authentication/throttling/roles/session revocation; catalog filters, real multipart cover decode/re-encode and replacement, editing/copies/archive; borrowing caps/ownership/overdue, decisions/allocation, pickup/expiry/returns/cancellation; staff management/last-admin/bootstrap, administrator student access; recovery identity/CSRF/authorization, digest/expiry/reissue/replay/atomic rollback and status preservation; administrator read-only audit privacy/filter/pagination. Prior test reports supply detailed individual assertions.

Scratch databases retained:
- kaaklat_request_test_75e7f58fa611
- kaaklat_decision_test_a654b8308862
- kaaklat_pickup_test_794cf2aa1ca5
- kaaklat_return_test_c3e23694de77
- kaaklat_cancel_test_3e545675ea8e
- kaaklat_staff_test_99ef129049b3
- kaaklat_recovery_test_a9651ffe33b3
- kaaklat_acceptance_6e44cffed72e

Temporary fixture tables and new fictional databases only; demo read-only checks did not mutate records. HTTP server stopped by harness; generated fixture cover/image files cleaned by exact names. Existing work, accounts, covers and research output preserved. No application changes needed by this verification; no commit/push.

Limits: no browser-control tool exposed for fresh visual/keyboard/narrow/zoom/screen-reader checks. New recovery/audit/admin-student pages still require manual acceptance. Existing user-reported passes remain scoped to the supplied prior checklists. Interactive bootstrap/password tools not rerun on demo. Legacy auth_integration_test.php not run because it requires a real demo password and changes its login-failure state; isolated auth/HTTP tests used. Hosted HTTPS/cookies, performance, restore rehearsal, participant evaluation and archive-versus-circulation races not tested. No exhaustive testing or security/accessibility certification claimed.

Next: manual acceptance of recovery/audit/admin student pages, then remaining SRS operational verification, especially backup/restore and performance before deployment.
