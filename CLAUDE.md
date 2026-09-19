# Claude System Memory & Hardware Synchronization Rules
# Project: SDASFC (Smart Door Automation System for CICS)

## 🚨 MANDATORY ARDUINO HARDWARE SYNCHRONIZATION DIRECTIVE

Whenever you edit code or configuration in the **`arduino/`** directory (or hardware bridge components), you **MUST** automatically inspect and synchronize all interconnected files across the entire system. Never leave outside files out of sync with hardware firmware changes.

Full reference contract: [`docs/ARDUINO_SYNC_CONTRACT.md`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/docs/ARDUINO_SYNC_CONTRACT.md)

### 🔗 Key Interconnected Files Checklist:
1. **Firmware:** `arduino/sdasfc_door_lock.ino`
2. **Serial Bridges:** `hardware/bridge/serial_bridge.ps1`, `serial_bridge.py`, `serial_bridge.php`
3. **Web API & Controller:** `public/api/rfid_scan.php`, `app/controllers/AccessController.php`
4. **Data Models:** `app/models/User.php`, `app/models/AccessLog.php`
5. **Registration UI:** `public/users/create.php`, `public/users/edit.php`
6. **Wiring Diagrams & UI:** `arduino/wiring_diagram_*/`, `public/wiring.php`
7. **Hardware Docs:** `arduino/README.md`, `arduino/note.md`, `docs/05-arduino-integration.md`, `docs/08-future-wifi-integration.md`, `docs/10-system-operation-and-startup-guide.md`
8. **Master Key Bypass & Brownout:** `arduino/sdasfc_door_lock.ino`, `database/seed.sql` (UID: `93 39 6E 1B`)
9. **Wi-Fi Direct HTTP Integration:** `arduino/sdasfc_door_lock.ino`, `docs/08-future-wifi-integration.md`
