# Shared interface owners

Updated 2026-11-04. English school demonstration, Asia/Manila. This is an owner map, not another source of borrowing policy. SRS.md and SCHEMA.md remain authoritative for eligibility, permissions, lifecycle, limits and lock order. Hosting stays deferred.

| Capability | Canonical owner | Presentation contract |
| --- | --- | --- |
| Current identity, roles and sessions | app/auth.php and existing account authorization helpers | layout.php reuses freshly verified identity; navigation never grants permission. |
| Navigation, route indication and skip target | views/layout.php | Same role workflow routes plus authenticated Settings; visible names, unique current route, utility divider, compact desktop sidebar and visible horizontally scrollable narrow navigation. No hidden mobile menu. |
| Appearance preference | public/assets/theme.js, views/settings.php and public/settings.php | Authenticated read-only settings; native radio cards and polite save feedback. Allowlisted browser-local System/Light/Dark, no identity storage or account mutation. Early effective theme, system-change handling, storage-error feedback, JS-free device fallback. |
| Decorative route icons and linked summaries | views/portal_ui.php | Static allowlisted SVG; hidden from accessibility tree, nonfocusable. Escape text/URLs; consume existing counts, do not query or mutate. |
| Counts | existing dashboard_counts calculation | Profile and dashboard share the calculation and summary presentation; own-student scope stays unchanged. |
| Colors, typography, spacing, focus and responsive shell | public/assets/app.css | DESIGN.md mirrors tokens. Natural scrolling, wrapping tabs, 44px actions, 16px inputs, checkbox errors in normal flow. |
| Landing imagery and bounded motion | views/landing.php, public/assets/landing.css | Existing illustrative images/copy; reduced-motion rules; no protected covers or new promises. |
| Forms, validation and feedback | existing page/service validators, public/assets/forms.js | Server remains authoritative; preserve field names/IDs, POST/CSRF, entered values, summary/field links and redirects. No new autosubmit or textarea behavior. |
| Catalog search, filters and results context | app/catalog.php; views/catalog_filters.php; views/catalog_pagination.php | Native GET form/disclosure, allowlisted sort/availability/year bounds, distinct totals, local detail/back links and page-one removal/apply. JS-free behavior; existing forms.js owns linked summary focus. |
| Other selected tabs | existing page GET allowlists | Retain URLs and current selections. |
| Requests, decisions, pickup, return and cancellation | existing app circulation services | No UI-owned counters, eligibility, allocation or transaction logic. |
| Private account/history errors | existing safe error/escaping helpers and protected pages | No new private data exports, logs or profile-selection parameters. |

No new async requests, dialogs, loading states, dependencies or localization system were introduced. Existing vertical textarea resizing is retained. Appearance extends the shared CSS owner to semantic light/dark palettes, including controls/status/focus/scrollbars and the guest landing variant. Actual verification and remaining manual coverage are recorded in verification/CAMPUS_PORTAL_ACCEPTANCE.md and verification/THEME_NAVIGATION_2026-11-10.md.


Shared pagination owner (2026-11-12): views/pagination.php renders all numbered list navigation; app/pagination.php owns authorized filtered totals and bounded page state. Catalog adapts its existing URL helper. Controllers pass only their validated search/status/book context; no arbitrary return URL. All links are native GET, current page uses aria-current, and existing app.css owns responsive Light/Dark/44px styling. Row-only service callers retain their legacy lookahead behavior; counted page controllers clamp stale pages. Security and circulation sources remain SRS.md/SCHEMA.md and existing service authorization. Verification: verification/SHARED_PAGINATION_2026-11-12.md.


Presentation owner update(2026-11-12): views/portal_ui.php shares only student summary item mapping and overdue notice markup; authorization/count calculation remains in existing services. views/time.php shares display-only UTC/Manila formatting without touching due-date/expiry calculations. Exact labels, error states, routes and ordering retained. Verification:24 identical presentation snapshots and the named full regression/HTTP/browser reruns in verification/SIMPLIFICATION_2026-11-12.md.

## Workspace presentation — 2026-11-12

Canonical optional directory/inspector enhancement: public/assets/workspace.js; route-scoped presentation: public/assets/workspace.css, loaded by views/layout.php before forms.js. Reuse original DOM forms, field names, IDs, values and POST/CSRF routes; never clone forms or fetch/serialize private records into storage. Native summary activation selects and focuses context; Back to list restores focus. Reveal affected rows/disclosures before error-link field focus. No-JS fallback retains full server-rendered forms; narrow screens begin with the list. Existing pagination ownership and appearance are unchanged. No application policy or query ownership transfers to UI code.

## Covers and student details — 2026-11-12

Circulation thumbnail owner: views/circulation_ui.php using /cover.php with existing identity/archive rules. No cover storage keys are output. workspace.js handles failed images with a visible fallback; selected-panel images load eagerly. Account detail owner: views/student_account_ui.php, fed by authorized bounded student-list reads and existing audit timestamps; librarian IDs only, no guessed dates or write behavior. Catalog column-center and pagination-container spacing belong to workspace.css; pagination controls keep their existing owner.
