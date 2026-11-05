# KaAklat interface system

The login and student registration pages use the shared `auth-entry` variant in `app.css`. The form comes first in the reading order. A navy side panel explains librarian approval or account recovery and moves below the form on narrow screens. Registration places short, accessible hints beside the fields they describe.

2026-11-04. User-approved campus-portal overhaul of the existing school blue/white/gray Operate interface. Shared application behavior remains authoritative. No new business features or invented school claims.

Use Bahnschrift with Segoe UI fallback for crisp geometric headings; Segoe UI Variable/Segoe UI/system sans for task-focused body UI. Canonical runtime tokens live in app.css: navy #0B2350, blue #1759D4, canvas #EEF2F7, slate #52637A, border #D4DEEB and white. Panels use8px corners; controls5–6px. Fixed rem hierarchy, navy headings, slate secondary text and blue primary actions. Light content surfaces are bordered, not shadowed. Desktop authenticated navigation is a navy 232px sidebar at 1100px+, with current-route blue treatment; below that it returns to wrapping top navigation with no hidden menu or new JavaScript. Guest header remains light. Existing SVG logo retained. Headings carry hierarchy without redundant eyebrow labels. Forms keep generous labels and 44px minimum actions, with 16px inputs, text error summaries, visible focus and restrained caution/status colors. Tabs wrap; code/secret blocks wrap; content stays visible with JS off. No external assets/dependencies.

Coverage through shared CSS/layout: welcome, login/register/reset, dashboards by role, catalog/details, student request forms/history, librarian books/reviews/pickup/returns/students, administrator staff/audit and recovery issuance. Existing POST fields, routes, authorization, transaction services, data and copy (except redundant decorative labels) preserved. New UI acceptance evidence must be recorded separately from prior visual passes.

## View-only student profile — 2026-11-04

Product register, English/Asia-Manila. Existing SRS/SCHEMA/WORKFLOW/TEST_PLAN own role/lifecycle/feedback behavior; no separate competing UX rules introduced. app.css remains canonical for all color/type/focus/scrollbar tokens; layout.php owns role navigation and route indication; current_user owns fresh identity/status/session checks; dashboard_counts owns profile/dashboard totals. views/student_profile.php adds a semantic definition list using existing typography/panels and reuses summary-grid/count-card for linked borrowing counts. Its profile-details rules only add wrapping and stacked account rows below600px. No decorative avatar, extra metrics, motion, editing controls or external resources. Pending/active, zero, error and access-denied states use existing language/state patterns. Read-only page: form/select/CRUD mutation contracts not applicable. New profile visual/keyboard/zoom acceptance must be recorded independently of earlier UI checks.

## Signed-out landing — 2026-11-03
The signed-out welcome surface uses Persuade mode within the same palette. views/landing.php owns semantic markup; landing.css is loaded only when render_page explicitly receives the landing flag. Authenticated index exits through the original dashboard branch and does not receive landing markup/styles. Two generated library still-life images are local optimized JPEGs, not school photographs or protected catalog covers. Hero displays explanatory alt text and illustrative-setting caption. No new claims about the school.
Motion thesis: one short opening of the reading scene (clip/opacity,700ms), bounded caption settling and a small directional-link hover response. No loops, autoplay, scroll-dependent hidden content or new JS. All motion lives under prefers-reduced-motion:no-preference; reduce disables animations/transitions. Four numbered steps represent actual borrowing order, not decoration. Primary actions remain existing login/register URLs; in-page guide is a native anchor.

## Campus portal — 2026-11-04

Approved design brief: docs/superpowers/specs/2026-11-04-campus-portal-design.md. Campus-directory signature: cobalt margin rule, navy navigation spine, decorative line icons, square route marker, white contextual topbar, compact shared summary strip and task cards. Sidebar232px at1100px+, visible wrapping topnav below it; no hidden hamburger or new JavaScript. Keep natural scrolling and identity wrapping; avoid distributing sidebar children with space-between. Shared summary/icon owner is views/portal_ui.php; existing dashboard_counts remains the data owner. UX-CONTRACT.md maps reusable owners rather than redefining policy.

Checkbox labels keep normal block flow so dynamically inserted field errors cannot become tiny flex columns. Existing summary focus/field links retained. Landing continues its scoped bounded motion and reduced-motion guard. Existing vertical textarea resize retained to avoid changing established input behavior. Current visual/keyboard evidence and remaining400%/JS-disabled/full-state limits: verification/CAMPUS_PORTAL_ACCEPTANCE.md. Earlier profile passes and mockup screenshots are historical evidence only for their own scope.

## Dashboard task directory

The three dashboards use the campus portal's existing blue margin rule, heading fonts, route icons, and focus styles. Student services sit below the borrowing summary. Librarian tasks are grouped by circulation and catalog operations. Administrator account and audit links share one directory. Each row pairs a task description with a visible link. At narrow widths the link sits below the description.

## Dashboard overview

The main navigation serves as the directory for each role. The dashboard uses a compact heading, places student notices above borrowing totals, and gives administrators a single Staff accounts shortcut.

## Personalized dashboard banners

Each role has a navy welcome banner with its display name, initials, and a decorative illustration. Below it, one panel previews relevant records and links to the existing workflow. The student panel uses Manila deadlines. The librarian panel shows pending requests and live pickups. The administrator panel shows a short list of account audit events.

## Compact student request history

Each request starts as a compact row with its cover, title, status, and relevant Manila date. A native disclosure reveals the progress tracker, original terms, explanations, and any available cancellation action. Status filters wrap on narrow screens. Active and Overdue are separate views, and All requests retains the full history.

The shared header gives KaAklat and Universidad de Dagupan separate brand groups. In the desktop sidebar, the university seal and name occupy their own row below a divider. In the horizontal header, the groups can wrap as space narrows.
