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

## Phase 3 — Schedules
- [x] CRUD pages for `schedules`, scoped per user
      (`public/schedules/index.php` lists users, `user.php?id=` manages
      that user's access windows)
- [x] Form validation — start time < end time, day-of-week required,
      and overlapping windows for the same user/day are rejected
- [x] Activate/Deactivate + Delete per schedule window
- Files: `app/models/Schedule.php`, `app/controllers/ScheduleController.php`

## Phase 4 — Access Control API (software-only test)
- [x] Built `public/api/rfid_scan.php` (moved from the `app/api/` path
      in the docs since `app/` is blocked from direct web access by
      `.htaccess` — same pattern as every other page living under `public/`)
- [x] Tested with manual POST (curl) simulating an RFID tap — JSON body
      `{"rfid_uid": "..."}` and form-encoded body both supported
- [x] Confirmed grant/deny logic: unknown UID, inactive user, outside
      schedule, and granted-within-schedule all logged correctly to
      `access_logs`
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
- [ ] Wire up RC522 + Arduino per `05-arduino-integration.md`
- [ ] Flash `rfid_door_lock.ino`, verify UID reads over Serial Monitor
- [ ] Build/run the USB-Serial bridge script, confirm it calls the API
      and relays grant/deny back to the Arduino relay

## Phase 7 — End-to-End Testing
- [ ] Full flow: create user + schedule → tap inside window (granted)
      → tap outside window (denied) → tap unknown card (denied)
- [ ] Inactive-user denial
- [ ] Edge cases from `04-access-control-flow.md`

## Phase 8 — Polish
- [ ] UI/styling consistency pass, empty states, validation messages
- [ ] Responsive check (tablet/mobile widths)
- [ ] `README.md` covering setup (DB import, Arduino wiring, bridge script)

## Deferred / Future
- [ ] WiFi/ESP32 integration (see `08-future-wifi-integration.md`) —
      documented only, not scheduled into the active phases above

---

**Current test credentials** (reset to these defaults after each test round):
- Username: `admin`
- Password: `admin123`
- Security question: "What is your mother's maiden name?" → Answer: `smith`

**Next step:** Start Phase 6 — Hardware Integration (requires the
Arduino + RC522 hardware on hand).
