# Behavior-preserving simplification

Implement the five audited candidates only: remove unused return display assignments; reuse catalog_like; share student summary items/overdue markup preserving exact page labels; consolidate display-only UTC/Manila formatting preserving each format; share low-level Chrome protocol plumbing across three browser tests without changing fixture isolation, ports, waits or cleanup. No CSS/design change, dependencies, schema, authorization or transaction edits.

- [x] Capture baseline presentation output and add meaningful helper boundary checks before implementation.
- [x] Apply each production simplification and compare baseline output/run affected tests.
- [x] Extract browser protocol helper and rerun all three existing browser matrices.
- [x] Run full isolated regression/HTTP checks, lint, static audit and pilot fingerprint comparison; document actual results and limits.

Keep all fixture writes in a newly named scratch DB and isolated config/source, never kaaklat_pilot. Do not commit/push. Usage tool unavailable. If any behavior changes, correct it and rerun affected checks; circulation writes/locks remain out of scope, requiring concurrency tests if unexpectedly touched.
