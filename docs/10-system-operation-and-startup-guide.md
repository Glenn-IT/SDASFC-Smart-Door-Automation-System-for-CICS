# SDASFC — System Initiation & Operation Guide

**Smart Door Automation System for CICS**  
*Complete Hardware, Firmware, Serial Bridge & Web Dashboard Operation Manual*

---

## 📑 Table of Contents
1. [System Architecture Overview](#1-system-architecture-overview)
2. [Hardware Prerequisites & Pin Reference](#2-hardware-prerequisites--pin-reference)
3. [Step 1: MicroSD Card & Voice Cues Preparation](#3-step-1-microsd-card--voice-cues-preparation)
4. [Step 2: Firmware Upload & Hardware Verification](#4-step-2-firmware-upload--hardware-verification)
5. [Step 3: Starting Web Server & Database (XAMPP)](#5-step-3-starting-web-server--database-xampp)
6. [Step 4: User & RFID Card Registration](#6-step-4-user--rfid-card-registration)
7. [Step 5: Launching the Real-Time Serial Bridge](#7-step-5-launching-the-real-time-serial-bridge)
8. [Step 6: Daily Operation & Access Workflows](#8-step-6-daily-operation--access-workflows)
9. [Troubleshooting & Maintenance FAQ](#9-troubleshooting--maintenance-faq)
10. [Daily Quick-Start Cheat Sheet](#10-daily-quick-start-cheat-sheet)

---

## 1. System Architecture Overview

The SDASFC integrates an **ESP32 microcontroller**, a local **PHP/MySQL web portal**, and an automatic **USB Serial Bridge**:

```
 ┌────────────────────────────────────────────────────────┐
 │                   PHYSICAL HARDWARE                    │
 │                                                        │
 │  [ RFID Card Tap ]         [ No-Touch IR Exit Sensor ] │
 │         │                               │              │
 │         ▼                               ▼              │
 │  [ RC522 Reader ]                 (GPIO 33 - Wave)     │
 │         │                               │              │
 │         ▼                               │              │
 │  [ ESP32 Microcontroller (115200 Baud) ]               │
 │    ├─► 1-CH 5V Relay ──► 12V Mag Lock / Solenoid       │
 │    ├─► DS3231 RTC Module (I2C Real-Time Clock)         │
 │    └─► DFPlayer Mini ──► 3W 8Ω Speaker (Voice Prompts) │
 └───────────────────────┬────────────────────────────────┘
                         │ USB Serial Cable
                         │ ("UID:<HEX_UID>" / "GRANT" / "DENY")
                         ▼
 ┌────────────────────────────────────────────────────────┐
 │                 HOST PC / SERVER                       │
 │                                                        │
 │  [ Hardware Serial Bridge ] (serial_bridge.php / .ps1) │
 │         │                                              │
 │         ▼ POST JSON                                    │
 │  [ Web API: public/api/rfid_scan.php ]                 │
 │         │                                              │
 │         ▼                                              │
 │  [ MySQL Database: `sdasfc` (Users & Access Logs) ]    │
 │         │                                              │
 │         ▼                                              │
 │  [ Admin Web Dashboard: http://localhost/.../public/ ] │
 └────────────────────────────────────────────────────────┘
```

---

## 2. Hardware Prerequisites & Pin Reference

### Power Distribution
- **Main Power Supply:** 12V 5A UPS Access Control Power Supply with 12V Backup Battery.
- **LM2596 Buck Converter:** Steps down 12V to **5.0V Regulated** with a **1000µF 16V filter capacitor** across 5V and GND.
- **Common Ground:** All module `GND` pins must connect to the shared ground bus.

### ESP32 Pin Connections
| Component | Module Pin | ESP32 Pin / Voltage Rail | Notes / Signal |
| :--- | :--- | :--- | :--- |
| **Power Input** | VIN (5V) | Buck Converter 5V Rail | 5.0V Regulated |
| **Common GND** | GND | Common Ground Rail | Shared 0V Bus |
| **RFID-RC522** | 3.3V | **ESP32 3V3 Pin** | ⚠️ **3.3V ONLY! Never connect to 5V** |
| | GND | Common GND | |
| | RST | **GPIO 4** | SPI Reset |
| | MISO | **GPIO 19** | SPI Master-In |
| | MOSI | **GPIO 23** | SPI Master-Out (VSPI) |
| | SCK | **GPIO 18** | SPI Clock |
| | SS / SDA | **GPIO 5** | SPI Chip Select |
| **DS3231 RTC** | VCC | 5V Power Rail | |
| | GND | Common GND | |
| | SDA | **GPIO 21** | I2C Data |
| | SCL | **GPIO 22** | I2C Clock |
| **DFPlayer Mini**| Pin 1 (VCC) | 5V Power Rail | Filtered 5V Rail |
| | Pin 2 (RX) | **GPIO 17 (TX2)** | **Via 1kΩ Resistor** (Reduces noise) |
| | Pin 3 (TX) | **GPIO 16 (RX2)** | **Via 1kΩ Resistor** (Protects ESP32) |
| | Pin 7 (GND) | Common GND | |
| | Pin 6 (SPK1)| Speaker (+) | Red Wire |
| | Pin 8 (SPK2)| Speaker (-) | Black Wire |
| **1-CH Relay** | VCC | 5V Power Rail | |
| | GND | Common GND | |
| | IN / SIG | **GPIO 27** | HIGH = Unlock, LOW = Locked |
| | COM / NO | 12V Power & Door Lock | Switches 12V to Lock |
| **IR Exit Sensor**| V+ / GND | +12V / 12V GND | 12V Power from Supply |
| | COM | Common GND | Ground Reference |
| | OUT / NO | **GPIO 33** | Active LOW (Internal Pull-Up) |

---

## 3. Step 1: MicroSD Card & Voice Cues Preparation

The DFPlayer Mini plays synchronized voice prompts for each access event.

1. **Card Format:** Format a MicroSD card ($\le 32$GB) as **FAT32** with Master Boot Record (MBR).
2. **File Structure:** Place audio files directly in the root directory (or in a folder named `MP3`):
   - `0001.mp3` — **Access Granted & Welcome:** *"Access granted you may now open the door. Welcome to the CICS laboratory"* (Plays on valid RFID card tap or IR Exit wave).
   - `0002.mp3` — **Access Denied:** *"Access Denied"* (Plays on unregistered or inactive/deactivated card taps).
   - *(Note: `0004.mp3 Door Lock` has been removed from the system. The lock operates silently after 5 seconds).*
3. Insert the card into the DFPlayer Mini slot.

---

## 4. Step 2: Firmware Upload & Hardware Verification

1. Connect the ESP32 to your PC via a Micro-USB data cable.
2. Open **Arduino IDE** and open [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino).
3. Select board: **Tools $\rightarrow$ Board $\rightarrow$ ESP32 Arduino $\rightarrow$ ESP32 Dev Module**.
4. Select port: **Tools $\rightarrow$ Port $\rightarrow$ COM9** (or your detected COM port).
5. Click **Upload** (`Ctrl + U`).
6. After upload completes, open **Tools $\rightarrow$ Serial Monitor** (`Ctrl + Shift + M`) at **`115200 baud`**.
7. Press the **EN / RESET** button on the ESP32. You will see:
   ```text
   ==================================================
     SDASFC ESP32 SMART DOOR LOCK CONTROLLER
   ==================================================
   [HW] Relay initialized (State: LOCKED).
   [HW] IR Exit Sensor initialized on GPIO 33.
   [HW] RTC DS3231 Ready: 2026-08-20 18:30:00
   [HW] RFID RC522 Reader Ready.
   [HW] Detecting DFPlayer Mini... ✅ Module Online!
   [HW] MicroSD Card OK: 4 readable audio file(s) found.
   --------------------------------------------------
   SYS:READY — Awaiting RFID taps or Exit button events.
   --------------------------------------------------
   ```

### Quick Manual Test:
- **Tap a Card:** The monitor prints `UID:XX XX XX XX`.
- **Wave at IR Sensor:** The relay clicks open for 5 seconds and prints `[EVENT] IR Exit Sensor Triggered!`.

> ⚠️ **IMPORTANT:** Close the Serial Monitor tab before proceeding to Step 5!

---

## 5. Step 3: Starting Web Server & Database (XAMPP)

1. Open the **XAMPP Control Panel** on Windows.
2. Start **Apache** and **MySQL**.
3. Open your browser and navigate to:  
   👉 **`http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/`**
4. Log into the Admin Dashboard using your administrative credentials.

---

## 6. Step 4: User & RFID Card Registration

To give a student or faculty member access:

1. In the Web Dashboard, go to **Faculty & Students** (or **Card Management**).
2. Click **Add New User** or **Edit** an existing user.
3. Enter their details:
   - **Full Name:** e.g., `John Doe`
   - **ID Number:** e.g., `2024-0012`
   - **RFID UID:** Enter the card UID (e.g., `4A 7B 12 9C` or `4A7B129C`).  
     *(Spaces and lowercase/uppercase are automatically handled).*
   - **Role:** `student` or `faculty`
   - **Status:** Set to **`active`**.
4. Click **Save User**.

---

## 7. Step 5: Launching the Real-Time Serial Bridge

The Serial Bridge connects the ESP32 USB port to the PHP/MySQL database in real time with automatic COM port detection.

1. Ensure the Arduino Serial Monitor is **closed**.
2. **Option A (Easiest — 1-Click):** Double-click [`start_bridge.bat`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/start_bridge.bat) in the project folder.
3. **Option B (PowerShell):** Run:
   ```powershell
   powershell -ExecutionPolicy Bypass -File hardware\bridge\serial_bridge.ps1
   ```

You will see:
```text
==================================================
   SDASFC SMART DOOR AUTOMATION SYSTEM
   Native Windows Hardware Serial Bridge (v2.0)
==================================================
[AUTO-DETECT] Found and selected port: COM9
 Target Port  : COM9
 Baud Rate    : 115200
 API Endpoint : http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php
--------------------------------------------------
[STATUS] Connected to COM9 successfully!
[STATUS] Web Database Integration: ACTIVE
[STATUS] Ready! Listening for RFID scans & Exit events...
==================================================
```

---

## 8. Step 6: Daily Operation & Access Workflows

### Scenario A: Authorized Entry (Valid Card Tap)
1. User taps their registered active RFID card on the reader.
2. ESP32 sends `UID:<HEX_UID>` over USB.
3. Bridge queries the web database $\rightarrow$ Finds active user $\rightarrow$ Replies `GRANT`.
4. ESP32 actions:
   - **Immediately energizes relay** (Door unlocks on millisecond 0).
   - Plays Track 2: *"Access granted you may now open the door. Welcome to the CICS laboratory"*.
   - Keeps door open for **6 seconds**.
   - De-energizes relay (door locks silently).
5. The web portal logs the entry timestamp, user name, and ID number in **Access Logs**.

### Scenario B: Unauthorized Entry (Unknown / Inactive Card)
1. User taps an unregistered or deactivated card.
2. Bridge queries the web database $\rightarrow$ No active match $\rightarrow$ Replies `DENY`.
3. ESP32 actions:
   - Plays Track 1: *"Access Denied"*.
   - Door remains securely locked.
4. The web portal logs an unauthorized attempt with `reason: unknown_uid` or `inactive_user`.

### Scenario C: Exiting the Room (No-Touch Wave-to-Exit)
1. Person inside the room waves their hand 5–10 cm in front of the optical IR sensor.
2. ESP32 detects signal on **GPIO 33**:
   - **Immediately energizes relay** for 6 seconds.
   - Plays Track 2: *"Access granted you may now open the door. Welcome to the CICS laboratory"*.
   - Automatically relocks after 6 seconds.

---

## 9. Troubleshooting & Maintenance FAQ

### Q1: The Serial Bridge says `Could not open serial port`
- **Cause:** Arduino IDE Serial Monitor or another program is open and locking the port.
- **Fix:** Close the Serial Monitor in Arduino IDE, then re-run `start_bridge.bat`.

### Q2: DFPlayer Mini plays wrong sound
- **Cause:** Track index mapping.
- **Fix:** Firmware maps Track 2 $\rightarrow$ Access Granted/Welcome, Track 1 $\rightarrow$ Access Denied.

### Q3: Database connection refused error
- **Cause:** MySQL is stopped in XAMPP.
- **Fix:** Open XAMPP Control Panel and start MySQL.

---

## 10. Daily Quick-Start Cheat Sheet

```powershell
# 1. Ensure Apache & MySQL are running in XAMPP Control Panel

# 2. Launch the Hardware Serial Bridge (Auto-detects COM port):
Double-click start_bridge.bat   (or run: powershell -File hardware\bridge\serial_bridge.ps1)

# 3. Open Admin Web Dashboard in browser:
http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/
```

*System is fully web-integrated, real-time responsive, and production ready.*
