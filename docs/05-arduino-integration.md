# Arduino Integration

## Hardware
- **Microcontroller:** Arduino Uno or Nano
- **RFID Reader:** MFRC522 (RC522) module, SPI interface
- **Door Actuator:** relay module driving an electric strike lock or solenoid
  lock (or a servo for a latch-style mechanism)
- **Optional feedback:** LED (green/red) + buzzer for granted/denied indication
- **Power:** Arduino via USB (also serves as the data link to the PC)

## Wiring (RC522 → Arduino Uno, standard SPI pinout)
| RC522 Pin | Arduino Uno Pin |
|-----------|------------------|
| SDA (SS)  | D10              |
| SCK       | D13              |
| MOSI      | D11              |
| MISO      | D12              |
| IRQ       | not connected    |
| GND       | GND              |
| RST       | D9               |
| 3.3V      | 3.3V             |

Relay module signal pin → e.g. D7 (drives door lock). LED/buzzer on spare
digital pins (e.g., D6 green, D5 red, D4 buzzer).

## Arduino sketch responsibilities (`hardware/arduino/rfid_door_lock.ino`)
1. Initialize RC522 + Serial (baud rate e.g. 9600).
2. Loop: check for a new card; if present, read UID, format as hex string.
3. Send UID to Serial as a single line, e.g. `UID:A1B2C3D4\n`.
4. Wait (with timeout) for a response line from Serial, e.g. `GRANT\n` or `DENY\n`.
5. On `GRANT`: energize relay for N seconds (e.g. 5s) to unlock, light green
   LED/short beep.
6. On `DENY` or timeout: keep relay off, light red LED/long beep.

## Serial bridge script (`hardware/bridge/serial_bridge.php` or `.py`)
Runs continuously on the host PC (the same machine running XAMPP, or one that
can reach it over LAN):
1. Open the serial port (e.g., `COM3` on Windows) at the same baud rate.
2. On each line received matching `UID:...`, extract the UID.
3. Call the backend: `POST http://localhost/SDASFC.../app/api/rfid_scan.php`
   with `{ "rfid_uid": "<uid>" }`.
4. Parse the JSON response; write `GRANT\n` or `DENY\n` back to the serial port.
5. Log errors (connection failures, malformed UID) to a local log file.

A Python script (using `pyserial` + `requests`) is usually simpler to prototype
than PHP CLI for serial I/O; can migrate to PHP CLI later if we want a
single-language stack. Decision can be deferred to the hardware-integration
milestone (see `07-development-plan.md`).

## Why USB Serial (not WiFi) for v1
Matches the decision to keep hardware simple: Arduino Uno/Nano + RC522 has no
built-in WiFi, avoiding the need for an ESP8266/ESP32 and the network
configuration that comes with it. The trade-off is the door reader must stay
tethered near the host PC. This can be revisited later by swapping the bridge
script for direct HTTP calls from an ESP32-based reader.
