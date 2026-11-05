# Campus portal implementation plan

**Goal:** apply the user-approved campus-portal mockup to the actual PHP website without changing core behavior.

**Spec:** docs/superpowers/specs/2026-11-04-campus-portal-design.md, approved by user: "I like this design apply it to the real website and make sure there are no bugs and errors with the core functionalities and logics".

**Architecture:** existing plain PHP/PDO backend untouched. Shared SVG presentation helper + existing layout, replacement consolidated app/landing CSS and small presentation wrappers in dashboard/profile. Current routes/actions/field names/IDs/permissions/data remain authoritative. CSS runtime tokens stay canonical and DESIGN mirrors them. No dependencies, migration, commit or push.

**Ruling:** execute in the existing authorized workspace to preserve the running local setup and uncommitted prompt/design proposal; do not create a new checkout or copy private config. No merge/branch operation is needed for this presentation-only increment. Preserve research output. Existing vertical textarea resizing stays: replacing it with new JS would change established input behavior.

- [x] Baseline23 regression scripts/133 HTTP assertions and fresh lint; record failures before editing UI.
- [x] Write failing presentation/accessibility tests for allowlisted decorative icons, escaping and role-specific shell navigation; extend actual HTTP checks to verify semantic current-route names rather than brittle literal markup.
- [x] Add views/portal_ui.php and update layout with icons, role caption, owner-safe identity and nonsticky contextual topbar. Maintain label/route/current-state/skip/error semantics.
- [x] Replace shared app.css with campus tokens and consolidated rules, maintaining every existing component selector and natural scrolling. Restyle landing.css; preserve imagery, links and borrowing copy. Add only dashboard/profile visual wrappers, never mutation/query changes.
- [x] Verify with actual regression/HTTP/lint and scoped static CSS/JS audit (PHP unsupported explicitly recorded). Exercise/render real fixture pages at desktop/768/360 when browser tools permit; stop on a browser policy denial. Record exact visual/keyboard/zoom limits; do not reuse old acceptance as proof.
- [x] Fresh whole-change review, fix confirmed defects, rerun affected tests. Update DESIGN/UX owner map, PROGRESS/current summary, TEST_PLAN, CHANGELOG and append actual prompt/outcome. No claim of zero bugs; favorites/performance/hosting remain separate.

**Review focus:** no hidden fields/records/actions, unchanged POST/CSRF/form/error targets, plain-text nav accessible names despite SVGs, correct role isolation, no mobile/zoom clipping, unchanged authenticated cover and pagination behavior, no private data/assets in static exports.

Execution ledger: baseline23/133 PASS; helper checks RED missing helper → GREEN28; implementation24/181/93lint PASS; seven concurrency suites17 scenarios PASS. Browser correction: receipt-error width64px (failing full-width expectation) →284px/284px PASS after normal-flow CSS. Final fresh review: no important defects; stale design status corrected. Ruling: retain existing textarea behavior and current workspace per user preservation requirement; no new dependencies or copied private configuration. Cost if wrong: less isolation from unrelated edits; final diff scope reviewed. Browser zoom/JS-disabled/full-state limits remain explicit acceptance tasks, not claimed passes. No commit/push or cleanup.
