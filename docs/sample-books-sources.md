# Real sample catalog — 2026-10-30

The 20 curated titles and author names in `sample_books.php` were checked against their Project Gutenberg records: https://www.gutenberg.org/ebooks/{the first value in each row}. Each row identifies its own source. Descriptions are original concise plot summaries. Title forms are shortened for display where appropriate. No ISBN, publisher, publication year or specific print-edition claim is added.

Covers come from `https://www.gutenberg.org/cache/epub/{id}/pg{id}.cover.medium.jpg`, downloaded locally, decoded and re-encoded as JPEG. These are the covers supplied for the referenced Gutenberg editions; some may be generated ebook covers rather than historical first-edition artwork. Source/permissions reference: https://www.gutenberg.org/policy/permission.html. Do not treat all internet covers as freely reusable or these images as proof of ownership of any physical edition.

`bin/prepare_sample_covers.php` prepares private images. Windows curl was used for downloads because local PHP cURL could not establish the connection; certificate verification was not disabled. Covers are in ignored `private/covers/`, served only by authenticated `/cover.php?id=...`. Include private covers in a future deployment/backup package; they are not pushed by Git.

`bin/install_sample_books.php` is restricted to local `kaaklat_dev`. It saves a private JSON backup of books/authors/links/copies, updates sample IDs 1–20, replaces their author links, and archives remaining known fictional titles only when no pending request or unresolved allocation exists. It keeps copy IDs and circulation history. Existing simulated history now displays the replacement titles. The original fictional `seed.sql` remains unchanged for isolated testing; do not reimport it into the used demo database.

Verification: `php verification/real_books_check.php`; catalog integration fixtures remain fictional and temporary. Private JSON backup is a targeted catalog snapshot, not a full database restore rehearsal.
