# Authentication entry and browser-history verification — 2026-11-24

The reported screen was a real mixed state: Login/Registration lacked a signed-in guard, while the layout independently detected the valid session and rendered its sidebar. A failing browser test reproduced authenticated Login remaining on the entry form. The fix adds one shared server guard before GET rendering and POST processing. It uses existing current_user() database validation, redirects valid identity to the role-aware root dashboard with303, and keeps existing expiry/revocation/suspension handling. Database failure while checking claimed identity gives a safe503. No table, migration, credential policy, borrowing rule or role permission changed.

Login explicitly uses no-store like Registration. On a persisted pageshow for Login/Registration only, entry.js uses location.replace(location.href) to issue a fresh GET. It neither replays POST nor adds history. Ordinary app Back/Forward, guest entry, explicit logout and no-JavaScript server redirects remain native. Recovery routes are outside this fix. Existing header design, animation and navigation owners are unchanged except the entry script cache version.

## Executed evidence

- Initial browser run failed the signed-in Login-to-dashboard assertion before implementation.
- 39 existing PHP regression scripts passed in isolated fixtures, including auth_regression_test.php idle expiry, revoked and suspended session checks.
- 606 workflow HTTP assertions passed:558 existing plus48 new. All four role/status identities cover guest entry, successful sign-in, direct signed-in GET and stale/forged entry POST, no new account insertion, retained identity, explicit logout, real guest Login/Registration after logout and session-version revocation. Old broad/profile test setups were corrected to use fresh guest clients when testing registration or a fresh sign-in; assertions were retained.
- Eight actual Chromium role/viewport history cases (active/pending student, librarian, administrator at375/1440px),48 Back traversals, direct entry redirects, recent Catalog/About Back, pre-login history traversal, explicit logout and no-JavaScript server guard passed. Zero runtime exceptions.
- Six unit checks cover persisted/ordinary pageshow and non-entry routes. Only cached Login/Registration replaces its current URL.
- Existing entry browser suite passed64 Light/Dark route/viewport combinations, password-eye keyboard interaction, recovery disclosure, validation focus,320px reflow, reduced motion, no-JavaScript forms and four role/status sign-ins. Zero runtime exceptions.
- Changed PHP files and JavaScript syntax passed. Independent read-only review found no blocking issue after inspecting guard, session lifecycle, logout, layout, native navigation and tests.

One full HTTP attempt timed out on the first new sign-in after the existing workflow assertions. The unchanged rerun completed all606 assertions. Earlier HTTP failures exposed authenticated test clients being reused as guests, corrected in test setup rather than weakening the guard. Raw logs and private fictional credentials remain outside the maintained project.

## Reproduce and limitations

Use isolated fictional configuration only: php tests/run_regression.php; php tests/pickup_http_acceptance_test.php; node tests/auth_history_test.cjs. Create a new private fixture using php tests/student_profile_browser_fixture.php --campus, then run node tests/auth_history_browser_check.cjs <private-fixture.json> <external-output-directory> and node tests/entry_browser_check.cjs <private-fixture.json> <external-output-directory>.

PHP8.4 on Windows with local MariaDB and Chromium emulation. Actual BFCache use is browser-dependent; unit evidence proves the persisted-event handler, while actual Back traversals prove the observed navigation outcome. Physical devices, Safari, screen readers and actual400% zoom were not rerun. This is not a guarantee of no defects. Verification used newly created fictional databases only, never kaaklat_pilot records. Private configuration, covers and schema remain untouched. Hosting deferred; no commit or push.
