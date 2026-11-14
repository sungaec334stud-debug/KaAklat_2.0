# Local catalog performance refresh — 14 November 2026

SRS NFR-06 / TEST_PLAN T-32 local HTTP target: **PASS** for all14 scenarios. **2,520 measured samples**,252 validated warmups,30 measured rounds per scenario/mode. Maximum scenario p95 **303.394ms**, below2,000ms. Largest observed response **352.383ms**. Zero invalid responses, HTTP errors, redirect responses, transport errors or timeouts. All failures would remain in timings and fail the target; no outliers removed.

## Reproduce

From the maintained project in PowerShell, with XAMPP MySQL running:

```powershell
& 'C:\php-8.4.14\php.exe' tests/catalog_performance.php
```

The runner creates a NEW kaaklat_perf_<12hex> database; imports schema.sql plus migrations001/002 only there; creates fictional fixtures; starts a separate loopback PHP server on an unused port and closes it at completion. It never imports into kaaklat_pilot, modifies its configuration or resets its accounts. No new dependency, application query, schema, index or circulation behavior was introduced. Ordinary pilot server stayed on8000. Its health returned HTTP200 ready after testing.

Existing runner reused and extended:14 scenarios (normal A–Z, title/author keyword searches, Z–A, newest/oldest year sorts, null-last year ordering, legacy exact-year combined filters, inclusive range, every current filter combined on descending page2, both availability states, ordinary pagination, and empty results). One fictional title has unknown year, title100 has all3 copies allocated. Dataset remains100 titles/300 copies/30 active students/2 librarians/1 administrator,183 requests and123 loans. Fifty titles have a second author; request/loan fixtures include pending, live pickup, active/overdue, returned, expired-uncollected and rejected history. No cover files are created by this runner: HTML uses the existing no-cover placeholder.

Five students sign in through ordinary CSRF-protected login using independent cookie engines. Their cookie fingerprints must differ; raw cookies/passwords are not saved. Every measured and warmup response checks the expected signed-in fictional identity, exact visible book IDs/order, filtered total and page summary, HTTP200 and real catalog heading. Invalid synthetic login/redirect/failure pages, wrong identity and wrong book IDs fail harness self-checks. The new positive HTML self-check initially lacked a UTF-8 meta declaration, causing its middot summary comparison to fail before any benchmark database was created; corrected the synthetic document to match the real page encoding. This was a harness-fixture correction, not an application defect; the measured run below completed after correction.

Each scenario receives3 sequential warmups per participating session before30 measured rounds. Sequential mode has30 samples/scenario; concurrent mode schedules5 independent authenticated cURL handles simultaneously each round, yielding150 samples/scenario. Modes run sequential first, then concurrent; scenario order is in run.json.252 warmups are validated separately and excluded from measured distributions. Readiness, login and identity preflights are also excluded. Total:420 sequential +2,100 concurrent samples. Single-process Windows development-server queueing is included in concurrent latency.

Nearest-rank p95 is sorted sample ceil(.95*n); averages/maxima use every measured sample. Raw CSV and a separate Python recalculation agree with all reported statistics, sample counts and response statuses. The manifest records p95 TTFB as well as complete HTML transfer time, failures and environment. Redirect following is disabled: HTTP3xx status counts as a redirect failure even though cURL redirect-follow count remains0.

## Results (milliseconds)

Each single row n=30; each five-session row n=150. Failure column combines both modes.

| Scenario | Single average | Single p95 | Single max | Five-session average | Five-session p95 | Five-session max | Failures |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| Title A–Z / first page | 54.406 | 73.664 | 93.498 | 146.036 | 274.948 | 338.006 | 0 |
| Title search | 51.267 | 81.016 | 101.893 | 152.974 | 284.137 | 316.759 | 0 |
| Combined author/title/category/initial/exact year | 46.775 | 63.600 | 89.695 | 143.878 | 259.901 | 320.007 | 0 |
| Available only / last page | 49.744 | 89.509 | 92.605 | 165.533 | 303.394 | 352.383 | 0 |
| Unfiltered page 5 | 53.867 | 82.922 | 87.777 | 141.119 | 257.546 | 291.883 | 0 |
| Author keyword search | 60.001 | 90.432 | 95.091 | 145.594 | 277.025 | 309.755 | 0 |
| Title Z–A | 54.133 | 66.513 | 71.154 | 132.927 | 238.223 | 298.729 | 0 |
| Year newest first | 48.581 | 63.314 | 77.250 | 138.869 | 251.845 | 294.738 | 0 |
| Year oldest first | 52.937 | 76.604 | 77.574 | 139.890 | 265.012 | 287.538 | 0 |
| Unknown year last / final page | 44.405 | 63.377 | 80.910 | 147.956 | 268.976 | 308.534 | 0 |
| Inclusive year range | 48.423 | 73.886 | 79.317 | 155.886 | 283.781 | 309.824 | 0 |
| All current filters + Z–A / page 2 | 49.415 | 86.249 | 87.677 | 149.886 | 275.202 | 309.525 | 0 |
| Unavailable only | 55.199 | 89.751 | 91.887 | 148.432 | 262.621 | 307.072 | 0 |
| No matching search results | 52.729 | 90.399 | 90.474 | 152.888 | 288.706 | 294.728 | 0 |

## Environment and source

- Windows11 Home Single Language10.0.26300 build26300; Intel Core i5-1235U,12 logical processors,8,085,560KiB (~7.71GiB) visible system memory.
- PHP8.4.14 using C:/php-8.4.14/php.exe; cURL8.12.1; MariaDB10.4.32, local InnoDB.
- One PHP built-in server process; HTTP on127.0.0.1; CLI OPcache disabled; memory_limit128M. Session files isolated to this run's tmp directory, unique benchmark cookie namespace and loopback-only guarded router.
- Published source base3122525; test runner extension and documentation uncommitted. Application/public/view source hashes and runner/router hashes retained in run.json and matched after measurements. No application source changed.
- Normal background desktop activity and power state were not controlled. Regression/HTTP suites started only after timing measurements finished.

## Query plans / defect assessment

Eight representative EXPLAIN plans retained in query-plans.json: listing, available count, combined filters, newest-year sort, unavailable count, author keyword search, year range and own-request status. Listing uses ix_books_catalog; copy relations use ix_book_copies_book, loan relations ix_loans_copy_history or uq_loans_request, and author relations existing primary/order/author indexes. Combined category/year predicates use ix_books_year + ix_books_category index merge with temporary sorting. Own-state lookup is indexed by student/request and unique loan relation.

Availability totals and newest-year ordering scan the100 book rows; year sorting uses filesort. These read-time derived predicates and small scans did not violate the measured target. No confirmed application performance defect justifies a speculative query/index/schema change at this scale. This is not proof of efficiency for a substantially larger collection.

## Safety and follow-up validation

Read-only full-row fingerprints/schema inventories of all12 pilot tables, all private covers and private configuration matched before/after benchmark and follow-up suites. No test account/title/loan was added to the pilot. Independent source hashes matched after measurements. Benchmark server stopped and port51389 was no longer listening; ordinary pilot port8000 remained healthy. Benchmark server log had0 PHP fatal/warning/notice/catalog-failure lines.

After timing completed:36 regression scripts PASS;416 workflow HTTP assertions PASS in separately named fictional scratch. PHP lint PASS for runner/router; git diff --check PASS. No circulation mutation/lock/schema change; independent-process circulation races were not rerun. No new visual/keyboard/screen-reader acceptance claimed. Previous user-reported interface passes retain their own scope.

## Evidence and retained resources

- Benchmark: kaaklat_perf_f39f6a373949.
- Private scratch sessions/server log: tmp/performance-f39f6a373949/ (do not commit session files).
- Public-safe fictional metrics: verification/performance-2026-11-14/{run.json,samples.csv,query-plans.json,independent-checks.json}.
- Console log: tmp/performance-refresh-20261114.log.
- Follow-up HTTP scratch: kaaklat_acceptance_c0be82aea4d3; tmp/performance-refresh-http-20261114.log.
- Regression log: tmp/performance-refresh-regression-20261114.log.
- Pre/post pilot fingerprints remain in the private local task workspace; no actual pilot row content exported to Git.

Retained resources were not deleted. For eventual cleanup, verify exact database/directory names, confirm their servers are stopped and obtain explicit deletion approval. Never reset/import/drop kaaklat_pilot or remove unknown folders.

## Limits / remaining SRS evidence

This refresh measures complete authenticated catalog HTML responses over loopback, not five interactive browser people, image/GD processing, CSS/JS/cover transfer, layout/paint/Core Web Vitals, real LAN/Wi-Fi timing or hosted visible-page timing. cURL concurrently schedules five sessions, while the one Windows development server serializes PHP requests. Fixed100-title fixture, warm caches and30-round closed-loop runs do not establish production capacity, endurance or arrival-rate behavior. Prior five-browser functional checks were user-reported and were not remeasured here. Hosted20-navigation <=5s checks remain deferred with hosting. No accessibility certification, deployment or bug-free guarantee.

Next local work: participant evaluation and recorded findings; protected off-device pilot backup copy/daily RPO evidence remain outstanding. No commit or push performed.
