# Progress Checklist

Tracks build status against `07-development-plan.md`. Update this file as
phases complete so the project can be picked up later without re-reading
the whole history.

## Phase 0 — Setup
- [x] Project structure under `htdocs`
- [x] `database/schema.sql` created and imported
- [x] `database/seed.sql` seeds one admin (`admin` / `admin123`)
- [x] `app/.htaccess` blocks direct web access to `app/`

## Phase 1 — Admin Auth & Shell
- [x] `login.php` / `logout.php` + `Auth` session helper
- [x] Shared layout (sidebar/topbar) — `public/partials/`
- [x] Protected dashboard (empty widgets, wired in Phase 5)
- [x] Show/hide password toggle
- [x] Forgot password — 5 fixed security questions, user must **pick**
      the correct question from a dropdown (does not reveal it),
      then answer it, then set a new password
- [x] Manage Profile — update full name, change password, update
      security question/answer
- [x] Login lockout — 3 failed attempts disables the Log In button
      with a 30s countdown (enforced server-side, not just UI)

## Phase 2 — Manage Users
- [x] `users` CRUD — `public/users/index.php`, `create.php`, `edit.php`
- [x] Manual RFID UID entry field (no hardware needed for this phase)
- [x] Duplicate ID number / RFID UID validation
- [x] Activate/Deactivate + Delete actions
- Files: `app/models/User.php`, `app/controllers/UserController.php`

## Phase 3 — Schedules *(removed)*
- [x] Built, then removed from the system. The `schedules` table, the
      `public/schedules/` pages, `app/models/Schedule.php`, and
      `app/controllers/ScheduleController.php` are all gone, and the RFID tap
      flow no longer checks a time window.
- Phase number retained so it still matches `07-development-plan.md`.

## Phase 4 — Access Control API (software-only test)
- [x] Built `public/api/rfid_scan.php` (moved from the `app/api/` path
      in the docs since `app/` is blocked from direct web access by
      `.htaccess` — same pattern as every other page living under `public/`)
- [x] Tested with manual POST (curl) simulating an RFID tap — JSON body
      `{"rfid_uid": "..."}` and form-encoded body both supported
- [x] Confirmed grant/deny logic: unknown UID, inactive user, and granted
      all logged correctly to `access_logs`
- Files: `app/models/AccessLog.php`, `app/controllers/AccessController.php`,
  `public/api/rfid_scan.php`

## Phase 5 — Reports/Logs
- [x] Log table with filters (date range, result, user) —
      `public/reports/index.php`
- [x] CSV export honoring the same filters — `public/reports/export.php`
- [x] Dashboard widgets (registered users, taps/granted/denied today,
      recent activity table) wired to real `access_logs`/`users` data
- Files: `AccessLog::filtered()`, `AccessLog::todayStats()`,
  `User::count()` added to existing models

## Phase 6 — Hardware Integration
- [x] Wire up RC522 + DS3231 + DFPlayer + Relay + IR Exit Sensor per `arduino/wiring_diagram.html`
- [x] Created `arduino/sdasfc_door_lock.ino` with RC522, DFPlayer Mini, DS3231 RTC, Relay, and Exit Button logic
- [x] Built `hardware/bridge/serial_bridge.py`, `serial_bridge.php`, and `serial_bridge.ps1`
- [x] Created `docs/10-system-operation-and-startup-guide.md` (complete operation & startup manual)

## Phase 7 — End-to-End Testing
- [x] Web API backend verification: registered card (granted) vs unknown card (denied)
- [x] Hardware Serial Bridge connection verification on COM9 (115200 baud)
- [x] Physical tap testing with live door lock assembly (RFID tap, voice prompt, 6s relay hold, and relock)
- [x] Inactive user denial and real-time database access control verified
- [x] Optical No-Touch IR Exit sensor wave-to-exit verified

## Phase 8 — Polish
- [x] Interactive wiring diagram with non-overlapping color-coded traces (`arduino/wiring_diagram.html`)
- [x] Native 1-click Windows batch launcher (`start_bridge.bat`)
- [x] Comprehensive documentation in `docs/10-system-operation-and-startup-guide.md`
- [x] Automated RFID card scan detection feature in web portal ("Fetch Last Scanned Card")

---

**Current test credentials** (reset to these defaults after each test round):
- Username: `admin`
- Password: `admin123`
- Security question: "What is your mother's maiden name?" → Answer: `smith`

**Operation Guide:** See `docs/10-system-operation-and-startup-guide.md`.

