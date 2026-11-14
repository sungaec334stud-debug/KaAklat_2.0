# Approved entry design — local verification, 2026-11-14

## Approved public entry design — 2026-11-14

Implemented the accepted landing/login/registration mockup, with a matching recovery form. Photo-led entry panels, concise field guidance, native expandable recovery help, password visibility buttons, light/dark layouts and finite reduced-motion-aware animation reuse existing images and palette. About remains in guest navigation and its complete content is unchanged. How borrowing works remains a real landing anchor. Prototype controls, simulated submissions and fictional preview messages were not copied into the application.

Verification: 36 regression scripts; 427 isolated workflow HTTP assertions; 132 PHP files linted; JavaScript syntax and strict static UI audit passed (zero findings). Chromium covered 64 entry route/theme/viewport combinations (320/360/390/768/820/1024/1280/1440px), images, wrapping, keyboard password/recovery controls, error summary/field focus, reduced motion, 320 CSS-pixel reflow, JS-disabled invalid registration, and active/pending student/librarian/admin sign-in. Zero browser runtime exceptions. Guest shell styles are scoped separately from authenticated navigation, including authenticated visits to entry routes. Pilot rows/schema/covers/configuration match the fresh read-only baseline; no test accounts added to pilot. Existing backup/performance/research work preserved. Actual 400% zoom, physical-device/Safari and screen-reader checks for this new presentation remain manual. Hosting deferred; no commit/push.

Evidence and replay commands: verification/ENTRY_DESIGN_2026-11-14.md.

## Scope and preservation

No app service, SQL, migration, lock, circulation rule, role authorization or audit transaction changed. The presentation around real forms was replaced; original POST fields/CSRF/redirects and account/recovery services remain. Applicant-supplied ID is retained only in their escaped no-store registration input. It is excluded from feedback disclosure/logs. The pilot fingerprint matched for all table rows, definitions, private covers and configuration before/after. No pilot test account or history was created.

The approved mockup is kaaklat-entry-preview-20261114 under the Codex visualization directory. Its theme/motion reviewer toolbar, fake client-only submissions and preview disclaimers are intentionally excluded. Real About content and navigation are retained. The two library photos are explicitly illustrative, not asserted to depict the university.

## Executed checks

- 36 regression scripts passed, including authentication hardening, school ID approval/privacy, staff/recovery, circulation, ownership, concurrency, audit rollback and presentation tests.
- 427 workflow HTTP assertions passed using isolated fictional databases. Added entry checks cover About routes, absence of prototype messaging, retained escaped nonsecret values, password exclusion and forged CSRF. Existing checks exercise actual successful registration, activation, role sign-in and recovery.
- 132 PHP files linted, entry.js/browser checker syntax passed, strict static UI audit returned zero findings, whitespace checked.
- 64 Chromium combinations: four routes × Light/Dark × eight widths (320, 360, 390, 768, 820, 1024, 1280, 1440). No horizontal overflow, missing images or runtime exceptions. One H1 per page and About navigation checked.
- Keyboard Enter toggles password visibility and native recovery disclosure. Invalid registration moves focus to the existing error summary; its link focuses school ID. Reduced motion disables entrance/photo animations. 320 CSS-pixel reflow checked.
- JavaScript-disabled invalid registration reached server validation, retained nonsecret inputs and did not repopulate password. Forms remain enabled without JS; decorative password controls stay hidden.
- Active/pending students, librarian and administrator signed in through the new real form. Dashboards kept their existing shell; signed-in visits to the entry route preserved responsive navigation direction.
- Desktop light login, desktop dark landing and phone registration screenshots visually inspected against the approved design. Screenshots retained locally in the task workspace entry-browser-output; raw summaries in verification/entry-2026-11-14/.

## Replay

From the maintained application root in PowerShell, with XAMPP MySQL running:

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
& 'C:\php-8.4.14\php.exe' tests/student_profile_browser_fixture.php
# Use the PRIVATE fixture.json path printed by that command:
node tests/entry_browser_check.cjs 'PATH_TO_PRIVATE_FIXTURE_JSON' tmp/entry-browser-output
```

The browser check uses the existing loopback acceptance router on 8028, headless Chrome debugging on 9428, and a dedicated Chrome profile in its output directory. It never reads pilot credentials into the browser, prints fixture passwords or writes to pilot tables. Existing fixture manifest passwords are private; do not commit them. Node's built-in WebSocket and the existing browser_protocol.cjs are used; no new package dependency.

Resources retained: browser scratch database kaaklat_acceptance_7e671cd49f68; private manifest tmp/student-profile-browser-7e671cd49f68/fixture.json. HTTP harness runs retain separately named scratch databases printed by their output (including kaaklat_acceptance_1e9e683cbc3d). No cleanup or database deletion performed.

## Manual acceptance still needed

1. Refresh the home, login, registration and recovery pages with Ctrl+F5. Check About opens the complete current page.
2. Try Light/Dark, keyboard Tab/Shift+Tab/Enter, show/hide password and recovery disclosure; enter invalid registration values.
3. Repeat on a real phone/tablet and at actual 400% browser zoom. Confirm no horizontal scrolling or hidden actions. Test with your screen reader if available.

Chromium viewport emulation/320 CSS-pixel reflow is not proof of actual 400% zoom, Safari/iOS/iPad hardware or screen-reader acceptance. Prior manual acceptance reports belong to previous designs. No hosted or deployment compatibility claim. This is not a guarantee that all possible bugs are absent.
