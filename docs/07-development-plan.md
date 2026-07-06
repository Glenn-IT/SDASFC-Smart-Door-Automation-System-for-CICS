# Development Plan

## Phase 0 — Setup
- Initialize project structure under `htdocs` (per `02-project-structure.md`).
- Create MySQL database + run `database/schema.sql`.
- Seed one admin account (`database/seed.sql`) for first login.
- Set up `.htaccess` / access guard so `app/` isn't directly web-reachable.

## Phase 1 — Admin Auth & Shell
- Build `login.php` / `logout.php` + `Auth` session helper.
- Build shared layout (sidebar/topbar) used by all pages.
- Empty dashboard page reachable only when logged in.

## Phase 2 — Manage Users
- CRUD pages for `users` table.
- Manual RFID UID entry field (hardware not required yet to test this phase).

## Phase 3 — Schedules
- CRUD pages for `schedules`, scoped per user.
- Form validation (start < end time, day selection).

## Phase 4 — Access Control API (software-only test)
- Build `app/api/rfid_scan.php` implementing the flow in
  `04-access-control-flow.md`.
- Test with a simple manual POST (Postman/curl) simulating an RFID tap —
  no hardware needed yet, just confirm grant/deny logic against seeded
  users/schedules.

## Phase 5 — Reports/Logs
- Build the log table with filters + CSV export.
- Populate dashboard's recent activity + chart using real `access_logs` data.

## Phase 6 — Hardware Integration
- Wire up RC522 + Arduino per `05-arduino-integration.md`.
- Flash `rfid_door_lock.ino`; verify UID reads over Serial Monitor.
- Build/run the bridge script; confirm it correctly calls the API and relays
  grant/deny back to the Arduino, actuating the relay.

## Phase 7 — End-to-End Testing
- Test full flow: admin creates a user + schedule → tap card inside window
  (door opens) → tap outside window (denied, logged) → tap unknown card
  (denied, logged) → verify dashboard/report reflect these events correctly.
- Test inactive-user denial.
- Test edge cases noted in `04-access-control-flow.md`.

## Phase 8 — Polish
- UI cleanup (Bootstrap styling consistency), input validation messages,
  empty states, basic responsive check on tablet/mobile widths for the panel.
- Write a short `README.md` covering setup steps (DB import, Arduino wiring,
  running the bridge script).

## Suggested order rationale
Software-first (Phases 1–5) lets most of the admin panel be built and tested
without needing the Arduino hardware on hand, since the access-control logic
can be validated with simulated API calls. Hardware integration (Phase 6) is
deliberately last so hardware debugging doesn't block software progress.
