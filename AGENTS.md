# Agent System Memory & Hardware Synchronization Rules
# Project: SDASFC (Smart Door Automation System for CICS)

## 🚨 CRITICAL RULE: ARDUINO HARDWARE SYNCHRONIZATION

Whenever you edit code or documentation inside **`arduino/`**, you **MUST** synchronize all dependent files across the system:

1. **Baud Rate (`SERIAL_BAUD = 115200`)**:
   - Firmware: `arduino/sdasfc_door_lock.ino`
   - Serial Bridges: `hardware/bridge/serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php`
   - Documentation: `arduino/README.md`, `hardware/bridge/README.md`, `docs/05-arduino-integration.md`, `docs/10-system-operation-and-startup-guide.md`

2. **Serial UID Format (`UID:<HEX_UID>`)**:
   - Firmware: `arduino/sdasfc_door_lock.ino`
   - Serial Bridges: Regex in `serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php`
   - API: `public/api/rfid_scan.php`, `public/api/last_scanned_rfid.php`
   - Backend Model: `app/models/User.php` (`findByRfidUid()`), `app/models/AccessLog.php`
   - Frontend: `public/users/create.php`, `public/users/edit.php`
   - Documentation: `docs/04-access-control-flow.md`, `docs/05-arduino-integration.md`

3. **Serial Decision Protocol (`GRANT` / `DENY` / `UNLOCK`)**:
   - Firmware: `arduino/sdasfc_door_lock.ino`
   - Serial Bridges: `serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php`
   - Backend Controller: `app/controllers/AccessController.php`

4. **ESP32 Pins & Wiring**:
   - Firmware: `arduino/sdasfc_door_lock.ino`
   - Wiring Maps: `arduino/README.md`, `arduino/note.md`
   - Schematics: `arduino/wiring_diagram_with_breadboard/wiring_diagram.html`, `arduino/wiring_diagram_without_breadboard/wiring_diagra_wb.html`
   - Web Portal: `public/wiring.php`
   - Docs: `docs/05-arduino-integration.md`, `docs/10-system-operation-and-startup-guide.md`

5. **Audio Tracks (DFPlayer Mini)**:
   - Track 1 (`0001.mp3`): Access Denied
   - Track 2 (`0002.mp3`): Access Granted & Welcome
   - Synchronize across `arduino/sdasfc_door_lock.ino`, `arduino/README.md`, `arduino/note.md`, `docs/10-system-operation-and-startup-guide.md`

6. **Relay Hold Timing (`UNLOCK_HOLD_MS = 6000`)**:
   - Synchronize across `arduino/sdasfc_door_lock.ino`, `arduino/README.md`, `arduino/note.md`, `docs/05-arduino-integration.md`, `docs/10-system-operation-and-startup-guide.md`

7. **Master Emergency Key Card (`93 39 6E 1B`) & Brownout Safety**:
   - Synchronize across `arduino/sdasfc_door_lock.ino`, `database/seed.sql`, database `users` table, and `docs/08-future-wifi-integration.md`

8. **Wi-Fi Direct HTTP Integration (`WIFI_SSID`, `API_URL`)**:
   - Synchronize across `arduino/sdasfc_door_lock.ino`, `docs/08-future-wifi-integration.md`, and `docs/10-system-operation-and-startup-guide.md`

9. **Speaker Volume (`defaultVolume = 30`)**:
   - Synchronize across `arduino/sdasfc_door_lock.ino`, `arduino/README.md`, and `arduino/note.md`

For complete matrix, see [`docs/ARDUINO_SYNC_CONTRACT.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/docs/ARDUINO_SYNC_CONTRACT.md).
