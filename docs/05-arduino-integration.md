# Arduino / ESP32 Hardware Integration
 
## Hardware Components
- **Microcontroller:** ESP32 Dev Module (30-pin board layout)
- **RFID Reader:** MFRC522 (RC522 v133) 13.56MHz SPI module (3.3V Logic)
- **Door Actuator:** 1-Channel 5V Relay Module driving a 12V Solenoid / Mag Lock (GPIO 27)
- **Exit Sensor:** Access Control Infrared Optical Sensor Exit Button (GPIO 33, Active LOW)
- **Real-Time Clock:** DS3231 AT24C32 I2C RTC Module (SDA GPIO 21, SCL GPIO 22)
- **Audio Feedback:** DFPlayer Mini (MP3-TF-16P) + 3W 8Ω Speaker (HardwareSerial2: RX2 GPIO 16, TX2 GPIO 17)
- **Power System:** 12V 5A UPS Power Supply + 12V Battery + LM2596 Buck Converter (Step-down to 5.0V) with 1000µF capacitor
 
## Pin Mapping & Wiring (ESP32)
| Module / Line | Module Pin | ESP32 GPIO Pin | Description / Notes |
|:---|:---|:---|:---|
| **RC522 RFID** | SS (SDA) | **GPIO 5** | SPI Chip Select |
| | SCK | **GPIO 18** | SPI Clock |
| | MOSI | **GPIO 23** | SPI Master Out |
| | MISO | **GPIO 19** | SPI Master In |
| | RST | **GPIO 4** | Reset |
| | 3.3V | **3V3 Pin** | ⚠️ 3.3V ONLY! Never connect to 5V |
| | GND | Common GND | Ground |
| **Relay (1-CH 5V)** | IN / SIG | **GPIO 27** | HIGH = Unlocked (6s hold), LOW = Locked |
| **Exit Sensor (IR)**| NO / OUT | **GPIO 33** | Active LOW (Internal pullup) |
| **DS3231 RTC** | SDA / SCL | **GPIO 21 / 22** | I2C Bus |
| **DFPlayer Mini** | RX / TX | **GPIO 17 / 16** | TX2 / RX2 via 1kΩ resistors |
 
## ESP32 Firmware Sketch (`arduino/sdasfc_door_lock.ino`)
1. Initialize RC522, RTC DS3231, DFPlayer Mini (Volume 30), Relay, IR Exit sensor, and Wi-Fi.
2. Baud rate: **`115200 baud`**.
3. **Master Emergency Key (`93 39 6E 1B`):** Verified locally by ESP32 firmware. Bypasses network and unlocks the door immediately for 6s during power brownouts or server downtime.
4. **Dual Transport Mode:**
   - **Wi-Fi Mode (Primary):** Sends HTTP POST directly to `http://192.168.1.208/.../rfid_scan.php`. No serial bridge needed when connected to router.
   - **Serial Mode (Fallback):** If Wi-Fi is disconnected, sends `UID:<HEX>` over USB Serial to `start_bridge.bat`.
5. On `GRANT`: Relay energized for 6000ms (`UNLOCK_HOLD_MS 6000`), DFPlayer plays Track 2 (`0002.mp3` - Access Granted & Welcome) at maximum volume (30).
6. On `DENY` or offline timeout: Relay remains locked, DFPlayer plays Track 1 (`0001.mp3` - Access Denied).
7. On Exit IR Sensor: Relay opens immediately for 6s **SILENTLY** (no welcome voice prompt) with edge-triggered anti-loop protection.
8. **Relay Inductive Immunity:** ESP32 brownout detector is disabled in software to prevent reboot/click-click loops during inductive load switching.
 
## Host PC Serial Bridge (`hardware/bridge/serial_bridge.ps1`, `.py`, `.php`)
Optional fallback when not using Wi-Fi: launched via [`start_bridge.bat`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/start_bridge.bat) on the host computer:
1. Opens serial COM port at `115200 baud` (auto-detects COM port in PowerShell bridge).
2. Listens for lines matching `UID:\s*([A-F0-9\s]+)`.
3. Sends POST request to Web API: `http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php` with `{ "rfid_uid": "<uid>" }`.
4. Parses JSON response `{ "access": "granted" | "denied", "reason": "..." }`.
5. Writes `GRANT\n` or `DENY\n` back to ESP32 over serial.
6. Web API registers recent scan in `access_logs`, enabling live tap auto-fill on user registration forms via [`public/api/last_scanned_rfid.php`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/last_scanned_rfid.php).
