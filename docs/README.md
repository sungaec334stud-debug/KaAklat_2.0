# Documentation guide

Start with [README](../README.md) for running the application. The project root keeps the requirements, schema explanation, test plan, current progress and changelog close to the source.

| Folder | Purpose |
| --- | --- |
| [operations](operations/WORKFLOW.md) | Local workflow, [backup and restore](operations/local-backup-restore.md), deferred deployment guidance |
| [design](design/DESIGN.md) | Design context, [UX contract](design/UX-CONTRACT.md), image provenance and accepted specifications |
| [research](research/RESEARCH_REVISION_PLAN.md) | Research revision guidance; preserve original research artifacts separately |
| [catalog](catalog/sample-books-sources.md) | Bibliographic provenance and historical catalog additions |
| [history](history/prompt.md) | Completed implementation plans, design brief and chronological work log |
| [verification](../verification/FINAL_CLEANUP_2026-11-15.md) | Current cleanup evidence; other verification reports remain in that folder |

Historical plans and commands describe their original phase; they are not instructions to seed or reset the current pilot. Optional sample installers, seed SQL and the obsolete landing stylesheet are preserved in Git history and the protected external cleanup archive.

Current private configuration, covers and pilot sessions stay in ignored `private/`. Generated tests, old backups and research output were archived outside the application. Keep that archive private: it contains database backups and configuration secrets. Git protects source history; it does not back up accounts or database records.
