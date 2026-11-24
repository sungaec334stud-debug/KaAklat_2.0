# Registration confirmation verification — 2026-11-25

Added a required masked Confirm password field below Password, with its own existing animated eye and accessible name, new-password autocomplete, concise hint and normal responsive field spacing. Confirmation prevents typing mistakes; password policy remains unchanged. Both passwords must match exactly, without trimming or normalization. public/register.php checks the string types and equality after CSRF and before database registration. It retains the existing guest-only entry guard and pending/student defaults. Existing account service/hashing/audit/schema are unchanged. Confirmation is never stored or reflected; ordinary nonsecret values remain escaped on error.

The existing shared error-summary and field-error helpers provide linked server mismatch feedback, explicit aria-invalid and hint/error description even without JavaScript. forms.js adds narrowly opt-in data-match handling through native custom validity and existing client feedback, rechecking either input and immediately before submit. It does not prevent paste, password managers or autocomplete. Each eye reveals only its associated field. Existing natural scrolling and reduced-motion styles remain.

## Actual checks

- Before implementation, the new isolated HTTP test failed because a missing confirmation did not produce a confirmation error.
- 39 regression scripts passed against fictional/temporary fixtures.
- 631 full HTTP assertions passed:606 existing and25 new. Missing, empty, unequal, array-valued and whitespace-different confirmation reject with linked field feedback, preserve escaped nonsecret fields, echo neither secret and leave user/audit counts unchanged. Matching confirmation creates exactly one pending student with a valid hash and one audit; repeated submission cannot duplicate it; matching confirmation cannot bypass CSRF. HTTP clients submit native forms without JavaScript.
- Existing registration HTTP fixtures now supply matching confirmation to continue exercising original NUL-password, invalid-ID, duplicate-ID and role-forgery tests. Eye-count expectations now include the second registration control.
- Extended entry browser suite passed64 route/theme/width combinations (320 through1440px), keyboard independent eye controls, client mismatch submission prevention, focused linked summary, correction and revalidation after editing the original password, no-JS server submission without password reflection, reduced motion, four role/status sign-ins and authenticated-entry redirects. Zero runtime exceptions. The first browser run correctly observed five invalid required fields; its old four-field expectation was updated. Desktop Light and phone Dark screenshots inspected with consistent spacing and no horizontal overflow.
- Changed PHP/JS syntax passed; strict design audit returned zero findings.

## Reproduction and limitations

Use isolated configuration only: php tests/run_regression.php; php tests/pickup_http_acceptance_test.php. For browser checks create a new private fixture with php tests/student_profile_browser_fixture.php --campus, then node tests/entry_browser_check.cjs <private-fixture.json> <external-output-directory>. Windows, PHP8.4, local MariaDB and Chromium emulation. No new dependencies or schema migration.

Physical devices, Safari, screen readers and actual400% zoom were not rerun. No claim of guaranteed absence of defects. Private credentials, raw logs and screenshots stay outside the maintained repository. kaaklat_pilot records, existing configuration and covers were not modified. Hosting remains deferred. No commit or push.
