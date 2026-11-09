# Approved navigation and appearance increment — 2026-11-10

User approved the standalone theme preview's navigation and subsequently requested dark mode as well. Implement inline in the existing plain PHP/CSS/minimal JS stack. No commit/push or hosting.

1. Preserve all current role links and fresh database identity checks in views/layout.php. Add Settings before About and Log out, separated visually from workflow links. Match the preview's compact navy sidebar, secondary university lockup and visible horizontally scrollable navigation on narrow screens. Do not introduce a hidden menu.
2. Add authenticated read-only settings.php for active staff and active/pending students. Reject mutation methods; no account selector or database preference. Use native radio cards: System default (initial), Light, Dark. Apply immediately and store only the appearance allowlist in localStorage. Explain browser-only scope and storage failure. With JS disabled, system CSS still works and controls clearly explain their limitation.
3. Extend app.css as the canonical semantic token owner. Separate dark headings from persistent navy artwork/button surfaces. Theme all shared controls, status, errors, catalog, history and landing, preserving meaningful text and contrast. Load the tiny shared theme initializer before CSS to avoid a light flash and apply system changes automatically.
4. First test theme selection/storage/system fallback and authenticated settings HTTP access/no mutation/stale-session restrictions. Run regression/HTTP/lint, then real browser page/role/theme/layout/keyboard checks using isolated fictional fixtures. Record exact evidence and remaining manual limits.

The preview itself is not production logic. Reuse live route/authorization/form owners; do not copy its fictional handlers/data or add circulation privileges. Preserve existing uncommitted performance work and all regular data.
