# Animated header theme toggle - 2026-11-21

## Change

Replaced the Settings appearance feature with the accepted named sun/moon header button and animation. The existing theme.js remains the sole browser-local appearance owner; views/portal_ui.php supplies shared button/icon markup and views/layout.php places controls in desktop/mobile/guest headers. app.css owns the 44px target, semantic colors and finite/reduced-motion transitions. Guest phone layout explicitly keeps the button on the first header row. Removed views/settings.php and obsolete radio styles/icon mapping. public/settings.php retains existing authentication/status/cache/method guards and redirects eligible GET/HEAD to dashboard; POST405 and stale/inactive access behavior remain.

The preference key is unchanged: kaaklat.appearance. Existing Light/Dark saved choices persist; legacy System/device preference follows OS until toggled. No account identity or credentials are stored. Header controls synchronize across tabs, keep correct next-action accessible names and report blocked persistence politely. Without JavaScript, device CSS appearance remains and controls stay hidden/disabled. Page titles/status and original navigation/forms/pagination remain.

## Fresh verification

- 36 regression scripts; 19 controller tests; 450 isolated workflow HTTP assertions, including exact compatibility redirect destination, current role links, denied POST and stale/inactive access.
- 129 PHP files linted; public and test JavaScript syntax checks passed.
- Focused header:25 guest/active/pending/librarian/administrator layouts at320/360/768/1100/1440px;50 animation toggles;44px targets, accessible names, keyboard Enter, rapid clicks, storage persistence, no-JS, reduced motion, legacy preference and blocked storage.
- Existing drawer:56 role/theme/viewport cases and26 native links. Routes dropped by four because Settings is no longer a menu destination.
- Entry:64 route/theme/width combinations, invalid form feedback, sign-ins and no-JS behavior.
- Broad themes:216 page/theme/width combinations with zero runtime exceptions and zero sampled text contrast failures. Keyboard/storage/guest/no-script checks passed. Eight combinations per role associated with Settings were removed from the former248-case matrix.
- Rendered desktop/phone authenticated and signed-out phone images inspected. Generated screenshots, fixture credentials, source snapshots and raw logs stay outside the repository under the task workspace theme-toggle-work folder.

Tests ran only against newly created fictional databases. The final source hash map records exactly the application/operator/test bytes exercised; no policy/service/schema changes were needed. The pilot fingerprint compares all12 tables, row contents/definitions, private config and every cover file before/after the increment. Database/account cleanup, resets and migrations are outside scope.

## Failed checkpoints and corrections

The controller test failed before implementation because no header control was enabled. It passed after the change. An initial regression invocation from the task folder failed the existing relative-backup-path check; running from the isolated source root passed. Browser startup/viewport/error-focus waits were synchronized with the loaded page. The old broad keyboard test initially sent an incomplete headless Enter event; diagnostic evidence showed focus state and a missing Enter text event. It now uses focus emulation and the same complete Enter representation as the shared protocol helper; dedicated and broad keyboard checks both passed. Visual review found the guest phone icon wrapping below navigation; the responsive guest header now places it on the brand row and affected checks were rerun. No circulation/database defect was found or changed.

## Limits

Chromium emulation is not physical-device/Safari/screen-reader testing or actual400% zoom. Contrast sampling is not accessibility certification. No finite suite guarantees absence of every bug. No concurrency rerun because transaction/locking/business code is byte-for-byte unchanged. Hosting remains deferred. The approved increment is implemented without committing or pushing.

## Maintained-pilot final check

Applied only the reviewed presentation/tests/current documentation. All application/operator/test file bytes match the isolated tested-source manifest. Normal start-local.ps1 was restarted; local health returned ready and login returned HTTP200 with the new versioned script and one guest header toggle. Fresh before/after fingerprints match exactly across all12 tables, definitions, row contents, private config and cover files. No test accounts or records were added to kaaklat_pilot. A fresh checksum-verified pre-change SQL/covers/config/source bundle is retained in the protected external task archive (theme-toggle-work/before-change-backup); the maintained folder did not retain generated fixture/browser artifacts. Final129 PHP lints, JavaScript syntax,19 controller checks and whitespace checks passed. No commit/push.
