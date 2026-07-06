# SDASFC — Smart Door Automation System for CICS

## 1. Project Title
**Smart Door Automation System for CICS (SDASFC)**

## 2. Summary
A web-based admin panel connected to an Arduino + RFID reader installed at the CICS
department door. Users (students/faculty/staff) tap their RFID card on the sensor to
request entry. The system only unlocks the door if the user has an active schedule
that covers the current day/time (e.g., allowed Mon–Fri 7:00–9:00 AM). Taps outside
the assigned schedule are logged as denied and the door stays locked.

## 3. Core Actors
- **Admin** — the only account type with panel access. Manages users, schedules,
  views dashboard analytics, and generates reports.
- **RFID User** — students/faculty/staff whose card UID is registered in the system.
  They do not log into the panel; they only interact via the physical RFID sensor.
- **Arduino Device** — reads RFID UID, sends it to the backend, receives an
  accept/deny response, and actuates the door lock (relay/solenoid/servo) accordingly.

## 4. Key Features (from requirements)
1. **Admin-only authentication** — single role, no public/user-facing accounts.
2. **Dashboard** — at-a-glance stats: today's taps, granted vs denied, active users,
   currently "in schedule" users, recent activity feed.
3. **Manage Users** — CRUD for RFID users (name, ID/section, RFID UID, status).
4. **Schedules** — assign one or more allowed day/time windows per user
   (e.g., Mon–Fri 7–9 AM). Supports multiple windows per user for flexibility.
5. **Access Control Logic** — on each tap, backend checks: (a) is UID registered,
   (b) is user active, (c) does current day/time fall inside any of the user's
   schedule windows. Only if all pass → door opens; otherwise access denied and logged.
6. **Reports / Logs** — historical log of every tap: user, time, result
   (granted/denied), reason if denied (unknown UID, inactive user, outside schedule).
   Filterable/exportable by date range, user, status.
7. **Arduino Integration** — Arduino reads RFID tag, sends UID to backend via a
   serial-to-web bridge script running on the host PC, receives grant/deny response,
   drives the door lock hardware.

## 5. Tech Stack (decided)
- **Backend:** PHP (native or lightweight structure), runs under XAMPP/Apache
- **Database:** MySQL (via phpMyAdmin/XAMPP)
- **Frontend:** Bootstrap 5 + vanilla JS (or jQuery) for the admin panel UI
- **Hardware bridge:** Arduino Uno/Nano + RFID-RC522 module, connected via USB
  Serial to a PC; a small bridge script (PHP CLI or Python) reads the serial port
  and forwards RFID tap events to the backend over HTTP (local API endpoint)
- **Charts (dashboard):** Chart.js (lightweight, works well with Bootstrap)

## 6. Out of Scope (for this draft)
- Multi-door / multi-room support (single door assumed for v1)
- Camera/photo capture on tap
- Mobile app for admin
- SMS/email notifications (can be a future enhancement)

## 7. Documents in this folder
- `01-overview.md` — this file
- `02-project-structure.md` — folder/file layout for the PHP project
- `03-database-schema.md` — tables, columns, relationships
- `04-access-control-flow.md` — how a tap is validated against schedules
- `05-arduino-integration.md` — hardware wiring + serial bridge design
- `06-pages-and-features.md` — admin panel pages/screens breakdown
- `07-development-plan.md` — phased build plan / milestones
