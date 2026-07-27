# Project Structure

Proposed folder layout under the XAMPP `htdocs` project root
(`SDASFC-Smart-Door-Automation-System-for-CICS/`):

```
SDASFC-Smart-Door-Automation-System-for-CICS/
├── docs/                        # planning docs (this folder)
│
├── public/                      # web root (point Apache vhost here, or use project root)
│   ├── index.php                # entry point / router
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── users/
│   │   ├── index.php            # list users
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── reports/
│   │   ├── index.php            # logs table + filters
│   │   └── export.php           # CSV export
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
│
├── app/                          # application logic (kept out of direct web access)
│   ├── config/
│   │   ├── config.php            # site config, timezone, constants
│   │   └── database.php          # PDO connection
│   ├── models/
│   │   ├── User.php
│   │   ├── AccessLog.php
│   │   └── Admin.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ReportController.php
│   │   └── DashboardController.php
│   ├── core/
│   │   ├── Auth.php              # session/auth helper, "require admin" guard
│   │   ├── Database.php          # PDO wrapper
│   │   └── helpers.php           # date/time helpers, response helpers
│   └── api/
│       └── rfid_scan.php         # endpoint the Arduino bridge script calls
│
├── database/
│   ├── schema.sql                # full CREATE TABLE statements
│   └── seed.sql                  # sample admin + test users
│
├── hardware/
│   ├── arduino/
│   │   └── rfid_door_lock.ino    # Arduino sketch (RFID read + relay control)
│   └── bridge/
│       └── serial_bridge.php     # (or .py) reads serial port, POSTs to app/api/rfid_scan.php
│
└── README.md
```

## Notes
- `app/` sits outside `public/` conceptually; since XAMPP serves from the project
  root, we restrict direct access to `app/` via an `.htaccess` deny rule (or move
  the whole project so only `public/` is web-accessible if using a custom vhost).
- `app/api/rfid_scan.php` is the single integration point between hardware and
  software — the Arduino bridge script is the only "client" that calls it.
- Keep Arduino sketch and bridge script versioned in `hardware/` so hardware and
  software history stay together in the same repo.
