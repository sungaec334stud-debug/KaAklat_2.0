# KaAklat campus portal visual overhaul — design for review

Status: user approved the mockup and requested implementation. Applied to the actual shared interface; named verification and remaining manual limits are in verification/CAMPUS_PORTAL_ACCEPTANCE.md. Hosting deferred; no commit/push requested.

## Standalone mockups for user review

User requested a mockup before implementation. Ignored prototype tmp/campus-portal-mockup/index.html supplies dashboard/catalog concept navigation with fictional student/counts and illustrative typographic covers. Static exports: desktop-dashboard.png1440×960, desktop-catalog.png1440×1100, phone-dashboard.png360×1600. All three inspected visually; phone export refined after the first capture clipped the narrow composition. The phone concept uses an explicit360px mock canvas to compensate for the export browser's minimum window layout width; this is **not** actual application responsive/keyboard/zoom acceptance. No authenticated website or user browser tab accessed for the exports; isolated headless profiles rendered only the local fictional mockup. No application/backend/data changes, dependencies, commit or push. Prototype links other than dashboard/catalog are illustrative. User subsequently approved this direction/brief and authorized application to the real website. The preceding paragraph describes the historical mockup stage only.

## Intent and approved direction

Give KaAklat a recognizable school-library portal identity while preserving its working PHP/PDO product. Students should quickly find books and their borrowing status; staff should recognize the correct task, student and copy before acting. Blue, white and gray remain the school palette. The user selected **Campus portal — crisp geometric typography and compact task-focused layouts** over the alternatives below.

The signature is a campus-directory composition: a strong navy navigation spine, a narrow cobalt margin rule, square tab-like route markers and orderly white work surfaces. It should feel like one coherent institutional product, not a landing-page theme applied to forms. The branding stays KaAklat and the existing open-book SVG remains.

## Approaches considered

| Direction | Strength | Tradeoff | Decision |
| --- | --- | --- | --- |
| Library reading room | Book-inspired headings, airy literary atmosphere | Less compact for circulation queues | Not selected |
| Campus portal | Clear navigation, crisp typography, compact task hierarchy | Requires care to avoid excessive density | Selected by user |
| Contemporary bookshop | Larger covers and exploratory discovery | Gives less emphasis to staff work | Not selected |

## Boundaries and non-goals

- Presentation only: no database/schema/config/permission/transaction/borrowing-rule changes.
- Preserve all routes, GET filter parameters, form methods/actions, field names/values/constraints, CSRF tokens, element IDs/error targets, POST allowlists, pagination and redirects.
- Preserve original information, status meanings, reasons/explanations, deadlines, copy codes, accessible action names and success/error/empty states. Never hide business information to make a card shorter.
- No new product features, dashboards/metrics, navigation destinations, account editing, favorites, notifications, arbitrary avatars or invented institutional claims.
- Existing native controls, minimal JS and server-rendered PHP remain. No framework, package manager, remote font, paid API or runtime dependency.
- Do not change application services, security/session checks, derived availability, audit or lifecycle code. If a genuine behavioral defect appears, record it and keep this overhaul focused; do not silently expand policy.
- Existing demo data, private covers/config/backups, fictional test resources and research output remain untouched. No database cleanup or seed import into existing data.

## Visual system

Existing app.css remains the canonical runtime token owner; DESIGN.md mirrors the accepted values and rationale (no generator or second token system).

| Role | Proposed value | Use |
| --- | --- | --- |
| Deep campus navy | #0B2350 | Navigation spine, headings, strong structural sections |
| Action blue | #1759D4 | Primary actions, links and current-route accents |
| White | #FFFFFF | Main work surfaces and high-contrast reverse text |
| Cool gray | #EEF2F7 | Page canvas and secondary surfaces |
| Slate | #52637A | Supporting copy and labels |
| Border gray | #D4DEEB | Rules, dividers and field/panel separation |

Retain distinct semantic danger, success and pending colors with text labels; they are functional signals rather than extra brand colors. Verify actual contrast of foreground/background states, including hover/focus/disabled/placeholder and selected tabs, before finalizing tokens. Aim for text contrast>=4.5:1 and visible component/focus contrast>=3:1.

Typography proposal: locally available Bahnschrift/Segoe UI Variable/Segoe UI/system sans for compact geometric display headings, and Segoe UI/system sans for body/controls. Fallbacks are intentional; no downloaded font and no font-load layout shift. Body/inputs stay16px, labels typically14px, meaningful page headings about30–34px desktop/26px narrow. All sizes use rem; no tiny data text or all-uppercase paragraphs.

Use an8px spacing rhythm, compact8px panel corners and6px control corners. Status tags remain compact and text-bearing. Use strong borders/rules rather than widespread shadows, gradients, blur or decorative icons. Any icon added to shared navigation is decorative beside an unchanged text label, never a new icon-only action; icons may be omitted if they add noise.

## Layout and screen treatment

### Shared authenticated shell

Retain the existing desktop sidebar at1100px+ and its232px geometry, preserving learned route locations and document scrolling. Restyle it as the navy campus directory with a readable identity block and distinctive current-route treatment. Optional role caption uses the current verified role only; it does not add permissions or account data.

Below1100px, retain visible wrapping top navigation. No new drawer, hamburger, collapse state, focus trap or JS dependency. At360px and actual400% zoom, every route remains visible/reachable. Sidebar scroll remains independent only when its own navigation is taller than the viewport; content/forms retain natural document height. Never impose overflow:hidden or fixed viewport height on the main content.

### Dashboards and profile

Use a compact welcome/title area and a coherent segmented borrowing-summary strip. Preserve the existing counts and their exact link destinations. Below, task panels form a structured campus-service directory, with consistent headings, explanatory copy and clear links. Keep the current role-specific task order and no added statistics. My Profile keeps its definition list and linked counts, with the same surface/rule system.

### Catalog and book detail

Build a clean collection index: a clearly bounded filter surface and consistent cover wells, restrained title hierarchy and readable author/category/year/availability. Maintain current page size, filter logic and cover authorization. Narrow grids may become more compact only when titles/metadata/links remain fully legible; use one column if two would clip content. Detail pages give the cover and book data clear hierarchy without hiding the request eligibility explanation.

### Requests, circulation and management

Retain the same tabs, records, order and actions. Distinguish identity/book/copy information, lifecycle/deadline information and action regions with rules and spacing. Approval/rejection and receive/cancel/archive choices remain visually separated; caution meaning is unchanged. Multi-author/copy forms, reasons and decision explanations retain all entered values, constraints and summary/field error targets.

Staff/student lists and audit metadata remain the existing bounded datasets. If a semantic table exceeds narrow width, only its table surface gets an accessible horizontal scroll region; do not convert comparison data into unlabeled cards or clip columns. Preserve all current search/page/filter state and server behavior.

### Authentication, recovery and signed-out landing

Login/register/recovery use the same portal identity and a tidy form column, with existing guidance/actions. Preserve password-manager/paste behavior, masks, recovery-secret handling and no-store behavior. No new recovery workflow or fake links.

Recompose the signed-out welcome as the public entrance to the same campus portal: navy-led typography, clear login/create-account actions and the two existing illustrative library JPEGs. Keep their alt text/dimensions/provenance and truthful four-step borrowing explanation. No school photographs or new hero claims. The landing retains its dedicated stylesheet and explicit render flag; authenticated dashboards must not receive landing markup or styling.

## Interaction and accessibility contract

- Existing native links/buttons/inputs retain semantics. Styling must not disable pointer, keyboard, focus or form behavior.
-44px action/navigation targets and16px inputs remain the project baseline. Text/values wrap; avoid ellipsis/clamping for important information.
- Skip link/current-route indication, status text, label associations, error-summary focus/links and visible focus rings remain intact.
- No new alert/confirm/prompt, dialogs, select replacement, animation-controlled visibility or screen-local validation code. Existing server validation still works without JS.
- Use only subtle hover/focus transitions. Any optional entrance motion must be short, nonessential and disabled for prefers-reduced-motion. No perpetual animation/parallax/scroll reveal.
- Forms and long pages use natural vertical scrolling. Browser zoom, forced colors and reduced motion must not hide content or actions.
- English copy and Asia/Manila date formatting remain unchanged. No new locale/policy assumptions.

## Implementation shape and regression boundaries

Primary owners: public/assets/app.css, public/assets/landing.css, views/layout.php, views/landing.php and presentation-only wrappers/classes in existing dashboard/page/view markup when necessary. Prefer replacing/consolidating the existing styling rules over appending conflicting override layers. Maintain selector/ID compatibility with forms.js and existing tests. Do not restructure backend/page mutation branches to obtain a layout.

Expand DESIGN.md and add a concise UX-CONTRACT.md if needed to map shared interaction ownership, referencing SRS/SCHEMA rather than copying business rules. Any static audit must explicitly target actual public CSS/JS and rendered HTML; the premium checker cannot parse PHP, and zero eligible files is not a pass for application UI. Do not introduce npm/Composer just to satisfy a generic audit.

## Verification and acceptance

1. Record baseline Git status, PHP lint, regression/HTTP results and representative current screens if browser access permits. Use isolated fictional accounts/databases only.
2. After implementation run PHP lint, relevant regression scripts and the full existing HTTP acceptance suite. Confirm shared form/navigation/ownership/escaping/error behavior stays intact. Database concurrency services are untouched; rerun races only if actual changes/failures justify it.
3. Check guest, pending/active student, librarian and administrator routes. Cover landing/auth, dashboards, catalog/detail/request/history/profile, review/pickup/returns/books/students/staff/recovery/audit, including empty and invalid states.
4. Observe desktop,768px,360px, actual400% zoom, keyboard Tab/Shift+Tab/Enter, summary-to-field focus, JS-off, reduced motion and long names/titles. Record actual screenshots/results where permitted. Check local overflow/contrast rather than relying on a screenshot alone.
5. Respect browser access policy. If Computer Use again cannot determine the URL, stop that automation, do not bypass it through another control channel, complete independent checks and provide a short manual matrix. Never infer visual or keyboard passes from HTTP tests.
6. Update PROGRESS/TEST_PLAN/CHANGELOG/prompt and evidence with exact results/limitations. Older user acceptance is not acceptance of the overhaul. No commit/push unless separately requested.

## Builder discretion and unresolved decisions

The builder may tune exact spacing, cover-well geometry and system-font fallback within the values/constraints above, using measured contrast/reflow and rendered screenshots when available. These are implementation choices, not new policy. No unresolved business or permission decision is intended. User approval of this concrete brief is the remaining design gate.
