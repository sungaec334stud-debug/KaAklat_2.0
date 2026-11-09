# Local catalog performance verification

Measured 2026-11-10, Asia/Manila, against SRS NFR-06 and TEST_PLAN T-32.
The executed local HTTP measurements passed the proposed two-second p95 target.
Browser rendering, five interactive browser users, and hosted visible-page performance remain unverified.

## Executed method

Run from the project directory in PowerShell.

```powershell
& 'C:\php-8.4.14\php.exe' tests/catalog_performance.php
```

This command requires the local application configuration, PHP cURL/DOM/PDO MySQL extensions,
and permission to create a separately named local database. Each run creates a new
`kaaklat_perf_<random suffix>` database, applies schema.sql and migrations 001/002 only there,
and retains the database. It starts a dedicated PHP server on an unused IPv4 loopback port.
The router accepts an explicitly named scratch database and loopback requests only.
It closes the server when finished. Exit code 0 requires all measured responses to validate
and every scenario's total-response p95 to meet 2,000 ms.

The fixture has 100 fictional titles, 300 copies, 30 active students, two librarians,
and one administrator. Fifty titles have two authors. There are 183 requests and 123 loan
records covering pending, approved pickup, active, overdue, returned, expired uncollected,
and rejected cases. Three live allocations make title 100 unavailable.
The regular application database, private configuration, catalog data, and covers are not
written by this command. Passwords are generated per run, held in memory, and omitted from evidence.

Five distinct students sign in through the ordinary login page using CSRF tokens and separate
cookie engines. A catalog preflight checks each authenticated display name, and the runner checks
that all five sessions have distinct cookies without saving cookie values.

For each scenario the runner makes three validated warmups per participating session, followed by
30 measured rounds. Sequential rounds use one session. Concurrent rounds submit five handles
together through curl_multi, giving 150 samples per scenario. There is no artificial delay between rounds.
The five scenarios run in the order shown below, sequential first, then concurrent.
Overall, 900 samples and 90 warmups were recorded or validated respectively.
Sign-in, readiness probes, and identity preflights are outside the measured sample set.

Each measured response must be HTTP 200 and contain the expected catalog heading, exact visible
book IDs in order, filtered total, page number, and total page count. Redirects are not followed.
The raw CSV includes HTTP status, cURL error code, redirect count, response bytes, TTFB, total time,
and content validity. Failures remain in the sample distribution and cause the run to fail.
Nearest-rank p95 uses sorted sample number ceil(0.95 × n), with no outlier removal.
The harness checks percentile arithmetic and rejects synthetic login, redirect, and failed-transfer responses.

## Final results

Times below are milliseconds, including loopback HTTP transfer and development-server queueing.
Each sequential row has 30 samples. Each concurrent row has 150 samples from five independent sessions.

| Scenario | Sequential average | Sequential p95 | Sequential max | Five-session average | Five-session p95 | Five-session max |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Catalog, first page | 48.985 | 64.364 | 91.778 | 112.632 | 230.858 | 277.571 |
| Title search | 47.219 | 63.687 | 64.373 | 158.046 | 284.106 | 346.661 |
| Combined title/author/category/year/initial | 57.872 | 78.244 | 79.423 | 136.969 | 248.502 | 276.568 |
| Available-only, last page | 41.170 | 62.904 | 65.625 | 123.772 | 232.963 | 275.838 |
| Numbered pagination, page 5 | 44.191 | 62.710 | 68.777 | 125.856 | 232.014 | 304.453 |

All 900 samples passed content validation. There were zero HTTP failures, redirects, cURL errors,
or timeouts. The largest final p95 was 284.106 ms and the largest observed response was 346.661 ms.
Per-scenario TTFB p95 is also included in run.json.
All scenarios passed when evaluated using the more conservative complete HTML transfer time.

## Environment and query plans

- Windows 11 Home Single Language, version 10.0.26300, build 26300.
- Intel Core i5-1235U, 12 logical processors, 7.71 GiB system RAM.
- PHP 8.4.14, cURL 8.12.1, MariaDB 10.4.32, CLI OPcache disabled.
- One PHP built-in development-server process and the database on the same computer.
- Plain HTTP on 127.0.0.1, without an external network or proxy.
- Source commit 099667c, with additional benchmark files. Source SHA-256 hashes are recorded in run.json.
- Background applications, CPU power mode, cache residency, and unrelated machine activity were not controlled.

EXPLAIN was recorded for the canonical catalog selection, derived available-count query,
a representative combined-filter query, and the own-status predicate.
Copy lookups use ix_book_copies_book, loan lookups use ix_loans_copy_history or uq_loans_request,
and author relations use existing primary/order indexes. The combined query uses the existing year
and category indexes, with temporary sorting. The availability count scans the 100 book rows to
evaluate derived availability. This is expected at the measured scale. No confirmed performance
defect required an application-query, index, or schema change.

## Saved evidence and retained resources

Reviewable copies are in `verification/performance-2026-11-10/`.

- `samples.csv` contains all 900 measured responses.
- `run.json` contains results, dataset counts, software/hardware details, source hashes, and limitations.
- `query-plans.json` contains SQL and EXPLAIN rows.

The final scratch database is `kaaklat_perf_284bb29d6fad`.
Its original evidence and server log are in `tmp/performance-284bb29d6fad/`.
The initial successful run retained `kaaklat_perf_84c0ff040d6a` and
`tmp/performance-84c0ff040d6a/`. That run also passed 900 samples with no failures,
with its highest p95 at 350.892 ms.
Both servers were terminated by their runners. The final evidence records server_stopped=true.

No automated database cleanup is provided. Before eventual cleanup, review these exact scratch names,
confirm neither is selected in private application configuration, retain the evidence, and remove only
the approved scratch resources through the local database administrator.

## Verification and limits

The two benchmark PHP files passed lint. The existing regression suite was executed after measurements,
with its result recorded in TEST_PLAN.md. The 900 CSV rows were independently checked for HTTP 200,
zero cURL errors, and content validity.

This run measures the local HTML endpoint, including query work and session handling.
It does not load images, CSS, or JavaScript and does not measure visual rendering, LCP, or actual browser navigation.
Covers use the existing placeholder. Five HTTP sessions are an approximation of browser traffic.
Windows PHP's one-process development server serializes application work, so five outstanding requests
include queueing and do not demonstrate five parallel PHP workers or production capacity.
There is no throughput guarantee, endurance test, cold-start test, or real-data-scale claim.

T-32's hosted requirement of at least 20 visible navigations remains deferred, as does its five-browser
interaction check. No hosting, public traffic, deployment, or production benchmark was performed.
