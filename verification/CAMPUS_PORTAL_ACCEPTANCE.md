# Campus portal acceptance — 2026-11-04

User approved the campus-portal mockup, then requested application to the real website while preserving core functionality. Production diff is shared CSS/layout, static decorative icons, dashboard/profile summary and task wrappers. No app service, SQL, schema, permissions, form handler or database change. Existing blue/white/gray palette and SVG logo retained; no dependencies or external assets added.

## Executed evidence

- Before implementation: 23 regression scripts and 133 HTTP assertions passed. Baseline HTTP scratch: kaaklat_acceptance_32f9754a3837.
- After implementation: 24 regression scripts passed, including 28 new icon/escaping/presentation checks. Expanded actual HTTP suite: 181 assertions passed. Scratch: kaaklat_acceptance_1c391b5bbc07. Tests exercise sign-in, account permissions, requests/decisions/pickup/return/cancellation, CSRF/forged inputs, student ownership, recovery/staff/audit/profile and retained role navigation.
- PHP lint: all 93 PHP files in app/public/views/tests/bin passed. git diff --check passed (line-ending normalization warnings only).
- Final rerun after the checkbox layout correction:24 regression scripts and181 HTTP assertions passed again; latest HTTP scratch kaaklat_acceptance_d9ecf47cc2dc. Normal application health on port8000 returned HTTP200. Owned fictional fixture server21436 on port8021 stopped after browser logout; temporary viewport reset and agent tab closed. Fixtures/cover manifest retained, no data cleanup.
- Seven independent-process concurrency suites passed, 17 scenarios: pending/duplicate requests, same-request/last-copy/last-allowance decisions, pickup/expiry, returns/allocation, cancellation/pickup/allocation, staff safeguards and recovery races. Retained scratches: kaaklat_cancel_test_3d6d0056f6cd, kaaklat_request_test_4c65f7e74fd0, kaaklat_pickup_test_2c79cc0a3bda, kaaklat_recovery_test_9da6672e833c, kaaklat_decision_test_2f2678ba4bbb, kaaklat_return_test_c9da4a088692, kaaklat_staff_test_c8973c847230.
- Python strict audit explicitly scoped to public/assets (app.css, landing.css, forms.js). One finding at unchanged forms.js line58 is a reviewed false positive: `link.href = '#' + field.id` constructs a real field target, not an empty link. Actual browser keyboard activation focused note-1 and receipt-3. No false claim of a zero-finding full PHP design audit; PHP is unsupported by that checker.
- Fresh read-only whole-change review: no important defects; independently reran 28 presentation checks and diff whitespace check. Documentation status correction requested and completed.

## Actual browser observations

In-app browser successfully reached loopback fixture server on port8021; the older Chrome URL-confidence blocker did not occur on this surface. Used newly generated fictional database kaaklat_acceptance_99a0ecbe6192 (four accounts, eight books with generated covers and pending/approved/active/returned/rejected history). No real/demo records used.

| Screen/check | Observed result |
| --- | --- |
| Student dashboard | Desktop and360px: navigation, long display-name wrapping, summaries and tasks visible. |
| Student active-loan history | 360px: wrapping tabs, cover, status, progress and history present. |
| Student profile | 360px: own information wraps in stacked rows. |
| Catalog | 768px: filters and cover grid visible; protected fictional covers loaded in visible rows. Offscreen lazy images are not classified as broken. |
| Librarian dashboard | 1440px: sidebar, counts and six task cards visible. Corrected inherited space-between sidebar gaps. |
| Review decisions | 360px: changed7 days to6 without explanation; server refused, retained6, focused summary. Tab/Enter on summary link focused note-1. |
| Pickups | 768px: allocated copy, borrower, deadline and actions visible. |
| Returns | 360px: invalid unchecked receipt refused; summary link Tab/Enter focused receipt-3. Confirmed field-error compression64px inside284px label; CSS normal-flow correction made it284px, then same measurement passed. No loan was returned by browser tests. |
| Manage books | 360px: search/cards/actions visible. |
| Staff accounts | 1440px: admin-only controls visible with original fields/roles. |
| Landing | Desktop and360px: existing illustrative images, copy, login/register actions visible. |

Measured inspected mobile/tablet document scroll widths350/758 against inner widths360/768, respectively, without horizontal overflow. These are representative screen checks, not a claim that every state at every breakpoint was visually tested. Saved actual fixture screenshots: tmp/campus-dashboard-verified.jpg and tmp/campus-landing-verified.jpg. No protected demo content exported.

## Remaining acceptance and operational limits

On 2026-11-05, the user reported both manual checks passed in Chrome. At 400% zoom, the student dashboard, catalog filters, and librarian review and return forms remained readable and usable. With JavaScript disabled, the librarian review form rejected a changed approval duration without an explanation, displayed the error, and retained the entered duration. Broader keyboard, screen-state, and OS accessibility settings remain outside this check.

Manual check on the normal local site: Ctrl+F5; sign in separately as student/librarian/admin; inspect dashboards and their task pages at360px/768px and400% Chrome zoom. Use Tab/Shift+Tab/Enter/Space for navigation and errors. Disable JavaScript, submit invalid request/decision/receipt forms, confirm values and useful errors remain. Exercise recovery secrets only with disposable accounts. Do not change demo circulation merely for a visual check.

Private fixture manifest (includes disposable credentials) is under tmp/student-profile-browser-99a0ecbe6192/fixture.json; never commit it. Its covers array names only the eight new generated images. Test resources/logs are retained; no database or file cleanup ran. Stop only this increment's owned fixture server, then review that manifest before any later explicit cleanup; never remove demo files or other scratch databases. No deployment, production compatibility or real-data restore claim. Performance, controlled recovery, hosting and evaluation remain outstanding.
