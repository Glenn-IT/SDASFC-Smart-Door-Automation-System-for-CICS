# SDASFC — Smart Door Automation System for CICS

A web-based admin panel connected to an Arduino + RFID reader installed at the CICS
department door. Users tap their RFID card to request entry; the system unlocks the
door only if the card is registered, active, and the current day/time falls within
the user's assigned schedule. Every tap is logged as granted or denied.

## Features

- **Admin authentication** with forgot-password / security-question recovery
- **Dashboard** — today's taps, granted vs. denied counts, active users, recent activity
- **User management** — CRUD for RFID users (name, section, RFID UID, status)
- **Schedules** — assign one or more allowed day/time windows per user
- **Access control** — validates UID, user status, and schedule window on every tap
- **Reports** — filterable access log history with CSV export
- **RFID API endpoint** (`public/api/rfid_scan.php`) for the Arduino bridge to call

## Tech Stack

- **Backend:** PHP, runs under XAMPP/Apache
- **Database:** MySQL (via phpMyAdmin/XAMPP)
- **Frontend:** Bootstrap 5 + vanilla JS
- **Hardware:** Arduino Uno/Nano + RFID-RC522 module, bridged to the backend over
  serial/HTTP

## Project Structure

```
app/            Application logic (config, models, controllers, core helpers)
public/         Web root — pages, RFID API endpoint, static assets
database/       SQL schema and seed data
hardware/       Arduino sketch and serial bridge script
docs/           Planning and design documentation
```

See `docs/02-project-structure.md` for the full layout and rationale.

## Setup (XAMPP)

1. Clone/copy this project into your XAMPP `htdocs` folder so it's reachable at
   `http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public`.
2. Start Apache and MySQL via the XAMPP control panel.
3. Create the database and import the schema:
   ```
   mysql -u root -p -e "CREATE DATABASE sdasfc"
   mysql -u root -p sdasfc < database/schema.sql
   mysql -u root -p sdasfc < database/seed.sql
   ```
4. Check `app/config/config.php` for DB credentials and `BASE_URL` if your project
   folder name differs.
5. Visit `public/login.php` and sign in with the seeded admin credentials.

## Documentation

Detailed design docs live in `docs/`:

1. `01-overview.md` — project summary and requirements
2. `02-project-structure.md` — folder/file layout
3. `03-database-schema.md` — tables and relationships
4. `04-access-control-flow.md` — how a tap is validated against schedules
5. `05-arduino-integration.md` — hardware wiring and serial bridge design
6. `06-pages-and-features.md` — admin panel pages breakdown
7. `07-development-plan.md` — phased build plan
8. `08-future-wifi-integration.md` — planned Wi-Fi upgrade path
9. `09-progress-checklist.md` — implementation progress tracker
