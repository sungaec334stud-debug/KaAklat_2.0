# KaAklat coding-agent instructions

## Read before changing anything

Read PROGRESS.md (especially Resume here), SRS.md, SCHEMA.md, TEST_PLAN.md and the files relevant to the task. Inspect directory contents and Git status if a repository exists; do not assume a fresh checkout. Preserve user work. These files describe a hosted school demonstration, not a deployed production school service.

The user selected option A: plain PHP 8.4, PDO, MySQL-compatible InnoDB database, semantic HTML, custom CSS and minimal JavaScript. No Laravel or other application framework. Do not introduce Composer/npm, paid APIs, email/cron dependencies or Bootstrap without a clear approved need. Database compatibility target: MariaDB 10.4+ and MySQL 8.0.16+; actual hosting runtime still needs verification. See STACK_AND_HOSTING_REVIEW.md.

## Current boundaries

- The initial deliverable is design, SQL and development documentation. Do not describe unimplemented requirements as working features.
- Student, librarian and administrator are distinct roles; no automatic admin circulation permission. Catalog and cover access require login; pending students can browse but cannot borrow.
- Five combined live approved pickups and active loans maximum. Approval and physical pickup differ; only librarians record pickup/return. No ISBN/fines/barcodes.
- Requirements marked P in SRS.md are proposed defaults, not verified school policy. Respect subsequent user decisions and synchronize the documents.
- Create only the next small reviewable increment. Do not generate the full application in one change.

## Planned repository structure and conventions

- `public/`: HTTP entry pages and public CSS/JS assets; never secrets, SQL dumps or unguarded student data/cover files.
- `app/`: shared bootstrap, PDO connection, authentication, authorization, validation and circulation functions.
- `views/`: HTML templates, escaping at output boundaries.
- `schema.sql`, `seed.sql`: initial schema and wholly fictional development seed. `migrations/`: later numbered additive changes with documented migration/rollback considerations.
- `tests/`: future automated checks; `verification/`: reproducible database/design checks created during this phase.
- Private config, staged uploads and backups belong outside `public/`, outside Git, and outside public hosting paths. Directory access denial must be verified if the provider forces private files inside its document root.
- Use four-space indentation, UTF-8, clear snake_case database columns, readable PHP function names, strict parameter/return types where useful, and explicit PDO exceptions. Keep files focused; avoid magic globals and copy-pasted authorization checks.
- Use GET only for reads and POST with CSRF tokens for state changes; redirect after successful forms. Do not depend on JavaScript for security or validation.
- UI text should explain a user's task, not expose database IDs, exception traces or implementation jargon.

## Local setup and verification commands

PowerShell, from this project directory:

```powershell
& 'C:\php-8.4.14\php.exe' -v
& 'C:\php-8.4.14\php.exe' -m
& 'C:\xampp\mysql\bin\mysql.exe' --version
git --version
```

Once the relevant application files exist:

```powershell
& 'C:\php-8.4.14\php.exe' -S 127.0.0.1:8000 -t public
```

This command is only for local development. XAMPP has another PHP executable/version; keep the selected executable explicit. A database client version is not proof of running server version; query `SELECT VERSION()` and `SHOW ENGINES` on each target.

Lint PHP files without pretending an empty directory passed:

```powershell
$phpFiles = Get-ChildItem -Path app,public,views,tests -Filter *.php -Recurse -File -ErrorAction SilentlyContinue
if (-not $phpFiles) { throw 'No PHP files found; lint not executed.' }
foreach ($phpFile in $phpFiles) {
    & 'C:\php-8.4.14\php.exe' -l $phpFile.FullName
    if ($LASTEXITCODE -ne 0) { throw "PHP lint failed: $($phpFile.FullName)" }
}
```

See WORKFLOW.md for database import steps; PowerShell does not support shell-style `< schema.sql`. Use the client's `source` command or phpMyAdmin. Never run seed.sql against an existing/live database. No application test runner exists yet: create a minimal meaningful runner alongside the first implementation tests and document its real command. Review TEST_PLAN.md for manual and concurrency scenarios.

## Security and integrity are part of each increment

- Hash passwords with PHP password functions; never seed usable shared credentials. Seed NULL password hashes mean disabled login, not an empty-password account.
- Parameterize query values, allowlist sort identifiers, validate on server, escape output for its context, and reject forged role/status fields.
- Require role and ownership checks on every protected request. Recheck current account status/session version; revoke sessions after security-sensitive changes.
- Implement CSRF, secure cookie flags, login throttling and recovery-secret expiry/single use as specified. Do not expose secrets in URLs, console logs, prompts or audit payloads.
- Covers are authorized JPEG/PNG only with byte/dimension limits, actual decoding and re-encoding, generated filenames and no execution. Do not allow raw arbitrary uploads or public static paths to bypass login.
- Use transactions and consistent locks for request/approval/pickup/return, copy allocation, student allowance and audit. Database uniqueness is a final backstop, not a substitute for policy checks. See SCHEMA.md for the exact lock order and state model.
- Never copy a counter such as available copies or overdue status into editable storage. Derive it from copies and loan lifecycle. Preserve original request terms and borrowing history.
- Do not delete archived/history records to fix a failing test or simplify a schema.
- Production errors show a generic message with a reference; details remain private and omit secrets/borrowing reasons.
- Use fictional borrowers only until the school has explicitly agreed to real-data handling. Never send private school records to external tools or APIs.

## Database changes and operational safety

Read the current schema and migration history before editing. For any schema change, update SCHEMA.md, affected SRS rules, tests, workflow and paper revision map. Prefer additive migrations and document compatibility with existing data. Check foreign keys and CHECK constraints on the actual server; don't assume engine/version behavior.

No destructive reset, data deletion, production migration, force push, recursive cleanup of unknown folders or live database replacement without explicit authorization. Never use `git reset --hard`, DROP/TRUNCATE, or a blanket database re-import as a routine fix. Independently created empty scratch databases may be populated for tests; keep their paths/database names explicit and isolated from XAMPP's existing data.

Before deployment, use DEPLOYMENT.md, take a private backup, verify restore, and account for live transactions in rollback. Do not make hosting accounts, paid subscriptions, or claims of completed deployment from documentation alone. No automated cron workarounds on InfinityFree.

## Working increments and truthful reporting

1. State the exact requirement and files the increment touches.
2. Check existing behavior and dependencies. Write meaningful tests for security/integrity and functional behavior before implementation; don't add tests that only mirror trivial markup.
3. Implement only that increment; run relevant lint/tests and inspect the interface when it exists.
4. Update TEST_PLAN.md with actual result/evidence, PROGRESS.md with current state, CHANGELOG.md with historical changes, and append the actual prompt/outcome to prompt.md. Keep failures and limitations visible.
5. Report what changed, what was executed, what passed/failed/not run, and the next action. Do not invent test counts, citations, interview responses, user-acceptance results or successful deployments.

Research writing must use planned language until implementation or evaluation evidence exists. Preserve the supplied PDF and revise chapter by chapter using its template.
