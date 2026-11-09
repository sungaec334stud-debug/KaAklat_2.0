# KaAklat interface system

## Shared navigation and appearance — 2026-11-10

User approved the separate theme mockup, then requested its navigation and dark appearance on the actual application. views/layout.php remains the only role-navigation owner: unchanged workflow destinations, Settings before About/Log out, a utility divider, compact 232px navy sidebar at 1100px+, and a visible single scrollable row below that width. Native links and visible scrollbars stay keyboard reachable; no hamburger, modal, role selector or fictional preview handlers. KaAklat remains primary; university identity remains secondary.

public/assets/app.css owns semantic light/dark tokens, including form, error, status, scrollbar and focus colors. Light retains the school palette. Dark uses canvas #101927, surface #182538, soft #1E2E44, text #E6EDF7, muted #B0BFD4, line #364962, links/focus #96C0FF, solid primary #2865CD and navy sidebar #0B1422. --navy is heading ink; --strong-surface is the dark artwork/selected background, so dark headings never turn navy surfaces white. Buttons use --button independently of --blue link/accent colors. Fixed white text on dark artwork and theme thumbnails are deliberate variants, not unthemed reading surfaces. landing.css consumes the same palette.

public/assets/theme.js loads before CSS, resolves only system/light/dark and sets the effective data-theme. localStorage key kaaklat.appearance stores no account information. Default follows prefers-color-scheme; device changes update System automatically. Selection is browser-wide, persists across logins and signed-out pages, and does not sync to a user account. Blocked storage leaves current-page selection usable with an honest unsaved message. Without JavaScript, the CSS media fallback uses the device theme; Settings explains disabled preference controls. Settings uses native fieldset/radios (keyboard arrows/Space) and a polite status region. No new server mutation, cookie, dependency or schema field.

Actual evidence and remaining zoom/assistive-technology limits: verification/THEME_NAVIGATION_2026-11-10.md. Earlier wrapping-topnav/light-only descriptions below are historical, superseded only for these shared capabilities.

## Own borrowing state on catalog cards — 2026-11-09

app/catalog.php owns a single page-bounded request-state query for at most12 visible book IDs; current_user provides fresh identity, and no owner ID is taken from request input. Pending and live approved allocations/loans derive their states from existing timestamps/decision/return/cancel fields. views/catalog_status.php allowlists four readable personal labels and reuses canonical status-badge colors with text, not color alone. public/catalog.php places the small label immediately below the title. No tooltip, popup, JS, new action or role permission. No badge for absent/closed records or staff users. Catalog count/filter/paging behavior retained. Pending/awaiting/active/overdue matching request-history rules; due today stays active, exact48-hour holds are expired. Normal page refresh observes state changes; no live polling.

## Catalog page navigation — 2026-11-09

views/catalog_pagination.php owns numbered read-only navigation for public/catalog.php. Reuses app.css palette, focus and 44px controls with wrapping. Count and total pages use the same SQL filter selection as results, including derived availability. Twelve books per page; out-of-range valid pages clamp to final page and no matches use page1of1. At most first/last and current±2 numbered pages for large catalogs, with noninteractive ellipses; all seven or fewer pages shown. Native links keep every filter/search and work without JavaScript. aria-current identifies current page, names distinguish number/previous/next links. Existing catalog access and sorted query semantics retained. No schema, role or borrowing changes. Counts can change between normal reads as catalog records change; this is navigation information, not allocation authority.

## Public About page — 2026-11-09

`public/about.php` and `views/about.php` provide read-only guest/role information using the existing layout, identity assets and canonical app.css tokens. The cobalt introduction rule anchors a readable single column; the school summary uses the existing panel; an ordered six-step guide encodes actual borrowing order. Shared layout owns navigation/current state and portal_ui owns its decorative icon. No new fonts, palette, dependencies or JS. No developer placeholders are rendered. School source: https://udd.edu.ph/, verified 2026-11-09; mission/vision labeled summary. Forms/loading/CRUD contracts do not apply. Authentication and policy remain owned by existing services.

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

Developer panel: user-provided portrait and credits sit side by side above 600px and stack below; email wraps within the existing About page. Existing surface/radius/type tokens retained.

Shared logout (2026-11-09): layout owns one signed-in navigation link for every role; logout.php owns a server-rendered confirmation using auth-panel and native POST/CSRF with confirm=yes. Stay signed in returns to dashboard; no JS or modal/focus-trap dependency. Existing tokens and responsive navigation retained.

Pagination correction: center the number group beneath a full-width centered summary. Override paragraph reading-width cap for .pagination-summary and declare nav.catalog-pagination after generic paging rules. Verified midpoint at1920/1440/360px.
