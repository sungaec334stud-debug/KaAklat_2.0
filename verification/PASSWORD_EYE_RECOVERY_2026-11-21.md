# Password eye and recovery guidance - 2026-11-21

## Implemented scope

One shared password_visibility_button_html() in views/auth_entry.php renders the 44px icon-only button and decorative open/closed eye. Existing entry.js changes only the associated input type and accessible label/title/pressed state; it no longer destroys icon markup by assigning Show/Hide text. entry.css provides a 240ms eyelid/iris transform and160ms opacity transition only when motion is permitted. Each field remains masked on initial/error render; controls are hidden without JavaScript and core forms remain usable. No new dependency, network call, password logging/storage or form submission behavior.

Login and registration use the shared eye; reset uses independent controls for the staff-issued recovery code, new password and confirmation. Reset labels omit UTF-8 byte jargon. Small linked hints recommend a short phrase of12-72 basic letters/numbers/spaces/symbols, identify complete private code paste,30-minute expiry and single use, and explain confirmation. Native collapsed help explains obtaining/renewing a code. The access-status reminder remains. Field errors sit outside the recovery input wrapper while retaining their IDs and forms.js associations. Server validation is unchanged, including the12-72 byte rule, NUL rejection, password confirmation, expiry/single use, session revocation and account-status boundaries. Reset controller POST logic is unchanged.

## Verification

-36 regression scripts passed on the new fictional default database.
-458 HTTP assertions passed, including valid recovery, mismatched confirmation, replay denial, CSRF and no reflected password/secret, plus new shared markup and guidance checks.
-17 eye-controller checks: immutable markup, independent state, correct accessible labels/pressed state, preserved values/selections and malformed-target fallback;19 existing theme-controller checks still pass.
-129 PHP lints and public/test JavaScript syntax passed.
-Focused eye browser:18 route/theme/viewport layouts at320/768/1440px and30 field cases;44px centered controls, finite opening/closing animation, Enter/Space, rapid clicks, unchanged input value, no accidental submits, independent masking, native recovery help, reduced motion, no-JS usable form and error non-echo. Zero browser exceptions.
-Existing entry browser:64 route/theme/width combinations, valid sign-ins and prior validation/no-script feedback retained.
-Existing header browser:25 role/width layouts and50 animated toggles passed, including the uncommitted moon/sun design and no Settings navigation.
-Rendered recovery screenshots inspected at desktop/phone in Light/Dark. Raw logs, images, fixtures and secrets remain outside the application in the task workspace password-eye-work folder. Sanitized reports/source hashes are alongside this report.

The new controller test failed before implementation because assigning Show/Hide text would destroy the icon structure; it passed after removing that mutation. No business/service defect was identified or changed. All tests ran in explicitly new fictional databases; no pilot fixture accounts or records were added.

## Preservation and limits

Current tracked and untracked source was copied before editing; the preceding uncommitted header change was preserved. No app/, schema, migrations, configuration, existing covers, sessions or operator tool changed. Before/after pilot fingerprints cover every table definition/row, all cover files and private configuration. A checksum-verified pre-change database/covers/config/source backup is preserved outside the project. Source transfer checks the current baseline first and verifies tested file hashes after copying; normal local startup/health are checked again.

No new full-theme or concurrency sweep: touched entry presentation has dedicated Light/Dark coverage and no transaction/locking code changed. Physical devices, Safari, screen readers and actual400% zoom for the new control remain manual. Finite tests do not prove absence of every undiscovered bug. Hosting remains deferred; no commit/push requested.

## Final maintained-project checks

The tested changes were applied after verifying every baseline source hash. All 161 tested source hashes match the maintained project; 129 maintained PHP files lint successfully. The 17 eye-controller and 19 existing theme-controller checks pass there, and Git diff whitespace checks pass with Windows CRLF handling. Guest GET checks on the running login, registration and reset pages return 200 with respectively one, one and three eye controls and the refreshed entry assets. The normal local health endpoint reports ready. Previous header implementation hashes are unchanged.

The final pilot fingerprint exactly matches the pre-change fingerprint for all database rows, definitions, existing covers and private configuration. The pre-change backup was checksum-verified again after moving it to the protected external task workspace password-eye-work/before-change-backup. No commit or push was performed.
