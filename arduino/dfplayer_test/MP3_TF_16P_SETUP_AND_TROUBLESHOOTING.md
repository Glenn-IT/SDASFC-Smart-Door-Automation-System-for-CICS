# 📘 ESP32 + MP3-TF-16P Hardware Setup & Troubleshooting Guide
**Project:** Smart Door Automation System for CICS (SDASFC)  
**Location:** `arduino/dfplayer_test`

---

## 📌 1. Hardware Overview & Pin Mapping

### Components Used:
* **Microcontroller:** ESP32 DevKit V1 (30-pin layout: `RX2`, `TX2`, `GND`, `VIN`)
* **Audio Player Module:** MP3-TF-16P (16-pin DIP package, 8 pins per side)
* **Speaker:** 3070 3W 8Ω Mini Rectangular Speaker
* **Resistors:** 2x 1kΩ (1kΩ on ESP32 `TX2` -> MP3 `RX`, 1kΩ on MP3 `TX` -> ESP32 `RX2`)
* **Power Supply:** External 5V 2A Power Adapter / Phone Charger (Solution 1 - Power Isolation)
* **MicroSD Card:** 4GB - 32GB MicroSDHC formatted **FAT32** with **MBR**

---

### 📐 Physical Pinout Table (ESP32 DevKit V1 + MP3-TF-16P)

| MP3-TF-16P Pin | Label | ESP32 DevKit V1 Connection | Notes |
| :--- | :--- | :--- | :--- |
| **Pin 1 (Top Left)** | **VCC** | **External 5V (+)** | Direct 5V Power Input |
| **Pin 2 (2nd Left)** | **RX** | **Pin TX2** | ⚠️ **Connect through 1kΩ Resistor!** (Dampens noise & current) |
| **Pin 3 (3rd Left)** | **TX** | **Pin RX2** | ⚠️ **Connect through 1kΩ Resistor!** (Protects 3.3V ESP32 DevKit `RX2` pin) |
| **Pin 6 (6th Left)** | **SPK1** | **3070 Speaker Lead 1 (+)** | Audio Output Channel 1 |
| **Pin 7 (7th Left)** | **GND** | **ESP32 GND & External GND** | **Common Ground Junction (Mandatory)** |
| **Pin 8 (Bottom Left)**| **SPK2** | **3070 Speaker Lead 2 (-)** | Audio Output Channel 2 |

---

## 📐 Wiring Schematic (Solution 1 - External 5V Power)

```text
  [ External 5V Power Adapter ]
       +5V -------------> MP3-TF-16P Pin 1 (VCC)
       GND ---+---------> MP3-TF-16P Pin 7 (GND)
              |
              +---------> ESP32 DevKit V1 GND (Common Ground Junction)

  [ ESP32 DevKit V1 Board ]
       Pin TX2 -------------> [ 1kΩ Resistor ] ---> MP3-TF-16P Pin 2 (RX)
       Pin RX2 <------------- [ 1kΩ Resistor ] <--- MP3-TF-16P Pin 3 (TX)

  [ MP3-TF-16P Module ]
       Pin 6 (SPK1) ---------------------------> 3070 Speaker Lead 1 (+)
       Pin 8 (SPK2) ---------------------------> 3070 Speaker Lead 2 (-)
```

---

## 🛠️ 2. Issues Encountered & Resolved

### 🔴 Problem 1: ESP32 Boot Loop (`flash read err, 988`)
* **Symptom:** ESP32 kept rebooting continuously on power-up showing `flash read err, 988` or `invalid header: 0xffffffff`.
* **Root Cause:** Powering the MP3 module directly from the ESP32's onboard 5V/VIN pin caused a severe voltage dip on boot when the MP3 module initialized.
* **Resolution:** Connected the MP3-TF-16P module to an **External 5V Power Supply** with **Common GND** tied to ESP32 GND (Solution 1).

---

### 🔴 Problem 2: Watchdog Timer Reset (`rst:0x8 (TG1WDT_SYS_RESET)`)
* **Symptom:** ESP32 booted, printed `[SUCCESS]`, and then immediately reset showing `rst:0x8 (TG1WDT_SYS_RESET)`.
* **Root Cause:** DFRobot library serial calls blocked the ESP32 main execution thread without yielding CPU control to FreeRTOS, tripping the Timer Group 1 Watchdog.
* **Resolution:** Updated [`dfplayer_test.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/dfplayer_test/dfplayer_test.ino) to include explicit `yield();` and `delay(10);` calls in `setup()` and `loop()`.

---

### 🔴 Problem 3: MP3-TF-16P LED Off & SD Card Not Detected
* **Symptom:** Commands sent over serial showed `[PLAY] Playing Track 2: 0002.mp3`, but the blue LED on the MP3-TF-16P stayed OFF and no sound played.
* **Diagnostic Discovery:** `player.readFileCounts()` returned `0 files detected` or `-1`.
* **Root Cause:** The MP3-TF-16P chip requires **SDHC cards $\le$ 32GB** formatted **FAT32** with **MBR**. Cards formatted exFAT or larger than 32GB fail to mount.

---

## 🎵 3. MicroSD Card Setup Specifications

### Card Format:
* **Capacity:** 2GB, 4GB, 8GB, 16GB, or **32GB Max** (MicroSDHC).
* **FileSystem:** **FAT32** (Allocation size: 4096 bytes).
* **Partition Style:** MBR (Master Boot Record).

### Directory & File Structure:
```text
MicroSD Card/
├── 0001.mp3   <-- Track 1: System Ready / Welcome Prompt
├── 0002.mp3   <-- Track 2: Access Granted Prompt
└── 0003.mp3   <-- Track 3: Access Denied Prompt
```
*or inside folder:*
```text
MicroSD Card/
└── mp3/
    ├── 0001.mp3
    ├── 0002.mp3
    └── 0003.mp3
```

---

## 💻 4. Interactive Serial Monitor Commands (115200 Baud)

| Command | Action |
| :---: | :--- |
| **`d`** | **Run Full Hardware Diagnostic** (DFPlayer Module + SD Card + Audio File Detection) |
| **`1`** | Play `0001.mp3` |
| **`2`** | Play `0002.mp3` |
| **`3`** | Play `0003.mp3` |
| **`f`** | Run SD Card File Count Diagnostic |
| **`+`** | Increase Volume (+2) |
| **`-`** | Decrease Volume (-2) |
