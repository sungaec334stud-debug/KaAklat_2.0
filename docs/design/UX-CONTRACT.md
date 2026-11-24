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


## System-wide navigation and expanding-section motion - 2026-11-23

Extended the accepted native document crossfade, sliding current-navigation marker and genuine pending feedback from guest pages to all existing signed-in HTML views. Desktop sidebar/mobile top bar retain continuity. Native links, query/filter/status tabs, browser history and form submissions remain authoritative; no router, artificial delay or persisted inputs. Existing button, selection, theme and password-eye feedback is preserved.

Extracted the Forgot password 260ms measured height behavior from entry.js into shared disclosure.js, loaded after workspace transformations. Native expandable sections now rotate their arrows and animate opening/closing: help, catalog filters, request history, book-editor sections, staff creation and nested student-ID claim review. Workspace record selectors retain their separate inspector handler. Rapid reversal, keyboard operation, focus return, resize, pagehide, external opens/invalid fields and reduced motion retain natural final layout. No-JS/missing Web Animations support uses native details. Existing editor spacing and pagination design are preserved.

Verification: 150 signed-in transitions across four account roles/statuses, Light/Dark and375/768/1440px;21 primary plus2 nested measured disclosures;2 same-page status tabs;12 prior help cases;18 guest transitions;56 drawer layouts/26 routes;160 header comparisons;25 theme layouts/50 toggles;120 existing feedback layouts/6 selections;30 navigation guard checks;36 regression scripts;469 workflow HTTP assertions. Zero browser runtime errors; PHP/JS syntax and strict design audit pass. Static detector style advisories concern existing approved accent borders and their matching marker, retained intentionally. Independent code review found no actionable correctness issue. Physical devices, Safari, screen readers and actual400% zoom were not rerun. Native cross-document effects depend on browser support; fallback remains usable native navigation. No backend/schema/pilot-data changes, hosting, commit or push. See verification/SYSTEM_MOTION_2026-11-23.md.

This system-wide motion entry supersedes the2026-11-22 guest-only asset boundary. Native server workflows and role restrictions remain unchanged.


## Librarian archived-book review and restoration - 2026-11-23

Implemented Active books / Archived books tabs inside Manage books. Active librarians can search/page archived titles, inspect protected covers, title/authors, archive date and active/individually archived copy counts, and explicitly confirm Unarchive book. The native POST uses CSRF, fresh role/session checks and actor→book locking. It clears only the title archive timestamp and writes catalog.unarchive_book audit atomically; individually archived copies and all borrowing/author/copy history remain intact. Availability stays derived; titles without active copies remain unavailable. Timestamp plus latest archive audit ID rejects stale confirmations, including a later archive in the same second. Repeated restoration returns a clear error without another write/audit. Administrators retain audit access, not archived-title/cover/restoration access; students/pending students are denied.

No schema migration. Existing managed-workspace, status-tab, pagination, theme, animation, time-display and form primitives are reused. A confirmed no-JavaScript View Transition promise exception found during native restoration verification is fixed by a noscript-only stylesheet disabling that optional effect; scripted animation remains unchanged.

Verified in isolated fictional databases:37 regression scripts,32 archive integration assertions,508 workflow HTTP assertions,36 archive browser layouts at375/768/1440px in Light/Dark,30 navigation guard checks and18 existing guest document transitions. Zero browser runtime errors; changed PHP/JS syntax and strict design audit pass. Desktop/mobile screenshots inspected; independent security/correctness review found no actionable issue. Physical-device, Safari, screen-reader and actual400% zoom checks remain manual. No pilot-record/private-config/cover changes, hosting, commit or push. Evidence: verification/ARCHIVED_BOOKS_2026-11-23.md.

Archive read ownership: app/archived_catalog.php authorizes an active librarian before list/detail reads. Normal catalog_book/catalog_books remain active-only. Archived cover access requires the explicit archived=1 context plus fresh active-librarian identity; never infer it from the referrer or broaden ordinary catalog reads. Unarchive requires the native confirmation checkbox, POST/CSRF and current archive version; PRG returns to the archived list with validated search/page context and bounded pagination. Errors retain review context when still archived. Existing active editor is unchanged.


## Student identity review layout - 2026-11-23

Reorganized pending-student identity review into separate labelled Activate account, Correct the school ID and Release a mistaken claim sections. Each native form retains its own required verification checkbox, CSRF token, student ID and stale review version. Confirmation text now names its action; correction includes a uniquely labelled required ID field and linked dashed-format example. Scoped theme-aware CSS aligns checkbox text, fields, hints and buttons with consistent gaps; the existing claim disclosure animation remains. No permission, identity-policy, database or pagination change. Pending archived-book implementation is preserved.

Verified:11 structural/form assertions,6 browser layouts at375/768/1440px in Light/Dark with measured expansion, keyboard checkbox and reduced motion;38 regression scripts;508 fictional workflow HTTP assertions; existing student policy and temporary-table integration tests; changed PHP/JS syntax and strict design audit (zero findings). Zero browser runtime errors; desktop/phone screenshots inspected. Physical-device/Safari/screen-reader/actual400% zoom acceptance was not rerun. No pilot-data mutation, commit or push.


## Requested duration caps librarian approval - 2026-11-24

Approved days may now equal or be shorter than the student's requested days, never longer. The decision service validates the authoritative requested_days after locking/rechecking the pending request and before any allocation, expiry cleanup, decision or audit writes. A longer period is refused even with an explanation. Shortening still requires an explanation; rejection behavior remains unchanged. The review field defaults to requested days, uses that request's HTML maximum and linked guidance, and associates an overlong server error with the duration field.

Existing approved decisions, due dates and historical records are not edited. No schema migration or pilot mutation. Preserved all pre-existing uncommitted documentation. Verified: failing overlong-with-note regression reproduced first; final38 regression scripts,513 isolated workflow HTTP assertions,6 Light/Dark browser layouts at375/768/1440px (1/7/30-day limits, defaults, client rejection and forged POST field error),52 fictional backup/restore compatibility checks, syntax and strict design audit pass. Zero browser runtime errors; independent review found no actionable issue. Physical-device/Safari/screen-reader/actual400% zoom checks remain manual. No hosting, commit or push. Evidence: verification/REQUESTED_DURATION_LIMIT_2026-11-24.md.


## Circulation sorting and status filters - 2026-11-24

Added shared native GET controls to Review requests, Pickups and Returns. Reviews default to oldest first and offer newest, shortest and longest requested borrowing period. Pickups default to nearest collection deadline and filter All outstanding / Ready for pickup / Expired, with farthest-deadline ordering available. Returns default to earliest due date and filter All outstanding / Overdue / Due today / Active-not-overdue, with latest due date available. Active includes due-today loans. Status uses existing UTC48-hour pickup and Philippine-calendar overdue rules; cancelled/collected/returned history remains excluded. No lifecycle, permission, CSRF, requested-duration cap or availability rule changes.

One server allowlist owns filters/sort keys; SQL values remain parameterized, ordering uses fixed internal expressions and deterministic request-ID ties. Filters apply before counts/pagination. Apply starts page1, Clear restores defaults, and pagination/validation/success redirects retain search/status/sort. Existing pagination design and native forms remain. Queue summaries now show status/deadline with readable separators; full details/actions remain intact. Responsive controls use existing theme tokens and44px native targets.

Verified on isolated fictional fixtures:39 regression scripts including34 new queue service checks;558 workflow HTTP assertions;66 browser layouts (11 status/order views ×375/768/1440px ×Light/Dark), selected options, keyboard focus, labelled44px targets, aligned layouts, no overflow, search/sort pagination retention, page1 reset and no-JS GET filtering. Zero runtime exceptions. PHP/JS syntax and strict design audit pass; phone/desktop screenshots inspected and independent read-only review found no actionable issue. Physical-device/Safari/screen-reader/actual400% zoom acceptance remains manual. No schema migration, pilot mutations, hosting, commit or push. Evidence: verification/CIRCULATION_FILTERS_2026-11-24.md.

Canonical circulation selection owner: app/circulation_filters.php. Native controls: views/circulation_filters.php; responsive geometry: workspace.css. Read-only q/status/sort remain in the URL and are never added to mutation payloads. List services validate options again, authorize active librarians and filter before total/page calculation. Clear resets search/filter/sort/page; applying any form omits page so starts at1. Status may change with time without any stored status mutation. Readiness means within the collection window; eligibility is rechecked at physical handover.


## Signed-in Login/Registration and browser history - 2026-11-24

Fixed Login and Registration rendering guest forms inside an authenticated sidebar. Shared app/guest_entry.php validates the current database-backed session before either rendering or processing these entry forms. Verified signed-in students (active or pending), librarians and administrators redirect to the existing role-aware dashboard with303. Stale entry POSTs do not switch identities or create accounts. Existing idle expiry, session-version revocation and suspended/archived checks remain authoritative. A claimed session that cannot be verified gets a safe503 instead of a mixed page. Login now explicitly sends no-store, matching Registration.

Ordinary Back/Forward between app pages stays native. A BFCache-restored Login/Registration page alone makes a fresh same-URL GET using location.replace, letting the server recheck identity without adding history entries or replaying POST. Guest entry pages, explicit logout, native forms and no-JavaScript server redirects remain available. No history trap, local-storage identity flag, artificial delay or recovery-policy change.

Verified using isolated fictional fixtures:39 PHP regression scripts;606 HTTP assertions (48 new);8 role/viewport history cases with48 Back checks;6 cached-entry unit checks;64 existing responsive Light/Dark entry combinations;zero browser runtime exceptions. Changed PHP/JS syntax passes. Independent read-only review found no blocking issue. Actual BFCache selection varies by browser; physical devices, Safari, screen readers and actual400% zoom were not rerun. No pilot-record/config/cover/schema changes, hosting, commit or push. Evidence: verification/AUTH_ENTRY_HISTORY_2026-11-24.md.

Canonical guest-only entry owner: app/guest_entry.php, called by Login and Registration before GET/POST handling. Identity remains owned by app/auth.php. public/assets/entry.js only revalidates persisted pageshow for those two paths; shared navigation retains native history. The header/sidebar is not hidden with CSS to disguise an invalid route.


## Registration password confirmation - 2026-11-25

Added required Confirm password below Password, reusing the shared independently named animated eye control and new-password autocomplete. The guest registration controller requires an exact string match before calling register_student; missing, different or array-valued confirmation cannot create an account or audit. Existing CSRF, password policy, pending-student creation, duplicate-ID/username checks and signed-in entry guard remain. Confirmation is transient and never stored; neither password is reflected after errors. Nonsecret values retain escaping. Server mismatch feedback links the summary to an explicitly invalid confirmation field with its hint/error description, including without JavaScript.

Shared forms.js progressively enhances data-match fields using native custom validity and the existing focused/linked error summary. It rechecks either input and submit (including values filled without input events); unrelated forms retain existing behavior. No custom strength meter, dependency, database/schema/config/cover change. The form keeps existing natural scrolling, spacing, responsive themes and reduced-motion behavior.

Verified in isolated fictional fixtures:39 regression scripts,631 workflow HTTP assertions (25 new),64 entry/browser combinations with keyboard eyes, mismatch blocking, linked focus, correction/revalidation, no-JS submission, responsive Light/Dark layouts and zero runtime exceptions; syntax and strict design audit pass. Desktop/phone screenshots inspected. Physical-device/Safari/screen-reader/actual400% zoom checks remain manual. Pilot records untouched; hosting deferred; no commit or push. Evidence: verification/REGISTRATION_CONFIRMATION_2026-11-25.md.

Registration confirmation owner: public/register.php validates exact equality server-side before registration. Shared views/auth_entry.php owns the named eye control; views/request_ui.php owns summary/field errors. public/assets/forms.js owns optional data-match/native validity integration, rechecking on either edit and submit. Confirmation stays transient, never enters database/audit/storage. No page-specific CSS or alternative validation framework.
