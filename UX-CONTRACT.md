# Shared interface owners

Updated 2026-11-04. English school demonstration, Asia/Manila. This is an owner map, not another source of borrowing policy. SRS.md and SCHEMA.md remain authoritative for eligibility, permissions, lifecycle, limits and lock order. Hosting stays deferred.

| Capability | Canonical owner | Presentation contract |
| --- | --- | --- |
| Current identity, roles and sessions | app/auth.php and existing account authorization helpers | layout.php reuses freshly verified identity; navigation never grants permission. |
| Navigation, route indication and skip target | views/layout.php | Same role routes, visible names, unique current route, no hidden mobile menu. |
| Decorative route icons and linked summaries | views/portal_ui.php | Static allowlisted SVG; hidden from accessibility tree, nonfocusable. Escape text/URLs; consume existing counts, do not query or mutate. |
| Counts | existing dashboard_counts calculation | Profile and dashboard share the calculation and summary presentation; own-student scope stays unchanged. |
| Colors, typography, spacing, focus and responsive shell | public/assets/app.css | DESIGN.md mirrors tokens. Natural scrolling, wrapping tabs, 44px actions, 16px inputs, checkbox errors in normal flow. |
| Landing imagery and bounded motion | views/landing.php, public/assets/landing.css | Existing illustrative images/copy; reduced-motion rules; no protected covers or new promises. |
| Forms, validation and feedback | existing page/service validators, public/assets/forms.js | Server remains authoritative; preserve field names/IDs, POST/CSRF, entered values, summary/field links and redirects. No new autosubmit or textarea behavior. |
| Search, pagination and selected tabs | existing page GET allowlists | Retain URLs, filters, bounded page sizes and current selections. |
| Requests, decisions, pickup, return and cancellation | existing app circulation services | No UI-owned counters, eligibility, allocation or transaction logic. |
| Private account/history errors | existing safe error/escaping helpers and protected pages | No new private data exports, logs or profile-selection parameters. |

No new async requests, dialogs, loading states, dependencies or localization system were introduced. Existing vertical textarea resizing is retained. Actual verification and remaining manual coverage are recorded in verification/CAMPUS_PORTAL_ACCEPTANCE.md.
