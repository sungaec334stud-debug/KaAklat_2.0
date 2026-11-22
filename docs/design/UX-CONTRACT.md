# Shared interface owners

Updated 2026-11-04. English school demonstration, Asia/Manila. This is an owner map, not another source of borrowing policy. SRS.md and SCHEMA.md remain authoritative for eligibility, permissions, lifecycle, limits and lock order. Hosting stays deferred.

| Capability | Canonical owner | Presentation contract |
| --- | --- | --- |
| Current identity, roles and sessions | app/auth.php and existing account authorization helpers | layout.php reuses freshly verified identity; navigation never grants permission. |
| Navigation, route indication and skip target | views/layout.php | Same role workflow routes without Settings; visible names, unique current route, grouped links and compact desktop sidebar. Below1100px, progressively enhanced native modal left drawer with icon-only named trigger and sticky current-page label; visible wrapped links without JavaScript/dialog support. |
| Appearance preference | public/assets/theme.js, appearance_toggle_html() in views/portal_ui.php and views/layout.php | One visible 44px named moon/sun header button per responsive state, including guests. Binary Light/Dark actions reuse browser preference; legacy System/device behavior persists until a click. Early effective theme, cross-tab updates, storage-error feedback and no-JS device fallback. Motion is finite/reduced-motion-aware; public/settings.php is a guarded compatibility redirect only. |
| Decorative route icons and linked summaries | views/portal_ui.php | Static allowlisted SVG; hidden from accessibility tree, nonfocusable. Escape text/URLs; consume existing counts, do not query or mutate. |
| Counts | existing dashboard_counts calculation | Profile and dashboard share the calculation and summary presentation; own-student scope stays unchanged. |
| Colors, typography, spacing, focus and responsive shell | public/assets/app.css | docs/design/DESIGN.md mirrors tokens. Natural scrolling, wrapping tabs, 44px actions, 16px inputs, checkbox errors in normal flow. |
| Landing imagery and bounded motion | views/landing.php, public/assets/entry.css | Existing illustrative images/copy; reduced-motion rules; no protected covers or new promises. |
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


## Accepted refinement owners — 2026-11-13

Layout groups the same role-allowlisted links; portal_ui owns accepted SVGs and escaped count labels. app.css owns compact shell/dashboard/discovery tokens; workspace.css owns existing directory/inspector and native editor-section presentation. app/dashboard.php separately authorizes administrator scalar account totals; existing dashboard_counts remains unchanged for student/librarian scope. Readable audit labels supplement the original codes without changing exact GET filters or exposing payloads. About source/information and existing pagination owners remain unchanged. Original POST forms/tokens/fields stay intact; failed editor actions and copies_page open the relevant native section, including without JavaScript. Prototype-only simplified validation, controls and examples do not define application behavior.


## Staff dashboard parity correction — 2026-11-13

The earlier redesign completion statement overstated staff-dashboard parity. Corrected the librarian and administrator dashboards to the accepted layout: whole-card summaries, two-column work/account panels, protected book covers, librarian student verification and own recent desk activity, administrator recent account activity and active staff access. All values come from bounded read-only queries. Librarians see only their own allowlisted circulation activity; administrators gain no circulation permissions. Existing student dashboard, role checks, About content, pagination and pilot records remain unchanged. No schema changes, commit or push; hosting deferred.

Verification: 36 regression scripts passed before the final administrator presentation adjustment; final affected dashboard/design tests, PHP lint and 416 workflow HTTP assertions passed. Final browser rerun covered 248 role/theme/viewport combinations with zero runtime exceptions and zero sampled contrast failures. Final librarian/administrator dark screenshots were visually inspected. Strict static UI audit reported zero findings. Actual 400% browser zoom, physical-device and screen-reader acceptance remain manual; no claim of a bug-free system. Usage tool unavailable.

Evidence: verification/STAFF_DASHBOARD_PARITY_2026-11-13.md.


## Public entry presentation — 2026-11-14

Accepted mockup implemented through views/landing.php, views/auth_entry.php and scoped entry.css. Existing controllers retain methods, fields, CSRF, validators, generic failures, redirects, throttling and recovery safeguards. Guest header styles never replace the authenticated sidebar. About remains a real /about.php destination; How borrowing works links to #how-borrowing-works on the landing page. No mockup theme/motion reviewer toolbar or fake submit behavior is shipped.

entry.js progressively reveals native type=button password toggles (44px targets, aria-controls and state-specific accessible labels); it never submits, stores, logs or sends field values. JS-disabled forms remain usable and toggles stay hidden. Registration retains the applicant's own escaped submitted ID/name/username on an error with no-store, but never passwords; duplicate feedback discloses no other account details or ID. forms.js still owns linked error summaries and focus. Native details supplies recovery help. Finite entrance/photo animations stop under prefers-reduced-motion; natural scrolling and narrow stacked forms remain. Existing application appearance storage and no-JS system theme are reused. See verification/ENTRY_DESIGN_2026-11-14.md for actual evidence/limitations.


## Responsive navigation owner — 2026-11-15

views/layout.php remains the only permitted-link/identity/current-route owner. public/assets/navigation.js moves that exact header into the native dialog below1100px and restores it to its original DOM position at desktop widths. It never clones links or reads/stores/fetches account records, changes route URLs or submits forms. public/assets/navigation.css supplies the accepted drawer and compact sticky header using app.css theme tokens; desktop and pagination keep their existing owners. Scripts and controls are emitted only for freshly authenticated identities. Guest header remains unchanged.

Modal contract: icon-only Open navigation trigger with aria-controls/aria-expanded; native dialog background inertness; explicit Tab/Shift+Tab wrap; Escape/Close/backdrop dismissal restores focus to trigger; native navigation closes the drawer without rewriting destinations. Role-scoped current route is highlighted; actual escaped page title, role and status stay visible when closed. Menu body scrolls independently, long identities wrap, safe areas and reduced motion are respected. Resize/pagehide/pageshow clear stale modal/scroll locks and restore desktop DOM placement. Without JavaScript/native dialog support, the original navigation is visible and wraps. No new preferences or storage. Evidence: verification/NAVIGATION_DRAWER_2026-11-15.md.

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
