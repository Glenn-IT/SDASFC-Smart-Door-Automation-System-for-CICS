# Version Control — SDASFC Presentation Rollout

This project uses a week-by-week versioned rollout for presentations. Each version unlocks
exactly one page or feature on top of the previous version. Pages not yet unlocked show an
"Under Construction" placeholder instead of their real content.

## Rollout Schedule

| Version | Feature | Page(s) Unlocked | Still Gated After This Version |
|---------|---------|-------------------|----------------------------------|
| v1.00 | Login / Forgot / Reset Password | `login.php`, `forgot-password.php`, `reset-password.php` | Dashboard, Profile, Users, Schedules, Reports, RFID API |
| v1.01 | Admin: Dashboard | `dashboard.php` | Profile, Users, Schedules, Reports, RFID API |
| v1.02 | Admin: Profile | `profile.php` | Users, Schedules, Reports, RFID API |
| v1.03 | Admin: Manage Users — View List | `users/index.php` | Users (create/edit), Schedules, Reports, RFID API |
| v1.04 | Admin: Manage Users — Create | `users/create.php` | Users (edit), Schedules, Reports, RFID API |
| v1.05 | Admin: Manage Users — Edit | `users/edit.php` | Schedules, Reports, RFID API |
| v1.06 | Admin: Schedules — Select User | `schedules/index.php` | Schedules (manage windows), Reports, RFID API |
| v1.07 | Admin: Schedules — Manage Access Windows | `schedules/user.php` | Reports, RFID API |
| v1.08 | Admin: Reports — View Access Logs | `reports/index.php` | Reports (export), RFID API |
| v1.09 | Admin: Reports — CSV Export | `reports/export.php` | RFID API |
| v1.10 | Admin: RFID Scan / Hardware Integration (Full System) | `api/rfid_scan.php` | — (full system unlocked) |

This system has a single authenticated role (Admin). The "users" managed in the Users and
Schedules pages are RFID cardholders (door-access subjects), not separate logins.

## Under Construction Strategy

- `components/version.php` defines the single source of truth: `CURRENT_VERSION`, e.g. `'v1.05'`.
- `components/under-construction.php` requires `version.php`, renders a full-page card
  (icon, version badge, title, description, Go Back button), and calls `exit`.
- Every gated HTML page has `require_once __DIR__ . '/../(../)components/under-construction.php';`
  as the first statement in its `<?php` block, before any of its own `require_once` calls. Since
  the component exits, the rest of the page never executes while gated.
- `public/api/rfid_scan.php` is a JSON endpoint hit by the Arduino/RFID bridge, not a browser
  page — wrapping it in the HTML under-construction card would break the hardware integration's
  JSON contract. Instead it requires `components/version.php` directly and, while
  `CURRENT_VERSION !== 'v1.10'`, returns `{"access":"denied","reason":"feature_not_yet_available"}`
  with HTTP 503 instead of running the real access-control logic.
- To unlock a version: remove the gate line from the page(s) for that version and bump
  `CURRENT_VERSION` in `components/version.php` (or the version check in `rfid_scan.php`).

## Git Commands Per Version

```bash
# Stage and commit the unlocked page(s) + updated version file
git add <unlocked-page.php> components/version.php
git commit -m "feat: implement vX.XX - unlock [Feature Name]"

# Tag and push
git tag vX.XX
git push origin main
git push origin vX.XX
```

## How Git Tags Work

A Git tag is a permanent, named pointer to the exact commit that was live at that point in the
rollout. Unlike branches, tags don't move as new commits are added — `git tag v1.03` always
refers to the same snapshot of the repo, so you can check out or reference "what the system
looked like during the v1.03 presentation" at any time in the future with
`git checkout v1.03`.

## GitHub Release Tags

| Version | Tag | Commit Hash |
|---------|-----|--------------|
| v1.00 | v1.00 | _(fill after push)_ |
| v1.01 | v1.01 | _(fill after push)_ |
| v1.02 | v1.02 | _(fill after push)_ |
| v1.03 | v1.03 | _(fill after push)_ |
| v1.04 | v1.04 | _(fill after push)_ |
| v1.05 | v1.05 | _(fill after push)_ |
| v1.06 | v1.06 | _(fill after push)_ |
| v1.07 | v1.07 | _(fill after push)_ |
| v1.08 | v1.08 | _(fill after push)_ |
| v1.09 | v1.09 | _(fill after push)_ |
| v1.10 | v1.10 | _(fill after push)_ |

Fill this table in with:

```bash
git tag | sort | xargs -I{} git log -1 --format="{} %H" {}
```

## When a Prof or Client Requests Changes After a Presentation

If feedback comes in on a page that's already been presented (already unlocked at an earlier
tag), fix it on `main` and re-point that version's tag at the new commit so the tag still
represents "what was shown," updated with the fix:

```bash
# Fix on main first
git checkout main
git add .
git commit -m "feat: update [page] per feedback"
git push origin main

# Delete old tag and re-create it pointing to the new commit
git tag -d vX.XX
git push origin :refs/tags/vX.XX
git tag vX.XX
git push origin vX.XX
```

Do not edit a version's gate/scope retroactively — only fix the content of pages already
unlocked at that version. If the feedback requires unlocking a page earlier than planned,
treat it as a deliberate re-ordering of the rollout schedule and update this file accordingly.
