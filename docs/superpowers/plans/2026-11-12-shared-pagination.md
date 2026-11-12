# Shared numbered pagination

User requested the existing catalog pagination across every paginated list. Reuse its CSS, bounded page-number window, current-page semantics and native links. Preserve per-page limits, access checks, query filters and all mutations. No schema/config/data changes.

Canonical owners: app/pagination.php for read-only count/page normalization; views/pagination.php for shared escaped navigation; views/catalog_pagination.php remains the catalog adapter. Existing list services keep returning rows for compatibility and optionally supply page metadata to controllers. Count the identical filtered selection, including student ownership. Clamp stale out-of-range pages to the last page. Empty lists use page1of1. Keep existing lookahead rows for old callers.

Scope: catalog, student request history, students, staff, audit, request reviews, pickups, returns, managed books and copies. No pagination for static forms or dashboard previews. Reuse existing Light/Dark tokens and 44px links, with wrapping at narrow widths and no JS dependency.

- [x] Add failing shared-page rendering/state tests.
- [x] Extract catalog renderer and add optional counted paging to existing list services/controllers.
- [x] Exercise isolated HTTP multi-page fixtures, search/status preservation, ownership and access denial; regressions/lint/static audit and browser narrow/theme/keyboard checks.
- [x] Update current handoff, test/design contracts, changelog and prompt evidence; no commit/push.

All fixture writes must target separately named scratch databases through isolated configuration, never kaaklat_pilot. Preserve earlier uncommitted catalog changes. Usage tool unavailable; actual zoom/real-phone acceptance reported separately.
