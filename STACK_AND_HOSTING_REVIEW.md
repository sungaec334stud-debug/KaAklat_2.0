# KaAklat stack and free-hosting review

Documentation checked: 2026-10-29 (Asia/Manila).
Status: Recommendation awaiting selection. No hosting account, deployed PHP test, database connection, or working application has been verified. The user's follow-up confirmed login-required catalog access, librarian-activated student borrowing, physical pickup/return tracking, and book-cover images without ISBN entry.

## Two small stack options

Both options use plain PHP and a MySQL-compatible relational database. A relational database stores connected records in tables, with keys linking students, requests, books and physical copies.

| Option | Components | Tradeoff |
| --- | --- | --- |
| A: Recommended | Plain PHP, PDO, MariaDB locally / assigned MySQL-compatible hosted database, HTML, custom CSS, minimal JavaScript. | Few dependencies and transparent code; we must build and check the responsive styles ourselves. |
| B: Optional UI toolkit | Same backend, with Bootstrap CSS and only necessary Bootstrap JavaScript. | Ready-made layout and form styles, but adds toolkit conventions and files. This is a frontend toolkit, not a PHP application framework. |

[Bootstrap documentation](https://getbootstrap.com/docs/5.3/getting-started/introduction/) supports use without build tooling. It is optional, not required. Neither proposal needs Laravel, Node.js, npm, Composer, paid APIs, or a frontend application framework for the MVP.

## What each part does

- PHP runs on the server: it checks permissions and input, handles requests, and renders HTML pages.
- PDO is PHP's database interface. Its MySQL driver connects to MariaDB/MySQL. Use [prepared statements](https://www.php.net/manual/en/pdo.prepared-statements.php) to pass user values separately from SQL; allowlist any dynamic sort-column names.
- HTML supplies semantic page structure: labels, headings, links, tables and forms.
- CSS adapts layouts to screen size using flexible widths, Grid/Flexbox and media queries. Responsive layout alone does not prove accessibility; keyboard access, labels, focus and contrast require checks.
- Minimal JavaScript improves interactions, while server-side validation and authorization remain mandatory.
- Git records development changes. It is not a backup of a hosted database.

## Local setup evidence and proposal

Read-only commands executed on 2026-10-29 found:

| Tool | Observed result |
| --- | --- |
| `php -v` | PHP 8.4.14, executable at `C:\php-8.4.14\php.exe` |
| `php -m` | PDO, pdo_mysql, session, mbstring and openssl present, among other extensions |
| `C:\xampp\php\php.exe -v` | PHP 8.2.12 |
| `C:\xampp\mysql\bin\mysql.exe --version` | MariaDB 10.4.32 client distribution; this does not identify a running database server |
| `git --version` | Git 2.47.1.windows.1 |

Proposed development setup: keep the project in this workspace, use the installed PHP 8.4 executable for the local development web server, and use XAMPP's database service. This avoids silently using XAMPP's different PHP version. No service was started or configuration changed during this review. Before schema execution, connect and inspect `SELECT VERSION()` and `SHOW ENGINES`; do the same on hosting. SQL must use the tested common subset, InnoDB tables, and a supported utf8mb4 collation rather than assume MySQL and MariaDB are identical.

After a `public` directory exists, the proposed local command is:

```powershell
& 'C:\php-8.4.14\php.exe' -S 127.0.0.1:8000 -t public
```

This is a future local-development command, not an executed test or a production hosting method. Using localhost alone also does not provide phone access; mobile testing will need browser emulation or the hosted site, followed by actual phone checks.

## Maintainable plain-PHP organization (proposal)

Use small entry-point pages under `public/`, shared security/database helpers under `app/`, HTML templates under `views/`, SQL under `database/`, and private configuration outside the publicly served directory. Keep business rules, such as approval and checkout, in reusable functions instead of copying them into many pages. Configuration credentials stay out of Git. The exact hosting layout must be checked early; do not assume the provider allows any chosen document root. If private configuration cannot be kept outside the web root, a tested access-denial arrangement must be established before deploying secrets.

## Hosting recommendation

InfinityFree is the preferred candidate for a small browser-based presentation. It advertises an ongoing free plan without a card or trial deadline. This is a current provider offering, not a guarantee that terms or availability will never change. AwardSpace was also checked but rejected for this login-based project because its free tier explicitly excludes SSL support. We should not transmit student/staff passwords through an HTTP-only deployment.

### InfinityFree: official-source findings

| Area | Documented finding | Project consequence / remaining verification |
| --- | --- | --- |
| Price and address | No credit card or trial; free subdomains. [Plan](https://www.infinityfree.com/) | No paid domain needed. Recheck terms at registration. |
| PHP/database | PHP 8.4; MySQL 8.0 / MariaDB 11.4; 400 databases. [Plan](https://www.infinityfree.com/) | Check the actual assigned runtime, PDO driver and server version before finalizing SQL. |
| HTTPS | Direct free subdomains receive system-managed certificates. `www` or deeper subdomains are not covered. HTTPS redirects still need configuration. [SSL documentation](https://forum.infinityfree.com/t/why-ssl-doesnt-work-on-www-subdomains-and-other-free-subdomain-ssl-issues/116209) | Use the direct free-subdomain address, enforce HTTPS, test secure session cookies. |
| Storage | 5 GB website space; bandwidth is advertised as unlimited. [Plan](https://www.infinityfree.com/) | This does not remove hit, CPU or database-load limits. |
| Database quota | Admin reports no fixed database storage/query quota, but shared database load is constrained. [Capacity](https://forum.infinityfree.com/t/the-capacity-of-the-free-account-mysql/93016), [load limits](https://forum.infinityfree.com/t/what-does-mysql-overloading-mean/49333) | Keep the demo small, queries indexed and results paginated. Do not promise unrestricted databases. |
| Transaction engine | InnoDB support was announced. [Admin announcement](https://forum.infinityfree.com/t/mysql-5-7-is-now-available-for-all-hosting-accounts/53835) | Actual foreign keys, rollback, row locking and concurrent checkout must be tested on the assigned database. |
| Request/CPU limits | 50,000 file hits per day; assets also count. Hitting limits can cause 24-hour suspension. CPU thresholds are not published. [Hits](https://forum.infinityfree.com/t/what-does-the-hits-limit-mean/49330), [CPU](https://forum.infinityfree.com/t/what-does-the-cpu-limit-mean/49328) | A visit is not one hit. Avoid unnecessary polling and large assets. |
| File count | Latest explicit Admin confirmation found states 80,000 inodes (Nov. 2025); older documentation lists lower limits. [Admin statement](https://forum.infinityfree.com/t/i-cannot-open-softaculous-apps-installer/116662) | An inode represents a filesystem entry. Check the actual account panel rather than relying on old figures. |
| Individual files | PHP/HTML/JS files: 1 MB each; `.htaccess`: 10 kB; most other files: 10 MB. [File limits](https://forum.infinityfree.com/t/file-size-and-upload-limits/49308) | Keep files small. Newly requested book covers need restricted staff-only image uploads, below provider limits. |
| Inactivity | Admin specifies at least five hits in 30 days; inactive accounts may be suspended and deleted approximately three weeks afterward. [Admin explanation](https://forum.infinityfree.com/t/my-simple-website-is-not-displaying-when-before-it-did/115254) | Check the site manually during development and before the presentation. Keep off-host backups. |
| Backups | No provider recovery backup. [Admin recovery explanation](https://forum.infinityfree.com/t/mysql-database-disappeared-wordpress-database-recovery/120319) | Export SQL and save site files outside hosting; test restoration locally. |
| Scheduled tasks | Cron is disabled; periodic automated-request substitutes are prohibited. [Cron policy](https://forum.infinityfree.com/t/cron-job-feature-is-now-disabled/80495) | Compute overdue status during ordinary page requests. Do not add cron workarounds or background reminder promises. |
| Email | No free email accounts; PHP mail is restricted. External SMTP is documented. [Plan comparison](https://www.infinityfree.com/premium/), [SMTP guide](https://forum.infinityfree.com/t/how-to-send-email-with-gmail-smtp/49239/) | No external email dependency in the proposed MVP. Use on-page status and a staff-assisted account recovery procedure with identity checks. |
| Deployment | FTP or online File Manager. [Plan](https://www.infinityfree.com/) | Plan manual file deployment and database import; do not assume SSH or Git deployment. Validate credential protection and use a secure upload method offered by the account. |
| Browser restrictions | Mandatory browser check requires JavaScript and cookies; ordinary API clients and webhooks are restricted. [Browser-security documentation](https://forum.infinityfree.com/t/browser-security-system-features-and-limitations/49353) | Server-rendered forms fit; test with real browsers. Do not claim the hosted site works with JavaScript disabled. |

### AwardSpace: screened out

The [free-plan page](https://www.awardspace.com/free-hosting/?iframe=yes) and [homepage](https://www.awardspace.com/) advertise ongoing free hosting without a credit card. However, the [SSL installation documentation](https://www.awardspace.com/kb/ssl-certificate-installation/) explicitly excludes free hosting from SSL support. It fails this project's secure-login requirement.

Additional findings, for completeness: [plan comparison](https://www.awardspace.com/web-hosting/shared-hosting/) and [terms](https://www.awardspace.com/terms-of-services/) describe 1 GB disk, 5 GB monthly transfer, one 30 MB database, 12,000 database queries/hour, 30 database connections, FTP/File Manager deployment, 15 MB upload limits, and restricted file types. The free-plan FAQ still lists PHP 5/7 and MySQL 5.7; current PHP 8 support for the free tier was not verified. InnoDB behavior and free-tier cron availability remain unverified. Its [FAQ](https://www.awardspace.com/frequently-asked-questions/) says free accounts do not receive restore backups. One email account is listed. Terms require an annual successful login for renewal and describe warnings/blocking after prolonged traffic inactivity. Numeric free-tier CPU and inode quotas were not verified. No attempt was made to work around the SSL restriction.

## Cover-image requirement added by the user

The user wants a book cover along with title, authors, description and number of copies. Proposed implementation: accept JPEG/PNG only from authorized catalog staff, check content using an image decoder, impose a 2 MB input cap and a pixel-dimension cap, then re-encode with generated filenames. Reject SVG, executable content and arbitrary files; prevent script execution in image storage. Confirm the hosted image-processing extension before committing to this implementation. Use a placeholder when no cover is supplied unless the user requires a cover for every book. No external cover API or ISBN lookup is needed.

## Suitability assessment

- **Demonstration:** reasonable candidate, subject to a real deployment test. Use fictional borrowers initially and maintain a local demonstration fallback.
- **School pilot:** consider only after school approval of data handling, backup ownership, downtime and support arrangements; current research does not establish suitability for real student records.
- **Daily school operations:** do not present this free-host setup as production readiness. Resource suspensions and missing provider backups require a separate operational assessment before adoption.

## Early verification checkpoint (not executed)

Before implementing full features, deploy a small temporary diagnostic page with no secrets or personal data exposed. Check direct-subdomain HTTPS, PHP version, PDO connection, sessions, database version, InnoDB, constraints, rollback and private-file access restrictions. Remove diagnostics afterward. Later execute two-librarian checkout and five-book-limit races with independent sessions/connections. Export and restore sample data locally. If critical constraints fail, stop and revise the host/design before adding more features.

The full DEPLOYMENT.md will be written after provider selection and updated with actual results; researched claims must remain separate from executed checks.
