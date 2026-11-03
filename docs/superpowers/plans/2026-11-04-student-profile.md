# View-only student profile implementation plan

**Goal:** show the signed-in pending/active student's existing account details and own borrowing totals; no editing or new data fields.

**Scope:** app/student_profile.php (safe read model), views/student_profile.php (semantic account summary and existing summary cards), public/profile.php (GET/HEAD-only protected endpoint), views/layout.php (student link), narrowly scoped CSS for a definition list. Existing dashboard_counts is the count owner; current_user owns fresh identity/status/session validation; shared layout/CSS own navigation, focus, palette and responsive behavior. SRS/SCHEMA determine UTC storage/Manila display. No schema/auth/business-rule changes or new dependencies.

**Design:** retain navy headings, blue links, white bordered surfaces and system typography from DESIGN.md/app.css. A compact definition list is the account-information owner; shared summary-grid/count-card styling connects profile to dashboard. No avatar, animation, controls implying editing or decorative metrics. Empty totals lead to catalog; active status is not a guarantee of borrowing eligibility. Shared page-error patterns provide login redirect/403/400/405/503 and safe recovery links. English/Asia-Manila; no locale/date picker/input decision needed for read-only text.

- [x] Write failing-before/passing-after isolated tests; final25 profile checks PASS.
- [x] Implement safe service/view/endpoint/navigation and minimal scoped styling; no-store/method/query guards.
- [x] Extend scratch HTTP acceptance, preserving original cases/backup work;133 assertions PASS.
- [x] Run23 regressions/90 lints/HTTP/17 race scenarios/preserved52-check restore PASS. Browser access attempted then stopped by URL-confidence policy; no UI/keyboard/zoom pass. Python alias inaccessible, static audit not run. Exact limitations recorded.
- [x] Update SRS/DESIGN/SCHEMA/PROGRESS/TEST_PLAN/CHANGELOG and append actual prompt/outcome. No commit/push; hosting deferred. Favorites next; performance outstanding.

Additional confirmed defect within date display: connection timezone inherited SYSTEM/+08 despite documented UTC storage. A failing test preceded session-only UTC initialization. No historical timestamp backfill or new schema; legacy dates remain an operator-review limitation.

**Risks to check:** long names/usernames wrap; no other student's totals; pending users still browse; sensitive auth fields never selected/rendered; errors do not expose account/SQL details. Browser/keyboard evidence cannot be inferred from markup/HTTP success.
