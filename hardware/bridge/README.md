# SDASFC Host Serial Bridge

The **Serial Bridge** acts as the communication link between the physical Arduino door controller (connected via USB) and the SDASFC Web Application API (`public/api/rfid_scan.php`).

## How It Works

```
┌─────────────────┐       USB Serial       ┌──────────────────────┐       HTTP POST        ┌─────────────────────────┐
│                 │ ──── UID:XX XX XX ───> │                      │ ── {"rfid_uid": ...} ─> │  SDASFC Web Application │
│  Arduino Uno    │                        │ Serial Bridge Script │                        │  (XAMPP / PHP / MySQL)  │
│                 │ <──── GRANT / DENY ──── │ (Python or PHP CLI)  │ <── {"access": ...} ── │  public/api/rfid_scan.php │
└─────────────────┘                        └──────────────────────┘                        └─────────────────────────┘
```

1. When a user scans an RFID card at the reader, the Arduino prints `UID:XX XX XX XX` over the USB serial interface.
2. The serial bridge script reads the serial line, extracts the UID string, and sends an HTTP POST request to the web API endpoint.
3. The Web API checks if the UID exists in the database `users` table and whether the user is active, logging the event in `access_logs`.
4. The Web API returns JSON `{"access": "granted"}` or `{"access": "denied"}`.
5. The serial bridge receives the HTTP response and immediately sends `GRANT\n` or `DENY\n` back to the Arduino.
6. The Arduino receives the command, plays the corresponding voice prompt via DFPlayer Mini, and triggers the relay if granted.

---

## Option 1: Running the Python Serial Bridge (Recommended)

### Requirements
- Python 3.6 or higher
- `pyserial` package:
  ```bash
  pip install pyserial
  ```

### Usage
Run the script from your terminal:
```bash
python hardware/bridge/serial_bridge.py --port COM3
```

Parameters:
- `--port`: The COM port assigned to the Arduino (e.g. `COM3`, `COM4` on Windows, `/dev/ttyUSB0` on Linux).
- `--baud`: Serial baud rate (default: `9600`).
- `--url`: API URL (default: `http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php`).

---

## Option 2: Running the PHP CLI Serial Bridge

If Python is not installed, you can run the PHP CLI bridge directly using XAMPP's PHP executable:

```bash
php hardware/bridge/serial_bridge.php --port=COM3
```

---

## Troubleshooting

1. **"Could not open serial port COM3"**:
   - Ensure the Arduino Serial Monitor window in Arduino IDE is **closed** (only one application can open the COM port at a time).
   - Check Device Manager (Windows) to verify the correct COM port number.
2. **"Failed to connect to Web API"**:
   - Verify XAMPP Apache is running.
   - Check that `http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php` is accessible in your web browser.
