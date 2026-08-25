# SDASFC Hybrid Serial Bridge

The **Serial Bridge** acts as the communication link between the physical ESP32 door controller (connected via USB) and the SDASFC Web Application API (`public/api/rfid_scan.php` & `public/api/whitelist.php`).

## How It Works in Hybrid Mode

```
┌─────────────────┐       USB Serial       ┌──────────────────────┐       HTTP POST / GET  ┌─────────────────────────┐
│                 │ ──── UID:XX XX XX ───> │                      │ ── {"rfid_uid": ...} ─> │  SDASFC Web Application │
│  ESP32 Dev      │ <──── GRANT / DENY ─── │ Hybrid Serial Bridge │                        │  (XAMPP / PHP / MySQL)  │
│  Door Lock      │ <── SYNC_WHITELIST: ── │ (PowerShell, Python, │ <── {"uids": [...] } ── │  public/api/            │
└─────────────────┘                        └────── or PHP CLI ────┘                        └─────────────────────────┘
```

1. **Automatic Whitelist Syncing**:
   - Whenever the Serial Bridge starts up, it queries `public/api/whitelist.php` and transmits all active registered user UIDs to the ESP32.
   - The ESP32 saves them into **Non-Volatile Flash Memory (Preferences / NVS)**.
2. **Live Tap Processing**:
   - When a card is scanned with USB connected, it logs the access to MySQL and makes real-time decisions (`GRANT` / `DENY`).
3. **Standalone Fallback**:
   - If the USB cable is unplugged or the PC is off, the ESP32 checks its local Flash storage. If the card is registered, the door unlocks immediately!

---

## How to Run the Bridge

### Option 1: Quick Start (Windows PowerShell / Batch - Default)
Double click [`hardware/bridge/start_bridge.bat`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/hardware/bridge/start_bridge.bat) or run in PowerShell:
```powershell
powershell -ExecutionPolicy Bypass -File hardware/bridge/serial_bridge.ps1
```

### Option 2: Python Serial Bridge
```bash
python hardware/bridge/serial_bridge.py --port COM3
```

### Option 3: PHP CLI Serial Bridge
```bash
php hardware/bridge/serial_bridge.php --port=COM3
```

---

## Troubleshooting

1. **"Could not open serial port COM3"**:
   - Close the Arduino IDE Serial Monitor (only one program can use the COM port at a time).
2. **"Failed to connect to Web API"**:
   - Ensure XAMPP Apache & MySQL are running.
