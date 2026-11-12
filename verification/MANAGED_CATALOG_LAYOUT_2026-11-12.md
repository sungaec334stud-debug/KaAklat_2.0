# Manage books mockup correction

The preceding workspace release did not implement the approved Manage books split
layout: the controller still chose list OR editor. This corrects that gap, rather
than attributing it to caching or to a browser difference.

The server now renders the bounded catalog directory alongside the selected book's
existing form. Covers, title/author, category and derived available/active-copy counts
are real query results, not mockup values. Native Manage links retain query/page and
focus the editor. Search, list pagination, copy pagination, close and successful
mutation redirects preserve appropriate context. Both sides work without JavaScript.
Original CSRF/POST fields, validators, librarian checks, copy/cover/archive services
and transaction handling remain authoritative. No new schema or editable copy total.

At desktop width the directory and blue-edged selected-title panel are side by side;
at900px and below they stack. The existing category datalist and derived-copy/add-copy
model are retained instead of copying the preview's simulated controls. Pagination
renderer and app.css have no diff; the browser tests now select independently labelled
Copy pages and Managed book pages navs, retaining all prior navigation assertions.

## Executed checks

- 33 regression scripts passed, including the new managed catalog escaping/context/
  selected-state/empty-state view test (first observed failing before implementation).
- 390 existing workflow HTTP assertions passed, including catalog creation, edit,
  add copies, cover replacement, archive, wrong roles, CSRF and circulation workflows.
- 373 pagination HTTP assertions passed.
- 24 new Chrome layout combinations(4 states × Light/Dark ×1440/768/360px) passed.
  Native no-JS Manage selection retained search and selected book; original CSRF/book
  fields retained; invalid save preserved values; valid edit retained editor/context;
  add copies refreshed derived counts in both panels. Zero runtime exceptions.
- 90 pagination Chrome combinations and28 keyboard/no-JS page navigations passed;
  zero runtime exceptions. Independent catalog/copy nav selection verified.
- 124 PHP files linted; JavaScript syntax and existing workspace contract passed.
  Strict premium static audit had zero findings. Rendered desktop screenshot inspected
  against the approved mockup; actual viewport stacking checked by browser geometry.

Actual400% browser zoom, real phone, screen reader, broad manual acceptance and new
performance measurements remain unverified. No concurrency claims: no lock/service/
transaction code was changed. Usage reporting unavailable; no invented percentages.
No commit, push or hosting work.

## Pilot activity and preservation

Fresh private baseline under `private/managed-layout-5925da95d84c/`.
Full baseline equality did NOT pass: existing-account activity during work added two
requests, approved three requests and recorded five audit events/three allocations.
Read-only audit timestamps/actor-role metadata explain these differences. This is
consistent with concurrent tester activity; no test script used pilot accounts.
Those records were retained, not reset or restored over.

All other table fingerprints(including users, books, copies, categories/authors),
covers and private configuration matched the baseline. No fictional browser fixture
accounts found in pilot. Structural SHOW CREATE TABLE definitions match the isolated
current schema after excluding normal AUTO_INCREMENT values. Inventory 'definition'
metadata includes row counts and auto-increment hashes, so its difference is not by
itself evidence of a structural schema migration.

## Retained isolated resources and reproduction

Source: `C:\Users\63961\Desktop\Emman\KaAklat2.0\private\managed-layout-5925da95d84c\source`

- Empty parent schema DB: `kaaklat_managed_test_5925da95d84c`.
- Browser fixture DB: `kaaklat_acceptance_2187be9bd98f` (final); preliminary
  `kaaklat_acceptance_893d50bb3f40` also retained.
- Workflow HTTP DB: `kaaklat_acceptance_e83ae21c573c`.
- Pagination HTTP/browser DB: `kaaklat_acceptance_b6951e0ee7e9`.
- Raw test outputs: isolated source `tmp/managed-regression.txt`,
  `tmp/managed-workflow-http.txt`, `tmp/managed-pagination-http.txt`.
- Browser screenshots/results: isolated source
  `tmp/student-profile-browser-2187be9bd98f/managed-browser-results.json` and sibling PNGs.
- Private baseline/current-observed fingerprints are retained in the parent directory;
  these contain private metadata and must remain outside Git/public paths.

Run only from that explicitly isolated source/configuration:

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
& 'C:\php-8.4.14\php.exe' tests/shared_pagination_http_test.php
node tests/managed_catalog_browser_check.cjs
node tests/shared_pagination_browser_check.cjs tmp/student-profile-browser-b6951e0ee7e9/fixture.json
```

No scratch database or backup was dropped. Inspect these exact locators before any
later separately authorized cleanup; do not remove pilot rows to make a hash match.

Local review: hard-refresh `/manage_books.php`, choose Manage, and verify the list stays
left of the editor on desktop. Search, select another book, check the selected tint,
then use catalog and Copies page links independently. Review narrow widths and actual
zoom in both themes. Use fictional isolated records for save/archive acceptance.
