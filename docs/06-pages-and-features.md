# Admin Panel — Pages & Features

Single role: **Admin**. No public registration; admin accounts are seeded
directly in the database (or created by a super-admin via CLI/seed script).

## 1. Login (`/login.php`)
- Username + password form.
- Session-based auth; failed attempts shown as generic "invalid credentials".

## 2. Dashboard (`/dashboard.php`)
- Cards: total registered users, taps today, granted today, denied today.
- Chart: taps over last 7 days (granted vs denied) — Chart.js line/bar chart.
- "Currently allowed" widget: which users' schedule windows are active right now.
- Recent activity feed: last 10 access log entries (live-ish, polling or manual refresh).

## 3. Manage Users (`/users/`)
- List view: table of users (name, ID number, RFID UID, role, status), search/filter.
- Create/Edit form: name, ID number, RFID UID (can be entered manually or
  "listen" for the next scan via the bridge to auto-fill it), role, status.
- Delete (with confirmation) — cascades to that user's schedules and access logs
  (or soft-delete by setting status = inactive, recommended over hard delete so
  historical reports stay intact).

## 4. Schedules (`/schedules/`)
- Per-user schedule manager: list of day/time windows for a selected user.
- Add window: choose day(s) of week (multi-select), start time, end time.
- Edit/remove individual windows; toggle a window active/inactive without
  deleting it.
- Validation: end time must be after start time; warn (not block) on overlaps.

## 5. Reports (`/reports/`)
- Filterable log table: date range, user, result (granted/denied), reason.
- Pagination for large log volumes.
- CSV export button (`/reports/export.php`) respecting the current filters.
- Optional: simple summary stats above the table (total taps, denial rate) for
  the selected filter range.

## 6. Navigation / Layout
- Persistent sidebar: Dashboard, Manage Users, Schedules, Reports, Logout.
- Bootstrap-based responsive layout; top bar shows logged-in admin name.

## Access guard
- Every page under `public/` (except `login.php`) calls an `Auth::requireAdmin()`
  guard at the top that redirects to login if no valid admin session exists.
