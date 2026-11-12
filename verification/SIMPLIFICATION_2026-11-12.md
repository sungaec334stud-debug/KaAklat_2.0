# Behavior-preserving simplification — 2026-11-12

## Outcome

Completed the five audited simplification areas without a rewrite: removed two unused return-date assignments, reused catalog_like for unified search, shared student summary items/overdue markup with existing page labels, shared display-only UTC/Manila formatting, and consolidated low-level Chrome protocol plumbing across three browser suites. Original24 presentation snapshots remain byte-for-byte identical. Verified32 regression scripts,763 HTTP checks(390 workflow+373 pagination),434 browser combinations(248 theme+96 catalog+90 pagination),28 pagination keyboard/no-JS navigations,12 theme-controller checks and122 PHP lints. All three browser suites reported zero runtime exceptions; the theme suite's sampled contrast checks had no failures. Browser protocol error/response-matching checks passed. Pilot table rows/definitions, covers and configuration match the pre-refactor baseline; no test accounts/data added to pilot. No schema, authorization, CSRF, circulation mutation, locking, deadline math or CSS changes. Actual400% zoom/real-device/screen-reader and fresh performance checks remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/SIMPLIFICATION_2026-11-12.md.

## Audit decisions and changes

1. Deleted only the two unused pickup/due display assignments in public/returns.php. return_card_html already performs those calculations. No action/service write changed.
2. Catalog search now calls the existing catalog_like helper instead of duplicating its strtr mapping. The parameterized SQL, literal !/%/_ semantics and result selection remain unchanged.
3. views/portal_ui.php shares student count-link items and overdue notice markup. Dashboard keeps Ready to collect; profile keeps Ready for pickup, existing own-only counts, active-minus-overdue calculation, notice order and empty state. No new DB query.
4. views/time.php shares display-only UTC-to-Manila conversion. Audit retains seconds; dashboard retains omitted year/uppercase AM-PM; request/review/return formats retain their original year/lowercase am-pm. Public request/dashboard wrapper functions remain compatible. Date-only due-date rendering, profile registration time object/ATOM output and pickup's combined48-hour expression remain as before: forcing them into the helper added complexity, so that attempted pickup expression change was not retained. All circulation deadline calculations remain untouched.
5. tests/browser_protocol.cjs shares request IDs/pending replies, Runtime error capture, evaluate/keyboard/screenshot primitives. The three existing browser suites retain their own process startup, isolated fixtures, ports, navigation waits, assertions and cleanup. The theme suite retains non-promise evaluation; discovery/pagination retain awaited evaluation. No npm package added. The three suites plus shared protocol helper total24984 bytes versus25415 before:431-byte reduction, excluding new verification tests. This is a small maintainability improvement, not a wholesale code-size reduction. Readable helpers/tests can add lines while removing duplicated logic.

No production dependency, schema, auth/current-session check, CSRF, permission, lock/retry, audit/transaction, backup/recovery or CSS logic was removed. The audit found no justification for aggressive deletion of working safeguards. Existing tests were not weakened to pass.

## Executed checks

- New presentation test first failed for missing views/time.php, then passed after implementation. Exercises leap-day/year UTC/Manila rollover, screen-specific formats, invalid date exception behavior, zero/singular/plural overdue markup, summary routes and own-count mapping.
- New browser protocol test first failed for missing module, then passed. Covers out-of-order response matching, protocol/script error propagation, Runtime exception capture, evaluation modes and Enter key dispatch.
- Captured24 pre-refactor fictional presentation outputs, then compared post-refactor SHA256 lists: all identical. Includes pending/active profiles with zero/one/multiple overdue loans,10 request lifecycle states,4 return-card/error/confirmation combinations and4 date outputs. Snapshot helper/hashes retained privately. Its initial capture omitted starting a session and failed before producing a usable baseline; corrected to use an isolated temporary session before capturing the baseline. No auth safeguard bypassed.
-32 regression scripts passed in isolated config. Includes existing auth/role/ownership, transaction failure, original request/pickup/return/cancellation tests, catalog lifecycle/wildcards and pagination; new presentation test added to runner.
-390 existing HTTP checks passed in kaaklat_acceptance_3770ad7cba93.373 pagination HTTP checks passed in kaaklat_acceptance_a098856c3ac5. Signed-out/wrong-role restrictions, current sessions, student ownership, CSRF/forged inputs, field errors, preserved values and workflows exercised.
- All three real headless Chrome suites passed with extracted protocol helper:248 theme cases,96 catalog cases and90 pagination cases=434 combinations. All reported0 Runtime exceptions. Theme suite reported no sampled rendered-text contrast failures and also exercised guest pages, storage failure, system theme and student/librarian invalid-input focus. Catalog tested keyboard disclosure/error links and JS-disabled controls; pagination tested28 native keyboard/no-JS navigations. Desktop/narrow Light/Dark geometry and44px links retained. Final fictional desktop dashboard and narrow Dark staff footer screenshots inspected.
-12 standalone theme-controller checks and browser protocol unit check passed.
-122 PHP files linted. JavaScript syntax checks for the three modified browser suites passed; strict premium static audit tmp/simplification-audit.json had0 findings/errors/warnings. git diff --check passed.
- Final read-only all-row/table-definition pilot fingerprints, private cover inventory and configuration SHA matched pre-change baseline. No fixture accounts/records, password reset, session revocation or migration applied to pilot. Temporary display-snapshot sessions were destroyed after use.

## Isolation and reproducible commands

Protected root: private/simplification-66c629b68dcf; isolated source under source; newly created base DB kaaklat_simplify_test_66c629b68dcf. Config/cookie/covers/sessions separated; no seed. Logs at root: regression.log,http.log,pagination-http.log,theme-browser.log,catalog-browser.log,pagination-browser.log. baseline-private.json and presentation-before.json/presentation-after.json/presentation-snapshot.php are private evidence. Never publish private fixture.json credentials/config.

Retained newly generated acceptance DBs:

- kaaklat_acceptance_3770ad7cba93:workflow HTTP.
- kaaklat_acceptance_a098856c3ac5:pagination HTTP and browser; source/tmp/student-profile-browser-a098856c3ac5.
- kaaklat_acceptance_077511f90583:theme browser; source/tmp/student-profile-browser-077511f90583.
- kaaklat_acceptance_06f629a6e5c1:catalog browser; source/tmp/student-profile-browser-06f629a6e5c1.

No existing scratch resources/backups deleted. Retain for review; later cleanup must target individually verified scratch names, never a prefix wildcard or kaaklat_pilot.

From the isolated source with its scratch config:

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
& 'C:\php-8.4.14\php.exe' tests/shared_pagination_http_test.php
node tests/browser_protocol_test.cjs
node tests/theme_test.cjs
node tests/theme_browser_check.cjs
node tests/catalog_discovery_browser_check.cjs
# Use the fresh private fixture path printed by the pagination HTTP test:
node tests/shared_pagination_browser_check.cjs tmp/student-profile-browser-a098856c3ac5/fixture.json
```

The browser suites preserve existing PHP ports8034/8035/8037 and debugger ports9358/9362/9364; each terminates its own helpers. HTTP test ports8017/8036 remain unchanged. Rerunning fixture scripts creates new DBs, not pilot records.

## Remaining limits

No behavior change or regression was detected in executed checks; this is not a guarantee of zero bugs. Independent-process concurrency suites were not rerun because no circulation mutation, SQL lifecycle predicate, lock/retry or transaction changed. If those areas are later refactored, execute the documented concurrency suites before accepting that increment. Actual400% zoom, real phones, screen readers, full-state contrast, new performance measurements and hosted compatibility remain unverified. Existing participant acceptance is historical, not evidence of this new run. Hosting deferred. Usage tool unavailable, no inferred percentage.

For local review, run the maintained start-local.ps1, sign in normally and check dashboard/profile count links and original wording, request history dates, librarian reviews/returns and administrator audit timestamps. No visible redesign is expected. Pilot small lists/availability remain genuine. No commit/push performed.
