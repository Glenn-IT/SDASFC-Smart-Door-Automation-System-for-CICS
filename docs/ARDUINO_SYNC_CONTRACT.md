# SDASFC — Arduino & Entire System Synchronization Contract

> **MANDATORY SYSTEM DIRECTIVE**: Whenever any file inside the `arduino/` folder or any hardware-related configuration is modified, ALL linked files listed in this contract MUST be inspected and synchronized immediately.

---

## 1. System Interconnection Architecture

```
                                  [ Physical Hardware ]
                                 RC522 RFID | Exit IR | Relay
                                             │
                                             ▼
                               ┌───────────────────────────┐
                               │ arduino/                  │
                               │  sdasfc_door_lock.ino     │◄── (Source of Truth for Hardware)
                               │  README.md, note.md       │
                               │  wiring_diagram_*.html    │
                               └─────────────┬─────────────┘
                                             │ USB Serial (115200 Baud)
                                             │ Protocol: "UID:<HEX>" / "GRANT" / "DENY"
                                             ▼
                               ┌───────────────────────────┐
                               │ hardware/bridge/          │
                               │  serial_bridge.ps1        │
                               │  serial_bridge.py         │
                               │  serial_bridge.php        │
                               │  start_bridge.bat         │
                               └─────────────┬─────────────┘
                                             │ HTTP POST JSON { "rfid_uid": "..." }
                                             ▼
                               ┌───────────────────────────┐
                               │ public/api/               │
                               │  rfid_scan.php            │
                               │  last_scanned_rfid.php    │
                               └─────────────┬─────────────┘
                                             │
                        ┌────────────────────┴────────────────────┐
                        ▼                                         ▼
         ┌───────────────────────────┐             ┌───────────────────────────┐
         │ app/                      │             │ public/                   │
         │  controllers/             │             │  users/create.php         │
         │   AccessController.php    │             │  users/edit.php           │
         │  models/                  │             │  wiring.php               │
         │   User.php, AccessLog.php │             │  dashboard.php            │
         └───────────────────────────┘             └───────────────────────────┘
                        │                                         │
                        └────────────────────┬────────────────────┘
                                             ▼
                               ┌───────────────────────────┐
                               │ docs/                     │
                               │  05-arduino-integration.md│
                               │  10-system-operation-...  │
                               │  README.md, Progress.md   │
                               └───────────────────────────┘
```

---

## 2. Change Trigger & Synchronization Matrix

Whenever an edit occurs in `arduino/`, consult the corresponding trigger below and update ALL associated files:

### Trigger A: Baud Rate Change (`SERIAL_BAUD`)
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `arduino/sdasfc_door_lock.ino` | Constant `#define SERIAL_BAUD <value>` and header comments |
| 2 | `hardware/bridge/serial_bridge.ps1` | Default parameter `[int]$Baud = <value>` |
| 3 | `hardware/bridge/serial_bridge.py` | Constant `DEFAULT_BAUD = <value>` |
| 4 | `hardware/bridge/serial_bridge.php` | Default option `$baud = <value>` |
| 5 | `hardware/bridge/README.md` | Connection settings & documentation |
| 6 | `arduino/README.md` | Serial monitor instructions and testing command |
| 7 | `docs/05-arduino-integration.md` | Section on sketch responsibilities and bridge configuration |
| 8 | `docs/10-system-operation-and-startup-guide.md` | Section 4 (Firmware Upload) and Section 7 (Serial Bridge) |

---

### Trigger B: Serial Protocol / UID Format Change
*Example: Changing from `UID:0A 75 B4 02` to another prefix, delimiter, or casing.*
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `arduino/sdasfc_door_lock.ino` | Serial output `Serial.print("UID:"); Serial.println(...)` |
| 2 | `hardware/bridge/serial_bridge.ps1` | Regex matcher `$line -match "UID\s*:\s*([A-F0-9\s]+)"` |
| 3 | `hardware/bridge/serial_bridge.py` | Regex matcher `re.search(r"UID:\s*([A-F0-9\s]+)", line)` |
| 4 | `hardware/bridge/serial_bridge.php` | Regex matcher `preg_match('/UID:\s*([A-F0-9\s]+)/i', $line)` |
| 5 | `public/api/rfid_scan.php` | Parameter validation for `rfid_uid` |
| 6 | `app/models/User.php` | `findByRfidUid()` normalization query (`REPLACE(..., ' ', '')`) |
| 7 | `app/models/AccessLog.php` | Storage and retrieval in `record()` and `lastScanned()` |
| 8 | `public/users/create.php` & `edit.php` | Input placeholders, regex validation, and auto-fetch parser |
| 9 | `docs/04-access-control-flow.md` | Data payload documentation |
| 10 | `docs/05-arduino-integration.md` | Protocol specification |

---

### Trigger C: Host Response Commands (`GRANT`, `DENY`, `UNLOCK`, `T1`, `T2`)
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `arduino/sdasfc_door_lock.ino` | Serial parser in `loop()` and `waitForSerialResponse()` |
| 2 | `hardware/bridge/serial_bridge.ps1` | Decision branches sending `$serial.WriteLine(...)` |
| 3 | `hardware/bridge/serial_bridge.py` | Decision branches sending `ser.write(...)` |
| 4 | `hardware/bridge/serial_bridge.php` | Decision branches sending `fwrite($handle, ...)` |
| 5 | `app/controllers/AccessController.php` | Output values in `handleScan()` (`'access' => 'granted'\|'denied'`) |
| 6 | `public/api/rfid_scan.php` | JSON response contract |
| 7 | `docs/04-access-control-flow.md` | Decision tree documentation |

---

### Trigger D: Pin Assignments & Wiring Connections
*Example: Modifying `RELAY_PIN`, `EXIT_BUTTON`, `SS_PIN`, `RST_PIN`, `RX2_PIN`, `TX2_PIN`, or I2C lines.*
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `arduino/sdasfc_door_lock.ino` | `#define` pin definitions and pin layout comments |
| 2 | `arduino/README.md` | Section "ESP32 Pin Connections & Breadboard Wiring" table |
| 3 | `arduino/note.md` | Section 1 "Complete Physical Wiring Table" |
| 4 | `arduino/wiring_diagram_with_breadboard/wiring_diagram.html` | Breadboard schematic SVG/DOM pins |
| 5 | `arduino/wiring_diagram_without_breadboard/wiring_diagra_wb.html` | Direct schematic SVG/DOM pins |
| 6 | `public/wiring.php` | Web portal wiring iframe view & diagram switcher |
| 7 | `docs/05-arduino-integration.md` | Section "Pin Mapping & Wiring (ESP32)" table |
| 8 | `docs/10-system-operation-and-startup-guide.md` | Section 2 "ESP32 Pin Connections" table |

---

### Trigger E: DFPlayer Mini Audio Track Assignments
*Example: Changing track mapping for Access Granted, Access Denied, or Welcome cues.*
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `arduino/sdasfc_door_lock.ino` | `player.play(<trackNumber>)` calls in `playGranted()` / `playDenied()` |
| 2 | `arduino/README.md` | Section "MicroSD Card Audio Setup" file listing |
| 3 | `arduino/note.md` | Section 2 "MicroSD Card Audio Tracks Setup" |
| 4 | `docs/10-system-operation-and-startup-guide.md` | Section 3 "MicroSD Card & Voice Cues Preparation" |

---

### Trigger F: Door Unlock Timing & Relay Hold Duration
*Example: Changing `#define UNLOCK_HOLD_MS 6000`.*
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `arduino/sdasfc_door_lock.ino` | Constant `#define UNLOCK_HOLD_MS` |
| 2 | `arduino/README.md` | Relay hold duration note in pin table and features |
| 3 | `arduino/note.md` | Relay unlock hold duration note |
| 4 | `docs/05-arduino-integration.md` | Unlock duration spec |
| 5 | `docs/10-system-operation-and-startup-guide.md` | Step 2 manual verification & Step 6 operation text |

---

### Trigger G: API Endpoints or Port Defaults
*Example: Moving `rfid_scan.php` or modifying URL structure.*
| # | File Path | What to Synchronize |
|---|---|---|
| 1 | `hardware/bridge/serial_bridge.ps1` | `$ApiUrl` default |
| 2 | `hardware/bridge/serial_bridge.py` | `DEFAULT_API_URL` constant |
| 3 | `hardware/bridge/serial_bridge.php` | `$apiUrl` default |
| 4 | `hardware/bridge/README.md` | API configuration guide |
| 5 | `docs/10-system-operation-and-startup-guide.md` | System architecture diagram & step 5/7 |

---

## 3. Mandatory Checklist for AI Agents & Developers

Before completing any task that edits `arduino/`:
- [ ] 1. Did you change baud rate? -> Verify and sync all 3 serial bridge scripts (`.ps1`, `.py`, `.php`) and docs.
- [ ] 2. Did you change pin assignments? -> Verify and sync `README.md`, `note.md`, wiring diagrams, and `docs/05` & `docs/10`.
- [ ] 3. Did you change audio tracks? -> Verify and sync `note.md`, `arduino/README.md`, and `docs/10`.
- [ ] 4. Did you change relay timing? -> Verify and sync `arduino/README.md`, `note.md`, and documentation.
- [ ] 5. Did you change serial input/output commands? -> Verify and sync all 3 serial bridges, `AccessController.php`, `rfid_scan.php`, and `User.php`.
- [ ] 6. Did you check `public/wiring.php`? -> Ensure diagram paths remain intact and reachable.
