# Approved workspace implementation — 2026-11-12

## Scope and preserved behavior

Implemented the approved working preview for Staff accounts, Students, Audit,
Account recovery, About, Logout, Review requests, Pickups, Returns, Manage books,
My requests & loans and My Profile. Existing navy/blue/white/gray tokens and both
themes remain authoritative. Pagination design was explicitly excluded.

`views/layout.php` allowlists the twelve routes for the new `workspace.css` and
`workspace.js`. Catalog, dashboards, sign-in, registration and Settings do not load
the new assets. No dependencies, schema changes, new queries, role permissions or
circulation-service changes were introduced by this design increment. Earlier
uncommitted simplification changes are preserved, not attributed to this redesign.

The optional directory/inspector moves existing record nodes rather than cloning
forms. Original names, IDs, values, actions, CSRF tokens, server validators and
redirects remain. Enter/Space on a record selects it and focuses its heading;
Back to list restores focus to the row. Errors reveal the affected record before
linked field focus. Narrow initial loads show the list rather than a long action
form. JavaScript-disabled pages keep the original complete server-rendered forms.
Staff creation uses native details and stays open after an unsuccessful creation.
No private selection data is logged, exported or persisted in browser storage.

Profile remains own-only and read-only; its school ID and counts use existing data.
Book thumbnails reuse authenticated cover access. Recovery issuance and one-time
secret delivery are unchanged. About preserves the existing school/developer copy,
links and borrowing guidance; its shelf illustration is decorative CSS.

## Executed evidence

| Check | Actual result |
| --- | --- |
| Existing regression runner | 32 scripts passed against the isolated configuration. |
| Workflow HTTP | 390 assertions passed, including successful circulation/account actions, invalid/forged requests, role denial, original terms, availability, stale actions, recovery and ownership. |
| Pagination HTTP | 373 checks passed. |
| Theme browser sweep | 248 role/theme/width combinations passed; zero runtime exceptions and zero sampled rendered-text contrast failures. Includes 320px. |
| Pagination browser sweep | 90 combinations at 1440/768/360px, Light/Dark; 28 keyboard navigations with JavaScript disabled; zero runtime exceptions. |
| New workspace browser suite | 78 layouts at 1440/768/360px, Light/Dark, and 30 focused interaction assertions passed; zero runtime exceptions. |
| Focused interaction content | Unique IDs; original form signatures retained across selection; Enter selection and back focus; second-record server validation with retained values; error-link field focus; receipt search retained; narrow initial list and keyboard selection; no-JS original forms and native staff-creation disclosure. |
| Syntax/contract | 122 PHP files linted; new JavaScript syntax checks and workspace presentation contract passed. |
| Premium static audit | Strict mode: zero findings/errors/warnings. This does not replace browser or security tests. |
| Pilot preservation | Every table's rows/definition, cover inventory and private-config hash match the private pre-redesign baseline. No test accounts or fixtures added to pilot. |

Actual screenshots were inspected for directory/action panels, profile, About,
audit, recovery, managed books, request cards, logout and a narrow keyboard-selected
review form. Desktop and phone-sized images are fictional fixtures, not actual
school records. The screenshot suite retains both-theme captures of every route.
Visual review caught and corrected an overbroad panel rule affecting logout;
follow-up browser runs passed. Narrow screens were also changed to start list-first.

`public/assets/app.css`, `views/pagination.php` and `views/catalog_pagination.php`
remain unchanged by this increment. The new stylesheet does not override pagination
classes. The existing renderer's search/status/book context is covered by the suites.

## Retained resources and reproduction

Isolated source:
`C:\Users\63961\Desktop\Emman\KaAklat2.0\private\workspace-refresh-62c9d0a6f356\source`

Parent database: `kaaklat_workspace_test_62c9d0a6f356` (empty schema, no seed).
Private setup/baseline locator: `private/workspace-refresh-current.json` and
`private/workspace-refresh-62c9d0a6f356/baseline-private.json` in the maintained root.
Credentials, row fingerprints, covers and fixture passwords stay ignored/private.

Authoritative workflow HTTP database: `kaaklat_acceptance_c8c4cdc693ec`.
Pagination HTTP database: `kaaklat_acceptance_e2415c0e4af8`.
Pagination browser database: `kaaklat_acceptance_41cdcf29332e`.
Theme browser database: `kaaklat_acceptance_746a3c4aae10`.
Final workspace browser database: `kaaklat_acceptance_fd6a3b17b62f`; results and all
route screenshots are in `tmp/student-profile-browser-fd6a3b17b62f/` under the
isolated source, including `workspace-browser-results.json`.
Earlier development browser
runs retained `kaaklat_acceptance_0d0e30b01435`, `2234b7ca27a6`, `af2503af18a1`,
`de2feae85e18` and `0f119c7bfc6c` (each shorthand has prefix `kaaklat_acceptance_`).
All matching fixture directories are below this isolated source's `tmp/` directory.
One final pre-fix browser run (`kaaklat_acceptance_3e7cf5fb06c1`) failed a focus
assertion because the test checked the newly parsed error summary before deferred
handlers completed. The test now waits for document completion before checking focus;
the complete final suite passed all78 layouts/30 interactions. This failed run is
retained and is not counted as a pass. Screenshot capture also waits for the existing
140ms color transition to settle before capturing a changed theme.
Nothing was dropped or recursively cleaned up. Inspect fixture locators and verify
scratch names before a later separately authorized cleanup; never clean pilot data.

Raw final HTTP output: `tmp/workspace-workflow-http.txt` and
`tmp/workspace-pagination-http.txt` under the isolated source. Browser fixture
directories contain the JSON results, screenshots and private fixture locator.
The strict static audit is retained in the task workspace as
`workspace-static-audit.json`.

Run these only from the retained isolated source, whose private configuration points
to the separately named test database. Do not run fictional cover fixtures from the
working pilot source.

```powershell
cd 'C:\Users\63961\Desktop\Emman\KaAklat2.0\private\workspace-refresh-62c9d0a6f356\source'
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
& 'C:\php-8.4.14\php.exe' tests/shared_pagination_http_test.php
node tests/workspace_contract_test.cjs
node tests/workspace_browser_check.cjs
node tests/theme_browser_check.cjs
node tests/shared_pagination_browser_check.cjs tmp/student-profile-browser-41cdcf29332e/fixture.json
```

The browser scripts create or use explicitly named fictional databases, start
loopback servers/headless Chrome with hidden windows, and terminate their own
processes. Existing tests own their mutation assertions; the new workspace suite
does not approve, collect or return a pilot record.

## Limits and local acceptance

No zero-bug, full accessibility, hosting or performance claim. Actual Chrome400%
zoom, real phones and screen readers remain unverified for these changes. Geometry
checks are simulated viewport checks. Sampled contrast does not certify all states.
Concurrency was not rerun for this presentation-only increment: no circulation
service, locks, transactions or audit writes were changed. Existing HTTP and
regression tests exercised their success/failure behavior. Usage tool unavailable
at start and finish; no percentage is inferred. No commit, push or deployment.

1. Run the maintained root with `& .\start-local.ps1`; open localhost and refresh
   with Ctrl+Shift+R to load the new assets.
2. Use each role's existing permitted pages. Select a row, inspect its context,
   then use Back to list. Browse search/status/page links; pagination looks the same.
3. Submit an intentionally invalid form using a fictional account/request. Check
   retained values, the linked error summary and the affected field. Avoid changing
   real pilot records merely to test a design.
4. Repeat at360px,768px, actual400% zoom and on a real phone in both themes. Use
   Tab/Shift+Tab, Enter and Space. Disable JavaScript and verify original forms/native
   disclosures remain accessible. Report the page, role and steps for any issue.
