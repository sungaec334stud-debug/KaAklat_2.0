# Catalog discovery verification — 2026-11-12

## Result and boundaries

Implemented catalog discovery: four deterministic sort orders, title-or-author search, inclusive/open year ranges with legacy exact-year compatibility, three availability choices for every authenticated role, distinct result totals, removable filters and preserved results/detail/back context. Native GET controls work without JavaScript; existing availability and request authorization remain authoritative. No schema or pilot data migration. Verified30 regression scripts,390 HTTP checks,115 PHP lints and96 authenticated Chrome role/theme/viewport combinations with zero runtime exceptions; keyboard/error focus and JS-disabled apply/removal/clear checked. Fifteen final read-only pilot catalog requests passed. Original accounts/rows,86 books/273 copies,87 cover files and configuration preserved; one independently registered/activated student during work was retained, bringing accounts to4. Actual400% zoom, real-device/screen-reader acceptance and updated performance timings remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/CATALOG_DISCOVERY_2026-11-12.md.

The production change is read-only catalog queries, validation, canonical URL helpers and native form/view styling. No schema, circulation mutation or availability calculation was replaced. Sort identifiers use fixed mappings; user search values are parameters with literal wildcard escaping. Archived titles stay excluded. Unknown publication years sort last in both year orders. Title articles A/The retain their existing literal ordering. Legacy year links map to equal bounds; conflicting old/new bounds fail validation.

## Executed evidence

- Red/green: new validation tests initially rejected available=0, then passed after the approved implementation.
-30 regression scripts passed, including new isolated SQL lifecycle/sort/count tests and native form/URL validation tests.
-390 named HTTP checks passed: active/pending students, librarian and administrator; availability choices, authenticated access, forged unavailable request refusal, context retention, escaped inputs and invalid ranges.
-115 maintained PHP files passed lint; browser JavaScript syntax check passed.
-96 headless Chrome combinations:4 roles ×2 themes ×4 pages ×3 widths(1440/768/360). Pages: normal catalog, filtered page2, unavailable details and invalid year range. Zero Runtime exceptions and no document overflow at sampled widths. Keyboard disclosure, linked summary focus and native JS-disabled Apply/removal/Clear search/Clear all passed. Desktop/360 screenshots inspected after pairing year-bound fields in a single fieldset.
- Strict frontend static audit:0 findings/errors/warnings. This is limited static evidence, not a comprehensive PHP/security/accessibility audit.
-15 final read-only current-pilot catalog requests across3 roles returned expected content. Original data/config/covers preserved as detailed below. No PHP Fatal/Warning/Notice/Parse diagnostics found in the local startup error log during the final check.

The first browser automation attempt omitted native Enter text; the harness was corrected and rerun. The first extended HTTP attempt reused an account whose session earlier security tests had revoked; the new checks now use separate fictional sessions. Neither harness correction relaxed application authorization.

## Retained isolated resources and reproduction

Protected root: private/catalog-discovery-2766b3d7e6f8. A separate source/config/cover/session copy is under source; base database kaaklat_discovery_test_2766b3d7e6f8. No seed import and no test fixture writes to kaaklat_pilot.

Final HTTP database: kaaklat_acceptance_fa174e5c431a. Final browser database: kaaklat_acceptance_8b013dd645dd; browser directory source/tmp/student-profile-browser-8b013dd645dd. Earlier HTTP/browser trial databases(c77d960c1f55,68fd9c34f2e3) and regression scratch resources are retained; exact generated names/output are in regression.log/http.log/browser.log. Do not delete databases by prefix or run these tests with pilot configuration. Private fixture.json contains fictional credentials and must remain outside Git/public.

From the protected source copy, using its isolated configuration:

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
node tests/catalog_discovery_browser_check.cjs
node --check tests/catalog_discovery_browser_check.cjs
```

Browser script creates its own fictional fixture, starts PHP on8035 and headless Chrome remote debugging9362, then stops its own helper processes. The HTTP runner records its separately generated scratch DB. Keep these resources for review; eventual cleanup must target the individually verified scratch names/directories, preserving current pilot and backups. Never run migration/seed/reset/restore against pilot as part of cleanup.

Lint was run over maintained PHP sources with explicit PHP8.4.14, stopping on any nonzero exit. Static audit used the installed frontend-design-premium audit_project.py in strict mode; output tmp/catalog-discovery-audit.json. This audit artifact is local and ignored. Browser screenshots/report and raw logs remain under the protected root, not public assets.

## Preservation and current operation

Initial fingerprints captured all pilot table definitions/rows, covers and private configuration. A student independently registered and was activated during verification: account.registered at06:05:01UTC and student.activate at06:05:40UTC. Those two appended audit events match that new student; no fixture-prefixed pilot account was found. All original3 user rows including password hashes and original9 audit rows remained identical. All other table rows/definitions,86 titles/273 copies,87 cover files and private configuration matched the original baseline. The legitimate added student and associated audit history were retained; current4 users/11 audit events were accepted as the preservation checkpoint. No rollback of user activity occurred.

Private baseline-private.json/account-activity-private.json/pilot-preservation.json record this verification without publishing account details or secrets. Final report passed15 read-only requests using temporary operator sessions, destroyed afterward. These reads are not actual participant-password login evidence. Root application remains connected to kaaklat_pilot on8000; use start-local.ps1. No source/config/session-cookie migration was performed.

## Remaining limits and local review

Actual400% browser zoom, real phones, screen readers and all interactive-state contrast have not been observed for these new controls. New query-path performance was not measured against the SRS timing target; earlier performance evidence predates this query change. Seven prior concurrency suites were not rerun for this read-only increment; existing circulation mutation code stayed unchanged. Full hosted compatibility remains deferred. No zero-bug or production-deployment claim.

Sign in using normal student/librarian/administrator accounts. Open Catalog, try the four sorts, combine search/category/year bounds and the three availability values, open details and return to results. Apply or remove a filter from page2 and confirm page1. Clear all should reset search/filters/sort. Test reversed years and follow the error link. The pilot unavailable view may legitimately be empty; use the retained fictional acceptance fixture to inspect unavailable states without creating pilot loans.

Usage tool unavailable; no percentage inferred. No commit/push performed. Prior unrelated changes remain preserved.
