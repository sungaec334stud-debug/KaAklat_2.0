# Account access completion — 2026-11-03
Scope approved by user: administrator student management, staff-assisted recovery and admin audit browsing. Existing schema only; no hosting/commit.
1. Add independent policy/transaction tests before app code. Separate student-manager authorization from shared librarian catalog guard.
2. Recovery service: numeric actor/target user locks then reset; cryptographic 32-byte secrets, raw-byte SHA256 hash only in database, UTC30min expiry, one issuance/account, post-lock recheck/redemption, atomic password/session/reset/audit; status preserved; no auto-login. Staff identity checkbox, POST/CSRF and PRG delivery stored in session only until first GET.
3. Minimal pages recover_account.php and reset_password.php; admin audit.php read-only bounded metadata filters, no summary/free-text payload. Shared existing responsive style. Add role navigation and login guidance.
4. Test independent-process redemption/reissue and account changes; HTTP authorization/CSRF/one-time/expiry/privacy and regression/lint. No demo mutation; document actual gaps and manual checklist.
