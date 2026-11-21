# About developer card - 2026-11-21

Refined the approved About developer card: portrait/name/role/course grouped in a responsive identity row, stacked on narrow phones; concise existing-project contribution; labelled44px Email Emmanuel and View project on GitHub links; original email address remains visible. No new qualifications, research credits or specific responsibilities were invented. All school, demonstration and six-step borrowing content outside the card remains byte-identical. Existing photo, themes, focus, no-script and motion behavior retained. Only views/about.php, its scoped workspace.css styles and the workspace CSS cache version in views/layout.php change at runtime.

Verified with36 regression scripts in an isolated fictional database; changed PHP lint; strict design audit with zero findings;30 Chromium layouts across guest/active/pending/librarian/administrator, Light/Dark and320/768/1440px. Tests cover portrait row/phone stack, wrapping,44px native links, true destinations, keyboard focus order, facts/borrowing preservation, reduced motion and no-JS. Zero runtime exceptions. Desktop and phone captures visually inspected. Backend/schema/operator and prior motion sources unchanged. Pilot preservation and final transfer are recorded in verification/DEVELOPER_CARD_2026-11-21.md. Physical devices, Safari, screen-reader and actual400% zoom remain manual. No commit/push.

## Verification evidence

The extended tests/design_review_test.php first failed on the missing grouped developer identity, then passed after implementation. The existing fact checks still pass; new checks cover the role, labelled contact/project actions, exact maintained GitHub destination and truthful existing-project description. Exact source comparison confirms all About content outside the developer section is byte-identical. Hashes for backend, schema, migrations, operator tools and existing motion CSS/JS are unchanged from the current baseline.

Executed in the isolated copy: php tests/run_regression.php (36 PASS); PHP lint for views/about.php and views/layout.php; node tests/developer_card_browser_check.cjs <new fictional fixture.json> <external output> (30 layouts,zero runtime exceptions); premium audit_project.py . --mode strict (zero findings). Browser fixture: newly created fictional kaaklat_acceptance database,never pilot identities. JSON results and tested source hashes are in developer-card-2026-11-21. Raw screenshots, fixtures, credentials, source backup and plan remain in the external task workspace developer-card-work.

No full HTTP mutation/performance/concurrency sweep was repeated for this static card: no controller, validation, query, POST, transaction or JavaScript changed. Link destinations were verified without sending email or posting anything externally. Windows/PHP8.4.14/local MariaDB/headless Chromium were used. Browser emulation does not prove physical Safari/device/screen-reader or actual400% zoom acceptance; these remain manual. No database-code change, dependency, commit/push or hosting action.

## Final preservation

Final source transfer and read-only pilot fingerprint results are appended after execution. The pre-change source copy includes all existing uncommitted motion work; baseline hashes are checked before replacing only the approved card/test/docs files.

## Final maintained-site outcome

Applied15 scoped static-view/style/test/document/evidence files after checking every existing baseline source hash, including all prior uncommitted motion work. All164 tested runtime/test source hashes match the maintained application. The normal About route returns200 with the new GitHub action and refreshed workspace CSS; normal local health reports ready. Maintained About fact checks, changed PHP lint and Git whitespace checks pass. No commit or push.

The final pilot fingerprint differs from the initial read-only baseline because live activity occurred during verification. Only audit_events,loans and password_resets changed: two audit events were added (loan.returned and account.reset_issued), with corresponding live return and staff recovery POST requests in the normal port8000 server log. Tests used the separate newly created fictional databases/ports. No pilot test accounts were inserted, no fixture/source transfer writes to the pilot, and no records were restored, deleted or overwritten. All other table fingerprints, private configuration and cover inventory match. The live changes were preserved. This report therefore does not claim a byte-identical database snapshot across an active user session.
