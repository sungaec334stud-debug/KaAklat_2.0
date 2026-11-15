# School portal visual refresh — 2026-11-01

User direction: modern, intuitive design in school blue, white and gray. Retain plain PHP, semantic HTML and custom CSS; no external fonts, libraries or invented school logo/name.

Design: navy header, blue primary actions, white bordered cards and light gray background. System sans-serif typography, consistent controls, readable helper text, labeled status tabs, book cards and responsive layouts. Dashboard presents task cards appropriate to the existing authenticated role, with short explanations. Signed-out welcome explains registration and borrowing. Shared navigation marks its current page; skip-to-content and visible focus styles support keyboard use. Error/success states retain semantic accent colors with text.

Scope: public/assets/app.css consolidated into one visual system; public/index.php dashboard/welcome presentation; views/layout.php shared shell. Authentication and circulation rules unchanged. This is an agent-selected visual direction under the user's redesign request, not independently approved school branding.

Verification: 14 existing regression scripts and all 45 PHP lints PASS. Browser desktop/mobile/keyboard visual acceptance remains pending; previous manual passes do not cover this redesign. No accessibility certification or user learning outcome measured. Try dashboard, catalog/details, request tabs, librarian forms and login/register at 360/768/1280px and 400% zoom. Check contrast, wrapping, selected navigation and Tab focus. Hosting deferred.
