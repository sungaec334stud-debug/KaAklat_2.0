# Accepted design implementation

Goal: apply the 13 November three-role design preview to the maintained application while retaining every existing information field and workflow. The preview is the visual reference, not a new permission or borrowing specification. SRS/SCHEMA remain authoritative. No schema, pilot writes, fake records, external services or dependency changes. No commit/push requested.

## Coverage and implementation order

- [x] Shared shell: compact headers, grouped role navigation and the accepted static line icons. Retain every permitted link, school identity, account identity/status, skip target and System/Light/Dark.
- [x] Dashboards: smaller personalized welcome; linked real counts; actual student deadline; librarian desk queue; administrator account/activity summary; protected student catalog preview. No invented counts, dates, or fake notifications.
- [x] Catalog and student screens: compact retained filters/results/chips and own-state badges; calmer request cards; clear profile definition list. Every status/history/reason/decision/cancellation control stays available.
- [x] Staff screens: consistent list/detail facts and action spacing; sectioned book details/copies/cover/archive; readable audit labels alongside existing codes and exact filters; recovery guidance/secret handling unchanged.
- [x] About/logout: retain the exact university, mission/vision summary, values, official link, six steps, disclaimer, developer image/name/qualification/contact and all logout information/actions. Restyle only.
- [x] Verify regression, HTTP, full role/theme/viewport browser checks, keyboard/errors/no-JS, content/form parity and unchanged pagination. Document actual limitations.

## Owners

views/layout.php owns grouping; views/portal_ui.php owns allowlisted decorative SVG. app.css owns shared tokens/shell/dashboard/catalog and existing pagination; workspace.css owns staff/request/profile/About refinements. Original forms stay in the DOM, including no-JS fallback. Native section disclosures never split or nest forms; failed POST sections open. app/dashboard.php may add authorized read-only administrator totals; existing circulation services remain untouched. Raw audit codes remain visible alongside presentation-only readable labels.

## Review risks

Check long names/titles and 320px overflow, selected-record errors and field focus, preserved search/page context, lazy covers after selection, no-JS access to all forms, student-only counts/catalog previews, administrator circulation denial, and exact About information preservation. Never infer activation/suspension dates. Never turn a mock editable-copy count into an application field.

## Reference

Accepted working prototype: C:/Users/63961/.codex/visualizations/2026/10/29/01a187f7-432f-7e73-ae08-e58339a0448a/kaaklat-design-review/index.html.
