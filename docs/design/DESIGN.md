## Staff dashboard parity correction — 2026-11-13

## Approved header appearance toggle - 2026-11-21

The sun/moon header control replaces the Settings page and menu. views/portal_ui.php owns shared decorative SVG/button markup; views/layout.php places it in desktop portal, mobile top row and guest header. app.css owns a 44px target, existing theme/focus tokens, 360ms icon rotation/scale with 200ms fade and 240ms surface transitions. Reduced motion removes those effects; no looping, page wipe or layout animation. Current page/status and the accepted drawer/pagination remain. theme.js alone owns browser appearance, legacy system preference compatibility, cross-tab sync and honest blocked-storage feedback. No account/database preferences. Old Settings URLs keep authorization/method guards and redirect to the dashboard. The former radio-card view/styles are retired.

The earlier redesign completion statement overstated staff-dashboard parity. Corrected the librarian and administrator dashboards to the accepted layout: whole-card summaries, two-column work/account panels, protected book covers, librarian student verification and own recent desk activity, administrator recent account activity and active staff access. All values come from bounded read-only queries. Librarians see only their own allowlisted circulation activity; administrators gain no circulation permissions. Existing student dashboard, role checks, About content, pagination and pilot records remain unchanged. No schema changes, commit or push; hosting deferred.

Verification: 36 regression scripts passed before the final administrator presentation adjustment; final affected dashboard/design tests, PHP lint and 416 workflow HTTP assertions passed. Final browser rerun covered 248 role/theme/viewport combinations with zero runtime exceptions and zero sampled contrast failures. Final librarian/administrator dark screenshots were visually inspected. Strict static UI audit reported zero findings. Actual 400% browser zoom, physical-device and screen-reader acceptance remain manual; no claim of a bug-free system. Usage tool unavailable.

Evidence: verification/STAFF_DASHBOARD_PARITY_2026-11-13.md.

# KaAklat interface system

### Catalog card alignment — 2026-11-13

Catalog row alignment corrected with seven shared natural-height CSS subgrid tracks and one optional-badge container. Covers, titles, badge blocks, authors, publication metadata, descriptions and available counts align within each visual row without fixed text heights or additional clipping. Server queries, status/availability rules, cover permissions, links, sorting, filters and pagination unchanged. Focused browser reproduction initially measured150.77px drift; final10 Light/Dark viewport cases(1440/1000/768/360/320px) measured0px, with no overflow/clipped text and a JavaScript-disabled CSS check passing. Existing96 authenticated catalog browser combinations and focused status/view tests passed; PHP lint passed. No pilot records changed, no commit/push. Earlier redesign work remains preserved.

## Accepted design-review refinement — 2026-11-13 (current)

Apply the approved three-role working preview through existing canonical owners. Retain blue/white/gray, Bahnschrift/Segoe UI, primary KaAklat and secondary university identity. Native numbered pagination is explicitly unchanged. views/layout.php groups existing permitted links into Library/Circulation/Accounts & security/Preferences; views/portal_ui.php uses the accepted nonfocusable decorative 26-unit line SVGs. No prototype role selector or mock data reaches the application.

app.css owns the compact header, smaller personalized welcome/avatar, linked summary strip, date tile and bounded protected-cover discovery. app/dashboard.php retains student/current-account scope and adds only active-administrator scalar account totals. public/index.php supplies existing catalog data; no stored counters or policy moves to UI. Student pending/browsing and separate active/overdue count semantics remain authoritative, even where prototype examples were simplified.

workspace.css refines existing directory/inspector, audit, requests, profile, recovery, About and logout surfaces. public/manage_books.php wraps intact server forms in native sections, opening the affected action on error or copy-page navigation; no-JS, field IDs, values, CSRF and multipart behavior remain. views/audit_ui.php supplements actual raw codes with allowlisted readable labels. Unknown codes remain Recorded activity, never an invented decision.

The complete maintained About source is preserved exactly: school philosophy/location/values/mission-summary/source, all six borrowing steps, demonstration disclaimer and developer photo/qualification/contact. The prototype's shorter About is not application content. Field, route, history, error and authorization information is never removed merely to fit the reference screenshot. No new textarea, preference persistence or circulation behavior is introduced. Narrow navigation was subsequently superseded by the approved2026-11-15 drawer below.

Evidence and pending real-device/zoom/screen-reader/performance review: verification/DESIGN_REVIEW_IMPLEMENTATION_2026-11-13.md. Previous visual descriptions below are historical where this refinement supersedes them.

## Approved workspace directory/detail design — 2026-11-12

The user approved the separate twelve-page working preview. `views/layout.php` loads `workspace.css` and deferred `workspace.js` only for the twelve named workspace routes, before existing form enhancement. Existing `app.css` owns colors, appearance, navigation and pagination. Compact field-labelled toolbars, readable directory rows and a blue-edged action inspector replace long expanded management forms when JavaScript is available. Select a native summary with pointer/Enter/Space to focus the selected record heading; Back to list restores row focus. Existing nodes move rather than clone, preserving fields, values and server actions. Error fields open the correct record before forms.js focuses the linked summary. At narrow widths the initial list is shown; selected details stack above it. Without JavaScript, existing full server forms remain reachable; staff creation uses native details and opens on a failed creation POST. No record selection or private context is persisted.

Profile uses existing own-only fields/counts and an escaped display initial, not an uploaded avatar. Managed books reuse authorized covers. Audit has labelled compact rows; recovery has a private-handover guide without changing secret issuance; About retains all existing school/developer/borrowing content with a CSS shelf decoration. Pagination renderer, classes and styles are expressly excluded from the redesign. Both skills’ visual/behavior contracts apply; zero-bug guarantees are not made. Evidence and remaining manual checks: verification/WORKSPACE_REFRESH_2026-11-12.md.

## Shared navigation and appearance — 2026-11-10

User approved the separate theme mockup, then requested its navigation and dark appearance on the actual application. views/layout.php remains the only role-navigation owner: unchanged workflow destinations, Settings before About/Log out, a utility divider, compact 232px navy sidebar at 1100px+, and a progressive modal left drawer below that width (approved2026-11-15). Native links remain server-rendered and keyboard reachable; an icon-only trigger retains its accessible name. Without scripts/dialog support, links remain visible and wrap. No role selector or fictional preview handlers. KaAklat remains primary; university identity remains secondary.

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

Approved design brief: docs/design/specs/2026-11-04-campus-portal-design.md. Campus-directory signature: cobalt margin rule, navy navigation spine, decorative line icons, square route marker, white contextual topbar, compact shared summary strip and task cards. Sidebar232px at1100px+; narrow layouts now use the approved2026-11-15 progressive drawer with a visible wrapped no-script fallback. Keep natural scrolling and identity wrapping; avoid distributing sidebar children with space-between. Shared summary/icon owner is views/portal_ui.php; existing dashboard_counts remains the data owner. docs/design/UX-CONTRACT.md maps reusable owners rather than redefining policy.

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


## Catalog discovery controls — 2026-11-12

app/catalog.php owns validated search, inclusive years, availability, fixed sort mappings and local results-context URLs. views/catalog_filters.php owns one native GET form: title-or-author search, category, availability and sort in the primary row; More filters is a native details disclosure for author/title contains, title initial and a paired From/To year fieldset. Reuse app.css tokens, 44px actions and forms.js linked error-summary focus. Keep Apply filters explicit and JS-disabled core behavior. Active choices are wrapping native removal links; Clear all restores defaults. Search clearing uses a local link/fragment returning focus to the search input. Details and pagination consume the shared URL helper. Result range is textual; zero availability uses a neutral text badge and a truthful general explanation. No async search, dependencies, new palettes or new borrowing rules. Actual browser/verification evidence: verification/CATALOG_DISCOVERY_2026-11-12.md.


## Shared numbered pagination — 2026-11-12

views/pagination.php is the sole navigation renderer; views/catalog_pagination.php adapts validated catalog URLs. All existing paginated lists reuse centered catalog-pagination/pagination-summary/current/gap tokens from app.css. Native GET links,44px targets, wrapping at narrow widths, current-page text and bounded first/last/nearby numbers; no new CSS/JS or dependencies. app/pagination.php counts the identical authorized/filtered selection and normalizes displayed page bounds; legacy row-only service callers retain their lookahead/empty out-of-range behavior. Keep20 rows for requests/reviews/pickups/returns/staff,25 students/audit,12 books and50 copies. Copy links preserve book ID and use copies_page; other lists retain their own search/status/audit fields. Dashboard previews and static forms do not get artificial pagination. Actual evidence and limits: verification/SHARED_PAGINATION_2026-11-12.md.


## Presentation-owner simplification — 2026-11-12

No visual redesign or token changes. views/portal_ui.php now owns student_summary_items and student_overdue_notice_html, reusing existing counts/URLs and preserving dashboard Ready to collect versus profile Ready for pickup. Page-specific empty states/order remain at callers. views/time.php owns display_manila_time with each screen's original format; date-only due dates, registration time with machine-readable datetime,48-hour pickup calculation and all service deadline math remain unchanged. Existing request/dashboard time wrappers retain caller contracts. Browser suites share test-only tests/browser_protocol.cjs, while suite-specific fixtures/ports/waits/cleanup remain separate. Evidence: verification/SIMPLIFICATION_2026-11-12.md.

## Manage books parity correction — 2026-11-12

views/managed_catalog.php owns read-only record directory/context links. public/manage_books.php renders that directory beside the original server editor, with no client fetching or form duplication. Columns show title/author, category, available/active-copy count and Manage. Selected-title tint and a blue-edged editor match the approved preview. Category remains the existing free-text/datalist field; total copies remain derived with existing Add copies actions, never a misleading editable total. Native GET links focus #book-editor and retain query/page; successful POST redirects retain the same context. Copies and catalog pagination remain independently labelled and styled by the unchanged owner. At900px and below, panels stack. This supersedes the incomplete earlier card-list/separate-editor implementation.

## Circulation covers and student metadata — 2026-11-12

views/circulation_ui.php owns decorative authorized thumbnail markup; list SELECTs add existing cover/archive metadata without changing eligibility. workspace.js builds the summary thumbnail using DOM nodes, retains the original header cover in the selected record, eagerly loads selected images and hides failed images in favor of No cover. No public static storage URLs or changed cover permissions. Managed categories/availability are centered against matching column tracks; title/author remains left-aligned. No-cover thumbnails explicitly reset the global minimum height. managed-pages-wrap provides container padding only; pagination classes/renderer/app.css remain unchanged.

views/student_account_ui.php owns read-only detail facts. Librarian-only school IDs now appear on active/suspended cards as requested; administrators retain dates without IDs. Existing indexed audit events supply earliest recorded student.activate, latest student.suspend and student.reactivate; created_at supplies registration. No updated_at inference, fabricated date, added migration or account editing. Unknown legacy dates say Not recorded. The current page is still bounded to25 students; new history reads do not grant account-change permission.


## Approved public entry design — 2026-11-14

Implemented the accepted landing/login/registration mockup, with a matching recovery form. Photo-led entry panels, concise field guidance, native expandable recovery help, password visibility buttons, light/dark layouts and finite reduced-motion-aware animation reuse existing images and palette. About remains in guest navigation and its complete content is unchanged. How borrowing works remains a real landing anchor. Prototype controls, simulated submissions and fictional preview messages were not copied into the application.

Verification: 36 regression scripts; 427 isolated workflow HTTP assertions; 132 PHP files linted; JavaScript syntax and strict static UI audit passed (zero findings). Chromium covered 64 entry route/theme/viewport combinations (320/360/390/768/820/1024/1280/1440px), images, wrapping, keyboard password/recovery controls, error summary/field focus, reduced motion, 320 CSS-pixel reflow, JS-disabled invalid registration, and active/pending student/librarian/admin sign-in. Zero browser runtime exceptions. Guest shell styles are scoped separately from authenticated navigation, including authenticated visits to entry routes. Pilot rows/schema/covers/configuration match the fresh read-only baseline; no test accounts added to pilot. Existing backup/performance/research work preserved. Actual 400% zoom, physical-device/Safari and screen-reader checks for this new presentation remain manual. Hosting deferred; no commit/push.

Evidence and replay commands: verification/ENTRY_DESIGN_2026-11-14.md.

Canonical entry owners: views/landing.php (public introduction), views/auth_entry.php (decorative photo scene), public/assets/entry.css (entry-only presentation), public/assets/entry.js (password visibility only), and views/layout.php (route asset selection). app.css/theme.js remain the single palette/appearance owners. Existing public controllers and forms.js remain authoritative for forms, validation and summaries. Existing landing.css is retained as a legacy asset but no longer loaded by the landing route.


### Approved responsive navigation drawer — 2026-11-15

Implemented the accepted phone/tablet mockup using the existing server-rendered navigation. Below1100px, an icon-only44px Open navigation button opens a native left modal drawer. The compact sticky header keeps the actual page title and role/status visible. Current links remain highlighted; the same permitted links/icons/identity move into the drawer without cloning, fetching or new permissions. Desktop232px sidebar and pagination are unchanged. Escape, Close/backdrop dismissal, focus wrapping/return, short-height scrolling, resize/bfcache reset, safe-area spacing and reduced motion are supported. JavaScript/dialog-unavailable fallback leaves real links visible and wrapped. Guest navigation is unchanged.

Verified:36 regression scripts;448 isolated workflow HTTP assertions;133 PHP lints; JS syntax; strict static audit zero findings;56 role/theme/viewport drawer layouts and30 real route navigations;64 existing entry-page browser combinations. Browser suites reported zero runtime exceptions. Active/pending student, librarian and administrator checked at320/360/768/1024/1099/1100/1440px; keyboard dismissal/wrapping, unsent-input retention, desktop transitions, short-height final-link access, reduced motion and JS-disabled fallback passed. Pilot rows/schema/covers/config match fresh before/after fingerprints. No schema/services/transactions/audit changes or pilot test accounts. Physical-device/Safari/screen-reader and actual400% browser-zoom acceptance for this new drawer remain manual. No commit/push; hosting deferred.

Evidence: verification/NAVIGATION_DRAWER_2026-11-15.md. Next: user phone/tablet/keyboard review, then continue local finalization/evaluation rather than optional features.

## Password visibility and recovery copy - 2026-11-21

views/auth_entry.php owns password_visibility_button_html(); entry.js controls only the associated field type and state-specific accessible label/title/aria-pressed. No field values are stored, logged, cloned or submitted by the toggle. entry.css owns a 44px icon target with short eyelid/iris opacity/transform transitions under no-preference motion only. Hidden password fields show a closed eye; revealed fields an open eye. All three recovery fields remain independent; no-JS controls stay hidden and forms remain usable. Login/registration/reset controllers retain their POST/CSRF/validation/service logic. Reset labels and small hints explain staff-issued code, exact private paste, 30-minute single use and unchanged access status; extra help is native collapsed details. The 12-72 byte server rule is unchanged; the guidance recommends basic characters so users need not understand byte encodings. Existing header theme/navigation/pagination owners are unchanged.


## Shared interaction motion - 2026-11-21

Approved register: subtle/professional task feedback. public/assets/motion.css owns --motion-press120ms, --motion-feedback160ms, --motion-selection180ms and --motion-ease cubic-bezier(.16,1,.3,1); it loads after the existing component styles through views/layout.php. Existing entry arrivals use240ms/no delay and the hero photo420ms. Focus outlines, form actions and native navigation are never delayed. Pagination is excluded. No looping, scroll reveal, height animation, broad transition:all, third-party dependency or new state persistence.

workspace.js remains the record-selection owner. Only a deliberate changed selection with focus gets a bounded180ms opacity/4px horizontal handoff. Previous motion is cancelled before every selection, on reduced-motion changes and pagehide. Missing animate() simply skips the effect; no animation is required for original forms or no-script fallback. Initial selection and error-link reveal do not animate. The stylesheet's nonessential effects require prefers-reduced-motion:no-preference; hover effects additionally require a fine hover pointer. Existing theme, eye and drawer animations retain their owners. No business, validation, POST, CSRF or focus semantics change.

Evidence: verification/MOTION_REFINEMENT_2026-11-21.md and verification/motion-2026-11-21/. A source-hash inventory records the exact tested runtime and test files. Physical-device/assistive-technology and hardware-performance limits are recorded in the report.


## About developer card - 2026-11-21

Approved identity hierarchy is owned by views/about.php and scoped workspace.css rules: section heading,96px portrait beside name/role/course, concise project contribution, labelled native Email/GitHub actions, and visible email address. At540px and below the identity grid stacks; wrapped actions remain at least44px tall. Use existing semantic palette/focus/motion; keep all other About information unchanged. The repository destination is the configured maintained public project URL. Do not invent qualifications, responsibilities, adviser credits or academic dates. No extra technical-stack section or new fields are introduced. No JS or backend change is required. Evidence: verification/DEVELOPER_CARD_2026-11-21.md.


## Date label refinement - 2026-11-22

User requested removal of the visible (Manila) suffix. Date labels and registration/activation/suspension headings omit repeated parenthetical timezone text. The audit introduction states once that times use Philippine time. views/time.php and existing date conversions remain authoritative and unchanged; internal Manila naming and ISO offsets are retained. Preserve the full-day due-date explanation. No preference, timezone selector, stored-date or workflow change. Evidence: verification/TIME_LABELS_2026-11-22.md.


## Guest header alignment - 2026-11-22

At761px and wider, body.is-guest .site-header .header-nav uses margin-inline-start:auto so native guest links sit immediately before the theme button on the right. app.css owns this rule; views/layout.php refreshes the CSS cache URL. The existing760px-and-below grid keeps brand/theme on row one and wrapped guest links on row two. Do not apply this spacing to authenticated navigation or reorder keyboard focus. Evidence: verification/GUEST_HEADER_ALIGNMENT_2026-11-22.md.


## Entry help disclosure resize - 2026-11-22

User clip identified abrupt growth/shrink of the login auth-shell when the native help disclosure toggled. entry.js owns optional measured260ms height animation only for .auth-main details.help bodies; entry.css owns a flow-root .help-content wrapper. Original nodes move without cloning. The summary remains native and outside clipping; controls/fields are never scaled. Opening starts at zero when native details is closed (closed children can retain layout boxes). Closing keeps content open through the animation and then settles native open=false, restoring summary focus if a hidden descendant held focus. Rapid actions cancel/reverse from current height; reduced-motion, resize and pagehide clean up. No max-height cap or final fixed height. No-JS/unsupported WAAPI uses native disclosure unchanged.

This deliberately scoped help-body resize is an exception to the earlier general avoidance of layout-height animation: it solves the user-reported layout discontinuity without animating page dimensions or unrelated controls. No form or authentication logic changes. Cache versions for entry.js/entry.css are refreshed together. Evidence: verification/HELP_DISCLOSURE_MOTION_2026-11-22.md.


## Public navigation continuity - 2026-11-22

views/layout.php owns guest-only page-navigation.css/js assets and one decorative .nav-current-marker on the existing current guest anchor. Server permissions, routes, native focus and forms are unchanged; authenticated pages do not load these assets. Browser native cross-document view transitions provide viewport crossfade180ms and marker slide220ms, enabled only for no-preference motion. Page content is not dimension-morphed. The head script observes allowlisted public native links without preventing default, fetching, assigning location, adding delays or storing data. Only a real pending navigation can show a150ms-delayed indeterminate2px line; pageshow/Escape/new interaction/submit/preference changes clear pending state. Form and unrelated transitions are skipped.

An incoming document retains page-transition-arrived to suppress duplicate CSS entrances after the native crossfade; page-transition-active and active-transition references still clean up. Reduced-motion changes skip active incoming transitions. Unsupported API/CSS keeps native navigation; native no-script links/forms remain usable. No private inputs, URLs or route state are persisted. Technical reference: https://developer.chrome.com/docs/web-platform/view-transitions/cross-document . Evidence: verification/PUBLIC_PAGE_NAVIGATION_2026-11-22.md.


## Consistent public headers - 2026-11-22

Unified Welcome, Login, Registration, Password recovery and guest About headers in public/assets/app.css. Removed conflicting entry.css header overrides. Desktop branding, university label, navigation and theme control now share dimensions; phones use left branding/right theme above centered links. The university header label is consistently hidden at 1050px and below; page content remains intact. Authenticated navigation, theme logic, native page transitions and database code are unchanged.

Verification: 160 header layouts across five routes, sixteen widths (320–1920px) and both themes; 25 theme layouts/50 toggles; 18 actual document transitions; 64 entry combinations and four fixture role sign-ins. PHP lint, JavaScript syntax and strict design audit pass. Browser checks use isolated fictional fixtures, not pilot accounts. Physical devices, Safari, screen readers and actual 400% zoom were not rerun. Evidence: verification/GUEST_HEADER_PARITY_2026-11-22.md. No commit or push.


## System-wide navigation and expanding-section motion - 2026-11-23

Extended the accepted native document crossfade, sliding current-navigation marker and genuine pending feedback from guest pages to all existing signed-in HTML views. Desktop sidebar/mobile top bar retain continuity. Native links, query/filter/status tabs, browser history and form submissions remain authoritative; no router, artificial delay or persisted inputs. Existing button, selection, theme and password-eye feedback is preserved.

Extracted the Forgot password 260ms measured height behavior from entry.js into shared disclosure.js, loaded after workspace transformations. Native expandable sections now rotate their arrows and animate opening/closing: help, catalog filters, request history, book-editor sections, staff creation and nested student-ID claim review. Workspace record selectors retain their separate inspector handler. Rapid reversal, keyboard operation, focus return, resize, pagehide, external opens/invalid fields and reduced motion retain natural final layout. No-JS/missing Web Animations support uses native details. Existing editor spacing and pagination design are preserved.

Verification: 150 signed-in transitions across four account roles/statuses, Light/Dark and375/768/1440px;21 primary plus2 nested measured disclosures;2 same-page status tabs;12 prior help cases;18 guest transitions;56 drawer layouts/26 routes;160 header comparisons;25 theme layouts/50 toggles;120 existing feedback layouts/6 selections;30 navigation guard checks;36 regression scripts;469 workflow HTTP assertions. Zero browser runtime errors; PHP/JS syntax and strict design audit pass. Static detector style advisories concern existing approved accent borders and their matching marker, retained intentionally. Independent code review found no actionable correctness issue. Physical devices, Safari, screen readers and actual400% zoom were not rerun. Native cross-document effects depend on browser support; fallback remains usable native navigation. No backend/schema/pilot-data changes, hosting, commit or push. See verification/SYSTEM_MOTION_2026-11-23.md.


## Librarian archived-book review and restoration - 2026-11-23

Implemented Active books / Archived books tabs inside Manage books. Active librarians can search/page archived titles, inspect protected covers, title/authors, archive date and active/individually archived copy counts, and explicitly confirm Unarchive book. The native POST uses CSRF, fresh role/session checks and actor→book locking. It clears only the title archive timestamp and writes catalog.unarchive_book audit atomically; individually archived copies and all borrowing/author/copy history remain intact. Availability stays derived; titles without active copies remain unavailable. Timestamp plus latest archive audit ID rejects stale confirmations, including a later archive in the same second. Repeated restoration returns a clear error without another write/audit. Administrators retain audit access, not archived-title/cover/restoration access; students/pending students are denied.

No schema migration. Existing managed-workspace, status-tab, pagination, theme, animation, time-display and form primitives are reused. A confirmed no-JavaScript View Transition promise exception found during native restoration verification is fixed by a noscript-only stylesheet disabling that optional effect; scripted animation remains unchanged.

Verified in isolated fictional databases:37 regression scripts,32 archive integration assertions,508 workflow HTTP assertions,36 archive browser layouts at375/768/1440px in Light/Dark,30 navigation guard checks and18 existing guest document transitions. Zero browser runtime errors; changed PHP/JS syntax and strict design audit pass. Desktop/mobile screenshots inspected; independent security/correctness review found no actionable issue. Physical-device, Safari, screen-reader and actual400% zoom checks remain manual. No pilot-record/private-config/cover changes, hosting, commit or push. Evidence: verification/ARCHIVED_BOOKS_2026-11-23.md.


## Student identity review layout - 2026-11-23

Reorganized pending-student identity review into separate labelled Activate account, Correct the school ID and Release a mistaken claim sections. Each native form retains its own required verification checkbox, CSRF token, student ID and stale review version. Confirmation text now names its action; correction includes a uniquely labelled required ID field and linked dashed-format example. Scoped theme-aware CSS aligns checkbox text, fields, hints and buttons with consistent gaps; the existing claim disclosure animation remains. No permission, identity-policy, database or pagination change. Pending archived-book implementation is preserved.

Verified:11 structural/form assertions,6 browser layouts at375/768/1440px in Light/Dark with measured expansion, keyboard checkbox and reduced motion;38 regression scripts;508 fictional workflow HTTP assertions; existing student policy and temporary-table integration tests; changed PHP/JS syntax and strict design audit (zero findings). Zero browser runtime errors; desktop/phone screenshots inspected. Physical-device/Safari/screen-reader/actual400% zoom acceptance was not rerun. No pilot-data mutation, commit or push.


## Circulation sorting and status filters - 2026-11-24

Added shared native GET controls to Review requests, Pickups and Returns. Reviews default to oldest first and offer newest, shortest and longest requested borrowing period. Pickups default to nearest collection deadline and filter All outstanding / Ready for pickup / Expired, with farthest-deadline ordering available. Returns default to earliest due date and filter All outstanding / Overdue / Due today / Active-not-overdue, with latest due date available. Active includes due-today loans. Status uses existing UTC48-hour pickup and Philippine-calendar overdue rules; cancelled/collected/returned history remains excluded. No lifecycle, permission, CSRF, requested-duration cap or availability rule changes.

One server allowlist owns filters/sort keys; SQL values remain parameterized, ordering uses fixed internal expressions and deterministic request-ID ties. Filters apply before counts/pagination. Apply starts page1, Clear restores defaults, and pagination/validation/success redirects retain search/status/sort. Existing pagination design and native forms remain. Queue summaries now show status/deadline with readable separators; full details/actions remain intact. Responsive controls use existing theme tokens and44px native targets.

Verified on isolated fictional fixtures:39 regression scripts including34 new queue service checks;558 workflow HTTP assertions;66 browser layouts (11 status/order views ×375/768/1440px ×Light/Dark), selected options, keyboard focus, labelled44px targets, aligned layouts, no overflow, search/sort pagination retention, page1 reset and no-JS GET filtering. Zero runtime exceptions. PHP/JS syntax and strict design audit pass; phone/desktop screenshots inspected and independent read-only review found no actionable issue. Physical-device/Safari/screen-reader/actual400% zoom acceptance remains manual. No schema migration, pilot mutations, hosting, commit or push. Evidence: verification/CIRCULATION_FILTERS_2026-11-24.md.
