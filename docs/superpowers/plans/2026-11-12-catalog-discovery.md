# Catalog discovery implementation plan

**Goal:** Implement the user's approved sorting/search/year-range/availability/results-context request without changing pilot data or circulation policy.

**Architecture:** Extend app/catalog.php as the canonical read-only query/parameter/URL owner. Keep the existing derived availability selection shared between listing, counts and details. Add views/catalog_filters.php for native GET controls, disclosure, removable labels and safe feedback. public/catalog.php remains the authenticated controller. views/catalog_pagination.php preserves the canonical query context. Reuse existing CSS tokens and forms.js summary focus; no new dependencies or asynchronous search.

**Spec:** The user's pasted request, recorded in prompt.md on completion. Plain PHP/PDO,12 titles/page, archived titles excluded, 100-character search/filter strings, years1–current Manila year, native Apply filters and JS-disabled operation. New allowlists: sort=title_asc/title_desc/year_desc/year_asc; available empty/1/0. Legacy year remains accepted and maps to matching inclusive bounds; contradictory legacy/range inputs are refused. Detail links carry allowlisted catalog parameters directly, with no arbitrary return URL.

## Tasks

- [x] Add failing unit and isolated SQL tests for sorting, year ranges, validation, totals, availability lifecycle and canonical navigation.
- [x] Extend query validation/ordering/availability and URL helpers. Unknown years last; title and ID tie-breaks; no author joins duplicating results.
- [x] Build compact catalog controls, result ranges, removable labels, truthful unavailable badge and details/back context. Preserve existing footer page summary for compatibility.
- [x] Extend fictional HTTP/browser acceptance for all roles, links, no-JS, themes, keyboard and widths; run relevant and full regressions plus lint.
- [x] Compare pilot preservation fingerprints, update SRS/design/UX evidence and handoff, and leave no commit/push.

Testing takes place in a separately named verification database and source copy. Never run fixture writes against kaaklat_pilot. Keep previous uncommitted documentation/data additions intact. Scope excludes autocomplete/fuzzy search/waitlists/notifications or mutation changes. If unfinished, document exact remaining work and observed failures.
