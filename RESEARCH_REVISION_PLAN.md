# KaAklat research revision plan

Scope addition, 2026-10-30: include primary category and first-publication year in catalog descriptions/ERD and title/author/category/year/initial filters in FR-03 evaluation. Migration 001 adds a category lookup and optional fields. This is an implemented demo feature subject to test evidence, not a completed participant evaluation or verified school policy.

Date: 2026-10-29 (Asia/Manila)
Status: Preliminary review; not a rewritten or adviser-approved paper.

## Sources and evidence boundaries

- Source: `C:\Users\63961\Desktop\Emman\SE1 System\SUNGA_CHAPTER1,2.pdf`, 16 PDF pages including front matter and references. All page text was extracted; PDF page 5 was also rendered and visually inspected to read Figure 1.
- Current requirements: the user's project brief and subsequent answers in this chat. Statements in the paper describe the earlier design and are not instructions to implement it.
- Page references below use printed page numbers; PDF pages are two greater, except front matter.
- No research participants, interviews, completed application tests, deployment, or measured improvements have been verified. Other studies' results are not KaAklat results.
- The cited publications have not yet undergone a complete source audit. This review checks internal consistency, not the truth of every literature claim.

## Confirmed direction from the user

KaAklat is initially a hosted demonstration of a school library system, with possible future school adoption. It will use PHP without an application framework. Students create accounts, browse availability, request books, and view their requests and loans. Librarians approve or reject requests, manage the catalog, and process circulation. A separate website administrator role is required.

The borrowing limit is five books. Students request a duration and explain why they need it; librarians decide the permitted duration. The user selected the proposed workflow: approval precedes physical pickup, the loan starts at librarian-recorded pickup, and only librarians record returns. Approved pickups plus active loans count toward the five-book limit. Pending-request limits and pickup expiry are still unresolved. The user selected login-required catalog access with librarian activation before students can borrow. Fines, ISBN entry and barcode scanning are explicitly excluded. No deployment or evaluation has yet occurred.

The presentation is around November 2026; an exact date was not supplied. The supplied PDF is the required formatting template. The user plans approximately 20 student participants and a couple of librarians; the exact librarian count, interview versus questionnaire procedure, and adviser approval remain unresolved. Well-known book titles will be used for testing/simulation. Catalog entry must accept title, author names, description, book cover and number of copies. Individual copy IDs can be generated internally without ISBN or scanning hardware.

## Follow-up decisions incorporated

The map below describes the original review questions; these subsequent answers resolve some of them:

- Required paper format: follow the supplied PDF's structure and visual style; do not ask for a new template unless an editable original is needed later.
- Catalog visibility: login required, replacing the earlier public-catalog proposal.
- Registration: students register; librarians activate borrowing access.
- Workflow: approved request -> physical pickup -> active loan -> librarian-confirmed return. Approved pickups and active loans count toward the maximum of five.
- Catalog: no ISBN field required; support title, multiple authors, brief description, book cover and a copy count at entry. Available count is derived from individual copies, not freely overwritten.
- Evaluation: planned 20 students and an unspecified small librarian group. No recruitment, responses or results have been evidenced. Interviews gather qualitative feedback; a rating questionnaire is a separate instrument if numerical acceptability scores are required.
- Deadline: approximately November 2026; exact date unknown.

## Section-by-section revision map

| Existing section | Proposed revision | Reason | Supporting evidence needed | Information still needed |
| --- | --- | --- | --- | --- |
| Title and front matter | Retain KaAklat and the web-based library title provisionally; update submission date only when supplied. | The project identity still fits. | School template and correct submission details. | Deadline, required title wording and formatting. |
| Background, pp. 1-2 | Explain the proposed demonstration and possible adoption; remove promised fines, reservations, and other excluded features. Describe expected benefits as expectations. | Page 2 promises fine computation, while p. 4 excludes it; the scope has also narrowed. | Verified literature and actual school-process evidence. | Has school requirements gathering already occurred? Any interview notes or approvals? |
| General objective, p. 2 | Describe developing and testing a hosted prototype for the school; retain user evaluation only if the research requires and actually conducts it. | A demonstration is not deployment into daily school operations. | Approved project objective and evaluation requirements. | Adviser instructions on evaluation and target school use. |
| Specific objective 1, p. 2 | Tie requirements analysis to the new SRS; replace the unsupported version reference `SRS v1.1` with the real approved document version. | No earlier SRS was supplied or found in the current workspace. | New requirement IDs and traceable school findings. | Whether the earlier SRS exists and should be consulted. |
| Specific objective 2, pp. 2-3 | Replace Laravel with the selected plain-PHP stack; include accounts, catalog/copies, borrowing requests, librarian decisions, lending, returns, and student status views. Remove FIFO queues and external reminders unless separately required. | Matches the current user request. | Approved SRS, stack decision, eventual implementation evidence. | Final request/pickup workflow and administrator permissions. |
| Specific objective 3, p. 3 | Separate functional testing, measured performance/security checks, and user feedback. State the ISO edition and justify any selected characteristics. | Questionnaire ratings alone cannot prove security, reliability, or performance. | Adviser-approved instrument, test plan, actual results, and evaluation procedure. | ISO edition, questionnaire, evaluators, consent process and assessment rubric. |
| Conceptual framework and Figure 1, p. 3 | Align the diagram and prose. Choose whether this is the research IPO (inputs, development/evaluation, outputs) or the application workflow and label it accurately. Remove fine rules, hold requests, FIFO, queue alerts, CSV/PDF reports, and notifications not in scope. | The prose describes a research process; the current figure mostly shows application operations. The figure is already marked 'To be changed'. | Approved workflow, study methodology, and final feature scope. | Adviser preference for conceptual-framework structure. |
| Scope, pp. 3-4 | Replace six broad modules with the smallest approved feature set and three roles. Include a five-book cap, requested duration/reason, and librarian-assigned terms. Separate book titles from physical copies. Describe hosting as a demonstration environment. | Current scope includes several features the user no longer wants for the MVP. | Approved SRS and hosting feasibility evidence. | Public versus login-only catalog, account activation, pickup policy, demo scale. |
| Limitations, p. 4 | Keep physical-book focus and exclusions for fines, payments, barcode/QR/RFID and native apps. Propose excluding reservations, renewals, email/SMS/push, imports and advanced reports. Replace local-network-only wording with the selected hosted-demo access model. | Browser access to a hosted site differs from a local-only XAMPP deployment. | Verified hosting restrictions and final scope. | Reliability of presentation internet; any mandatory excluded feature. |
| Significance, p. 5 | Keep benefits conditional. Remove report-related promises if reports are deferred. Distinguish possible future institutional value from demonstrated outcomes. | No efficiency, accuracy or adoption result has been measured. | Verified literature, eventual observations and test results. | Whether faculty are borrowers in this MVP or future users. |
| Definition of terms, pp. 5-6 | Remove operational definitions of fine and reservation if excluded; add borrowing request, approval, physical copy, active loan, due date, and overdue. Define automation as automatic record/availability/rule handling with human librarian decisions. | Terms should match the actual system. | Approved business rules and terminology. | Whether pickup and approval are distinct stages. |
| Chapter 2: requirements analysis, pp. 7-8 | Retain relevant studies after source checking; connect them to three-role permissions and library workflows without claiming school interviews were performed. | External studies support rationale, not local facts. | Original papers, accurate metadata and cited passages. | Local requirements-gathering evidence, if any. |
| Chapter 2: web-system development, p. 8 | Keep Laravel studies as comparisons when relevant; explain why their workflow findings can inform a plain-PHP prototype. Do not pretend these studies used the new stack. | Changing KaAklat's stack does not invalidate all framework-based literature. | Original publications and verified feature/technology claims. | Adviser expectations for literature coverage. |
| Chapter 2: testing, pp. 8-9 | Preserve other studies' reported results only after verification; separate them explicitly from planned KaAklat tests. Avoid equating user acceptability with technical quality certification. | Published response times and pass rates cannot be transferred to this project. | Test methods in the source studies, approved evaluation plan. | Required quality model and scoring method. |
| Synthesis, pp. 9-10 | Remove claims that KaAklat will address all listed gaps, such as reservations, reminders, scanning and digital access. Replace 'stack close to KaAklat' where it implies Laravel use. Explain the specific small workflow the prototype addresses. | Existing synthesis promises features beyond the revised MVP. | Verified literature and approved project scope. | Final agreed research contribution. |
| References, pp. 11-14 | Verify each cited work and numerical claim, reconcile duplicate entries, and format one reference list if the school style calls for it. | The list currently includes both general references and a separate objective-organized RRL list with repeated works. | Publisher/DOI originals, school citation style. | Required citation style and whether the objective grouping is intentional. |

## ISO edition check

On 2026-10-29, the official [ISO/IEC 25010:2023 page](https://www.iso.org/standard/78176.html) confirmed a nine-characteristic product quality model. The draft cites this edition while naming a selected five-characteristic evaluation and discussing older studies using eight characteristics. This does not automatically make the older studies wrong. The revised methodology must distinguish editions and explain its chosen subset. Full instrument alignment remains unresolved; no certification is claimed and no purchase is required for this preliminary review.

## Proposed automation boundary

Software will check the five-book rule, derive availability from copy/loan records, calculate due dates from librarian-approved terms, identify overdue active loans, and save request/loan history. Librarians still decide whether to approve and confirm physical handover and return. Automatic email, background scheduled jobs and automatic approval are not proposed MVP requirements.

## Safe revision sequence

1. Obtain adviser requirements, deadline and unresolved workflow answers.
2. Select the stack/host and approve the small MVP.
3. Create SRS and database design with stable requirement IDs.
4. Revise Chapter 1 in planned/proposed language; keep the original PDF intact.
5. Audit Chapter 2 citations and revise relevance/synthesis without inventing results.
6. Add implementation and evaluation statements only when recorded evidence supports them.
7. Cross-check the paper, diagrams, schema, tests and actual site before presentation.

No chapter has been rewritten by this initial review.

## Delivered revision — 2026-10-31

Both chapters have been rewritten in output/pdf/SUNGA_CHAPTER1_2_REVISED_KaAklat.pdf, retaining the original contents and navigation. See its companion REVISION_NOTES.md for source corrections, verification and adviser decisions still needed. Original PDF preserved. This supersedes earlier planned-only wording about the chapter rewrite; formal evaluation remains planned.
