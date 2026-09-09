# Antigravity System Memory & Hardware Synchronization Rules
# Project: SDASFC (Smart Door Automation System for CICS)

## 🚨 MANDATORY ARDUINO HARDWARE SYNCHRONIZATION DIRECTIVE

Whenever you or any agent/developer edits code or configuration in the **`arduino/`** directory (or hardware bridge components), you **MUST** automatically inspect and synchronize all interconnected files across the entire system. Never leave outside files out of sync with hardware firmware changes.

Full reference contract: [`docs/ARDUINO_SYNC_CONTRACT.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/docs/ARDUINO_SYNC_CONTRACT.md)

---

## 🔗 Key Interconnected Files Map

### 1. Arduino Firmware & Circuitry (Source of Truth)
- [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino): Master ESP32 production firmware.
- [`arduino/README.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/README.md): Pin mapping, baud rate, libraries, audio tracks, and upload guide.
- [`arduino/note.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/note.md): Breadboard junction map and MicroSD audio track map.
- [`arduino/wiring_diagram_with_breadboard/wiring_diagram.html`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/wiring_diagram_with_breadboard/wiring_diagram.html): Breadboard schematic.
- [`arduino/wiring_diagram_without_breadboard/wiring_diagra_wb.html`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/wiring_diagram_without_breadboard/wiring_diagra_wb.html): Direct connection schematic.

### 2. Serial Bridge Layer (Outside Arduino)
- [`hardware/bridge/serial_bridge.ps1`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/hardware/bridge/serial_bridge.ps1): Native Windows PowerShell bridge (Primary bridge launched by `start_bridge.bat`).
- [`hardware/bridge/serial_bridge.py`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/hardware/bridge/serial_bridge.py): Cross-platform Python 3 serial bridge (`pyserial`).
- [`hardware/bridge/serial_bridge.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/hardware/bridge/serial_bridge.php): PHP CLI serial bridge.
- [`start_bridge.bat`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/start_bridge.bat): 1-click batch launcher for serial bridge.
- [`hardware/bridge/README.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/hardware/bridge/README.md): Serial bridge manual and parameter reference.

### 3. Backend API & Access Logic (Outside Arduino)
- [`public/api/rfid_scan.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php): Validates `POST {"rfid_uid": "..."}`, returns `{"access": "granted"|"denied", "reason": "..."}`.
- [`public/api/last_scanned_rfid.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/last_scanned_rfid.php): Provides live scanned RFID UID from recent taps for user registration.
- [`app/controllers/AccessController.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/app/controllers/AccessController.php): Access control decision logic and log recording.
- [`app/models/User.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/app/models/User.php): Normalizes and queries RFID UIDs (`REPLACE(..., ' ', '')`).
- [`app/models/AccessLog.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/app/models/AccessLog.php): Records access events.

### 4. Frontend & User Interface (Outside Arduino)
- [`public/users/create.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/public/users/create.php) & [`public/users/edit.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/public/users/edit.php): RFID registration fields with "Fetch Last Scanned Card" AJAX button.
- [`public/wiring.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/public/wiring.php): Embeds wiring diagram iframes with breadboard / direct toggle.

### 5. Documentation (Outside Arduino)
- [`docs/05-arduino-integration.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/docs/05-arduino-integration.md): Architecture & integration specs.
- [`docs/10-system-operation-and-startup-guide.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/docs/10-system-operation-and-startup-guide.md): Complete setup & operational handbook.
- [`docs/02-project-structure.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/docs/02-project-structure.md): Directory breakdown.
- [`README.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/README.md) & [`Progress.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/Progress.md): Status and instructions.

---

## ⚡ Sync Triggers & Required Actions

1. **Baud Rate Changed** (`SERIAL_BAUD = 115200`):
   - Update `arduino/sdasfc_door_lock.ino`, `arduino/README.md`.
   - Update all 3 bridges: `serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php`.
   - Update `docs/05-arduino-integration.md` and `docs/10-system-operation-and-startup-guide.md`.

2. **Serial UID or Message Format Changed** (e.g. `UID:<HEX>`):
   - Update regex matchers in `serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php`.
   - Update API parser in `public/api/rfid_scan.php`.
   - Update query normalization in `app/models/User.php`.
   - Update placeholder and validation in `public/users/create.php` & `edit.php`.

3. **Serial Decision Commands Changed** (`GRANT`, `DENY`, `UNLOCK`):
   - Update `arduino/sdasfc_door_lock.ino` command parser.
   - Update `serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php` send lines.
   - Update `AccessController.php` output keys and `rfid_scan.php`.

4. **ESP32 Pins or Wiring Changed**:
   - Update `#define` pins in `arduino/sdasfc_door_lock.ino`.
   - Update pin tables in `arduino/README.md` and `arduino/note.md`.
   - Update HTML wiring diagrams in `arduino/wiring_diagram_*/`.
   - Update `public/wiring.php` switcher.
   - Update pin tables in `docs/05-arduino-integration.md` and `docs/10-system-operation-and-startup-guide.md`.

5. **Audio Track Assignment Changed**:
   - Update `player.play()` calls in `arduino/sdasfc_door_lock.ino`.
   - Update track lists in `arduino/README.md`, `arduino/note.md`, and `docs/10-system-operation-and-startup-guide.md`.

6. **Relay Hold Duration Changed** (`UNLOCK_HOLD_MS = 6000`):
   - Update constant in `arduino/sdasfc_door_lock.ino`.
   - Update descriptions in `arduino/README.md`, `arduino/note.md`, `docs/05-arduino-integration.md`, and `docs/10-system-operation-and-startup-guide.md`.

---

## 🔒 Code Style & Operational Rules
- Never break the `GRANT` / `DENY` serial response contract between the bridge and ESP32.
- Preserve backward compatibility for both space-separated (`0A 75 B4 02`) and non-spaced (`0A75B402`) RFID UIDs.
- Always keep PowerShell (`serial_bridge.ps1`), Python (`serial_bridge.py`), and PHP (`serial_bridge.php`) feature-parity identical.
