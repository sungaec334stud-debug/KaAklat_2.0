# Subtle interaction motion - 2026-11-21

## Scope and behavior

Implemented the approved subtle/professional animation refinement using one shared motion.css loaded by views/layout.php. Buttons have120ms press feedback; navigation/status tabs, fields and selected rows use160ms state transitions. Routine pages, feedback and native disclosures use brief opacity-only arrivals; existing entry composition is shortened to240ms with no delay (photo420ms). Existing eye/theme/drawer effects remain. Workspace explicit changed-record selection uses a180ms native animation, cancels previous motion on rapid selection, preference change or pagehide, and keeps immediate DOM moves/focus. No library, fake loading, delayed submission, scroll interception or content gating. Reduced-motion and no-JS fallback remain; pagination design and backend are unchanged.

Verified on isolated fictional fixtures:36 regressions,458 HTTP assertions,19 theme and17 eye controller checks,129 PHP lints and22 JS syntax checks. Motion browser120 route/theme/viewport combinations and6 keyboard selection cases; existing78 workspace layouts/30 interactions,64 entry combinations,25 header layouts/50 toggles,18 eye layouts/30 field cases,56 navigation layouts/26 routes and10 catalog-alignment cases. Zero browser exceptions and zero strict design-audit findings. Pilot row/schema/covers/config fingerprints remain exact. Physical devices, Safari, screen readers, actual400% zoom and hardware frame-rate measurements remain manual. See verification/MOTION_REFINEMENT_2026-11-21.md. No commit or push; hosting deferred.

Runtime changes are restricted to public/assets/motion.css (new shared CSS), public/assets/workspace.js (existing selection owner), and views/layout.php (one shared stylesheet and refreshed workspace JS cache version). No public controller, forms.js, navigation.js, theme.js, entry.js, service, SQL, migration, cover or configuration changes. The existing default DOM remains visible; no spinner or success is invented before a server result. Form feedback never auto-dismisses. Motion does not intercept link navigation or wait to focus an error/record heading. Existing entry composition and its reduced-motion fallback are preserved with shorter timing.

## Reproduction and evidence

All commands below run only inside the isolated source copy with fictional default configuration, never the pilot config. Browser arguments refer to a newly created private fixture from tests/student_profile_browser_fixture.php --campus.

```text
php tests/run_regression.php
php tests/pickup_http_acceptance_test.php
node tests/theme_test.cjs
node tests/password_visibility_test.cjs
node tests/motion_browser_check.cjs <fictional fixture.json> <external output>
node tests/workspace_browser_check.cjs
node tests/entry_browser_check.cjs <fictional fixture.json> <external output>
node tests/header_theme_browser_check.cjs <fictional fixture.json> <external output>
node tests/password_eye_browser_check.cjs <fictional fixture.json> <external output>
node tests/navigation_browser_check.cjs <fictional fixture.json> <external output>
node tests/catalog_alignment_browser_check.cjs <fictional fixture.json>
python <premium skill>/scripts/audit_project.py . --mode strict
```

The new browser test failed first for the absent shared motion stylesheet, then passed after implementation. Browser synchronization now requires a changed performance.timeOrigin before inspecting a newly navigated document, including same-route no-JS reloads. No confirmed business/service defect was found. The final entry check covers the shorter entry timing; the final motion matrix covers corrected actual account-tabs selectors. Sanitized JSON evidence is in motion-2026-11-21; raw screenshots, logs, browser profiles, private fixture accounts and work plans stay in the external task workspace. Desktop staff and dark phone login screenshots were visually inspected. Existing workspace verification also proves original form identity, values and error-link associations survive selection.

## Preservation and limits

All app/, bin/, schema.sql and migration hashes match the initial source. Before/after full pilot fingerprints cover every table's row hashes/definitions, private configuration and cover inventory, and match exactly. No pilot fixtures are inserted. A pre-transfer private backup and exact baseline/hash checks protect final application to the maintained folder; the final transfer/startup outcome is recorded below after execution.

Environment: Windows, PHP8.4.14/PDO, local MariaDB10.4.32, existing headless Chrome and repository browser protocol tools. The duration budget is120ms press/160ms feedback/180ms selection; bounded existing photo arrival420ms. This is behavioral/browser emulation evidence, not a physical-device FPS or hosted latency claim. No new concurrency/performance database sweep is claimed because no query/transaction/network policy changed. Physical phone/tablet, Safari, screen-reader, actual400% zoom and slower hardware remain manual acceptance. Hosting is deferred. No commit or push.

## Final application and startup verification

Applied 20 scoped runtime/test/document/evidence files after verifying every pre-change source hash. All 163 final tested runtime/test source hashes match the maintained project. Four normal guest entry/About GET routes return200 and load the shared motion stylesheet; the unchanged normal startup health endpoint reports ready. Modified maintained JS/PHP syntax and Git whitespace checks pass. Static independent review found no actionable issue in the three scoped runtime owners.

The final pilot row/schema/covers/private-config fingerprint exactly matches the pre-change baseline. A fresh pre-change database/covers/config/source backup was checksum-verified before use and again after moving it into the protected external task workspace motion-work/before-change-backup. Raw generated test files, plans, fixtures and screenshots remain outside the maintained project. No commit or push was performed.
