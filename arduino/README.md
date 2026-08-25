# ESP32 Hardware Documentation & Setup Guide

This directory contains the production firmware for the **SDASFC (Smart Door Automation System for CICS)** hardware setup.

## Firmware Operation: Hybrid Mode (Online & Standalone Offline)

The firmware ([`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino)) supports **Hybrid Operation**:

1. **Connected via USB Serial / Wi-Fi**:
   - Live authorization checks against the MySQL database (`rfid_scan.php`).
   - Active user whitelists are automatically synchronized to ESP32 Flash memory on startup and tap.
2. **Standalone Offline (USB Unplugged & No Wi-Fi)**:
   - When the USB cable is unplugged, the ESP32 automatically falls back to its Non-Volatile Flash (`Preferences` / NVS) storage and hardcoded master list.
   - Any registered user will **unlock the door immediately** with the voice prompt even when running standalone!

---

## Full Hardware Components List

1. **Microcontroller**: ESP32 Dev Module (30-pin board layout)
2. **Power System**:
   - 12V 5A UPS Access Control Power Supply Board (with 12V backup battery connection)
   - 12V Lead-Acid / Lithium Backup Battery
   - LM2596 Buck Converter (Step-down 12V to 5V to power ESP32 VIN, RFID, RTC, DFPlayer, & Relay)
   - 1000µF 16V Power Decoupling & Smoothing Capacitor (across 5V and GND)
3. **RFID Reader**: MFRC522 (RC522 v133) 13.56MHz SPI Module
4. **Real-Time Clock (RTC)**: DS3231 AT24C32 I2C RTC Module
5. **Exit Sensor**: Access Control Infrared Optical Sensor Exit Button (No-Touch IR)
6. **Actuator / Relay**: 1-Channel 5V Relay module driving the 12V door lock
7. **Audio Feedback Module**: DFRobot DFPlayer Mini MP3 player (MP3-TF-16P) + 3W 8Ω Speaker with JST-PH 2-Pin connector *(Auto-detected: firmware runs smoothly with or without the audio module connected)*
8. **Protective Resistors**: 2x 1kΩ Resistors on DFPlayer RX/TX lines

---

## ESP32 Pin Connections & Wiring

| Component | Pin Label | ESP32 GPIO Pin / Target | Notes |
|-----------|-----------|-------------------------|-------|
| **RC522 RFID (v133)** | SDA (SS) | **GPIO 5** | SPI Chip Select |
| | SCK | **GPIO 18** | SPI Clock |
| | MOSI | **GPIO 23** | SPI Master Out |
| | MISO | **GPIO 19** | SPI Master In |
| | RST | **GPIO 4** | Reset Pin |
| | 3.3V | **ESP32 3V3 Pin** | ⚠️ **Connect to 3.3V ONLY! (Never 5V)** |
| | GND | Common GND | Ground |
| **DS3231 RTC** | SDA | **GPIO 21** | I2C Data |
| | SCL | **GPIO 22** | I2C Clock |
| | VCC | 5V Rail (Buck Converter) | 5V Power |
| | GND | Common GND | Ground |
| **Infrared Exit Sensor** | OUT / NO | **GPIO 33** | Active LOW (Hand wave detection) |
| | COM | Common GND | Ground Reference |
| | V+ / GND | Power Supply (+12V / GND) | Powered by 12V supply |
| **1-CH 5V Relay** | IN / SIG | **GPIO 27** | HIGH = Unlocked (6s), LOW = Locked |
| | VCC | 5V Rail (Buck Converter) | 5V Relay Coil Power |
| | GND | Common GND | Ground |
| | COM / NO | 12V Door Lock Loop | Switched 12V Power |
| **DFPlayer Mini (MP3-TF-16P)** | Pin 1 (VCC) | 5V Rail (Buck Converter) | Regulated 5V Power |
| | Pin 2 (RX) | **GPIO 17** (TX2) | ⚠️ **Connect via 1kΩ Resistor!** (Dampens noise) |
| | Pin 3 (TX) | **GPIO 16** (RX2) | ⚠️ **Connect via 1kΩ Resistor!** (Protects ESP32 3.3V logic) |
| | Pin 6 (SPK1)| Speaker Lead 1 (+) | 3W 8Ω Speaker |
| | Pin 7 (GND) | Common GND | Ground |
| | Pin 8 (SPK2)| Speaker Lead 2 (-) | 3W 8Ω Speaker |

---

## Required Arduino IDE Libraries

Before uploading [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino), open Arduino IDE (`Tools` -> `Manage Libraries...`) and install:
1. **MFRC522** by GithubCommunity
2. **RTClib** by Adafruit (for DS3231 RTC)
3. **DFRobotDFPlayerMini** by DFRobot

---

## MicroSD Card Audio Setup

Format a MicroSD card ($\le$ 32GB) to **FAT32 (MBR)** and place the following files in the root directory:

```text
MicroSD Card Root/
├── 0001.mp3  <-- Track 1: Access Denied prompt
└── 0002.mp3  <-- Track 2: Access Granted & Welcome to CICS Laboratory prompt
```

---

## Testing & Operation

1. Open [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino) in Arduino IDE.
2. Select Board: **ESP32 Dev Module**, select your COM port, and upload.
3. Start the Serial Bridge:
   - Double-click [`hardware/bridge/start_bridge.bat`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/hardware/bridge/start_bridge.bat) or run PowerShell / Python / PHP CLI bridge.
   - It will automatically synchronize all active users from MySQL into ESP32 Flash memory.
4. **Standalone Test**: Unplug the USB cable (ensure ESP32 is powered via 5V from the LM2596 buck converter). Tap a registered card — the door will unlock immediately!
