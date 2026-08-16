# ESP32 Hardware Documentation & Setup Guide

This directory contains the production firmware for the **SDASFC (Smart Door Automation System for CICS)** hardware setup.

## Full Hardware Components List (from `note.md`)

1. **Microcontroller**: ESP32 Dev Module
2. **Power System**:
   - 12V 5A UPS Access Control Power Supply Board (with 12V backup battery connection)
   - 12V Lead-Acid / Lithium Backup Battery
   - LM2596 Buck Converter (Step-down 12V to 5V to power ESP32, RFID, RTC, & Relay)
3. **RFID Reader**: MFRC522 (RC522 v133) 13.56MHz SPI Module
4. **Real-Time Clock (RTC)**: DS3231 AT24C32 I2C RTC Module
5. **Exit Sensor**: Access Control Infrared Optical Sensor Exit Button (No-Touch IR)
6. **Actuator / Relay**: 1-Channel 5V Relay module driving the 12V door lock
7. **Audio Feedback Module**: DFRobot DFPlayer Mini MP3 player + 3W 8Ω Speaker with JST-PH 2-Pin connector *(Auto-detected: if not attached yet, firmware runs smoothly without stalling)*

---

## ESP32 Pin Connections

| Component | Pin Label | ESP32 GPIO Pin | Notes |
|-----------|-----------|----------------|-------|
| **RC522 RFID (v133)** | SDA (SS) | **GPIO 5** | SPI CS |
| | SCK | **GPIO 18** | SPI SCK |
| | MOSI | **GPIO 23** | SPI MOSI |
| | MISO | **GPIO 19** | SPI MISO |
| | RST | **GPIO 4** | Reset Pin |
| | 3.3V | **3.3V** | **WARNING: Connect to 3.3V ONLY!** |
| | GND | GND | Ground |
| **DS3231 RTC** | SDA | **GPIO 21** | I2C Data |
| | SCL | **GPIO 22** | I2C Clock |
| | VCC | 5V / 3.3V | Power |
| | GND | GND | Ground |
| **Infrared Exit Sensor** | OUT / SIG | **GPIO 33** | Active LOW on hand motion |
| | VCC | 5V / 12V | Power (per sensor spec) |
| | GND | GND | Ground |
| **1-CH 5V Relay** | IN / SIG | **GPIO 27** | HIGH = Unlocked (5s), LOW = Locked |
| | VCC | 5V | Power from LM2596 |
| | GND | GND | Ground |
| **DFPlayer Mini (Optional)** | RX | **GPIO 17** (TX2) | ESP32 TX2 (via 1kΩ resistor) |
| | TX | **GPIO 16** (RX2) | ESP32 RX2 (HardwareSerial2) |
| | VCC | 5V | Power |
| | SPK1 / SPK2 | Speaker Pins | 3W 8Ω Speaker |

---

## Required Arduino IDE Libraries

Before uploading [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino), open Arduino IDE (`Tools` -> `Manage Libraries...`) and install:
1. **MFRC522** by GithubCommunity
2. **RTClib** by Adafruit (for DS3231 RTC)
3. **DFRobotDFPlayerMini** by DFRobot

---

## MicroSD Card Audio Setup (When DFPlayer is added)

Format a MicroSD card (FAT32, max 32GB) and save files in root:

```text
SD Card Root/
├── 0001.mp3  <-- Track 1: System Ready / Welcome prompt
├── 0002.mp3  <-- Track 2: Access Granted prompt
└── 0003.mp3  <-- Track 3: Access Denied prompt
```

*Note: Since the DFPlayer and speaker are not yet installed on your hardware, the firmware automatically detects this on startup, logs `DFPlayer Mini NOT detected (System running without audio prompts)`, and continues running the RFID access system seamlessly! When you plug them in, audio will activate automatically.*
