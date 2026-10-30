# KaAklat implementation plan

**Goal:** Build and verify the KaAklat demonstration in small, reviewable increments using the approved plain-PHP stack.

**Spec:** `SRS.md`, `SCHEMA.md`, `TEST_PLAN.md`, `WORKFLOW.md`, and `DEPLOYMENT.md`.

**Important boundary:** This plan follows the selected stack and user-confirmed workflow. Requirements marked P in `SRS.md` are proposed demonstration defaults. Do not treat this plan as evidence that the website, tests, hosting, research evaluation, or paper revision is complete.

## Order of work

### 1. Repository and isolated local database baseline

- [ ] Initialize Git only if the user confirms it is the intended repository location; do not overwrite another repository.
- [ ] Create `public/`, `app/`, `views/`, `tests/`, `verification/`, private local configuration outside `public/`, and an `.env.example` without secrets.
- [ ] Verify PHP 8.4, PDO MySQL, actual MariaDB version and InnoDB in an explicitly named empty test database.
- [ ] Import `schema.sql`, then `seed.sql`, only into that empty test database. Record successful table/fixture counts and constraint checks in TEST_PLAN.md.
- [ ] Create the first database verification script with safe, read-only assertions. Do not use the fictional seeded accounts for sign-in because they contain no password hashes.

### 2. Shared application foundation

- [ ] Write tests first for environment loading, PDO settings, HTML escaping, CSRF creation/validation, flash messages and request validation.
- [ ] Implement a single private configuration loader, PDO factory with exceptions and UTC/session settings, base layout, safe response helpers and production-safe error handling.
- [ ] Add a health page only for local development. It must expose no secret/configuration details and must be removed/disabled before deployment.
- [ ] Lint all PHP files and run the actual test command before marking the increment verified.

### 3. Authentication and account lifecycle

- [ ] Write tests for student-only self-registration, canonical username duplicates, password hashing, login/logout, session rotation, idle timeout, pending/suspended status, forged role fields, and failed-login throttling.
- [ ] Implement registration, sign-in/out, session checks, librarian activation/suspension, and administrator staff-account management with last-administrator protection.
- [ ] Implement in-person recovery using a random single-use hashed secret, expiry, session-version revocation and audit event.
- [ ] Execute T-01, T-02, T-04, T-05, T-20, T-27 and T-28 where the environment permits; record real results.

### 4. Login-protected catalog and physical copies

- [ ] Write tests for catalog access control, author/title search, pagination, title/copy creation, archive guards, and count-derived availability.
- [ ] Implement catalog list/detail and librarian-only title, multiple-author and individual-copy management. Generate a unique copy code per physical copy; never store an editable available-count field.
- [ ] Implement restricted cover handling: decode/re-encode JPEG/PNG, enforce byte/pixel limits, generated names, private storage and authorized delivery. Test rejected disguised/non-image uploads.
- [ ] Execute T-03, T-06 through T-09, T-25, T-26 and relevant accessibility checks.

### 5. Borrow-request and decision workflow

- [ ] Write tests for request eligibility, reasons/durations, duplicate title requests, pending limit, unavailable catalog state, rejection notes and changed-duration notes.
- [ ] Implement own request history/cancellation and librarian decision screen. During approval, lock/recheck student, request and copy as specified in SCHEMA.md; create the matching allocation/loan and audit event in one transaction.
- [ ] Implement pickup expiry as an ordinary-request/action check, not a background task or cron workaround.
- [ ] Execute T-10 through T-13, T-17, T-21 and T-23 with independent database connections for races.

### 6. Pickup, return and operational lists

- [ ] Write tests for physical-copy confirmation, cap enforcement, Manila due-date calculation, overdue display, duplicate pickup, return idempotency and suspended/overdue borrower returns.
- [ ] Implement librarian pickup/return screens, student-only history, and librarian outstanding/overdue list. Add corresponding audit events atomically.
- [ ] Execute T-14 through T-19, T-22 and T-24.

### 7. Responsive/accessibility, backup and deployment rehearsal

- [ ] Inspect every core screen at 360, 768 and 1280 CSS pixels and at 400% zoom; complete core workflows using keyboard. Fix findings and record evidence.
- [ ] Populate the intended fictional performance dataset and collect the planned response measurements; do not label a target passed without samples.
- [ ] Rehearse export and restoration to a separate empty database, validate data/cover history, and record actual recovery results.
- [ ] Create a minimal hosted diagnostic deployment only after provider account setup is authorized. Verify HTTPS, PDO, InnoDB, constraints, secure private-file boundaries, session cookies and direct-subdomain behavior. Remove diagnostics before demo data.
- [ ] Execute T-30 through T-36 as applicable.

### 8. Presentation and research alignment

- [ ] Create a runbook with demonstration accounts created securely at the time, realistic but fictional catalog data, and a local fallback plan.
- [ ] Use actual test evidence to update the research revision plan and revise Chapter 1 first, then Chapter 2 after source checks. Keep planned, implemented and verified wording distinct.
- [ ] Conduct the planned participant work only after adviser/school requirements are known; record real procedure/results rather than using placeholder claims.

## Review checkpoints

Pause for review after each numbered increment. Each review includes changed files, requirements covered, exact checks run, observed result, unresolved items, and the next increment. Before any database schema change, deployment, research rewrite, or real-data use, update affected documentation and obtain the needed authorization.

