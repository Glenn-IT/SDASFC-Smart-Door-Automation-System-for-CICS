# ESP32 + DFPlayer Mini (MP3-TF-16P) Standalone Wiring Diagram & Setup Guide

This guide explains how to connect your extra **ESP32 Dev Module**, **DFPlayer Mini MP3 Module (MP3-TF-16P)**, and **3W 8Ω Speaker** to test your voice audio prompts (`0001.mp3`, `0002.mp3`, `0003.mp3`) on your MicroSD card.

---

## 📌 Hardware Pin Mapping Table

| Component | Pin Label | ESP32 Connection | Notes |
|-----------|-----------|------------------|-------|
| **DFPlayer Mini** | **Pin 1 (VCC)** | **5V / VIN** | Connect to ESP32 5V (or USB 5V rail) |
| | **Pin 2 (RX)** | **GPIO 17 (TX2)** | ⚠️ **MUST put a 1kΩ Resistor in series!** |
| | **Pin 3 (TX)** | **GPIO 16 (RX2)** | Direct connection to ESP32 RX2 |
| | **Pin 7 (GND)** | **GND** | Connect to ESP32 GND |
| | **Pin 9 (SPK_1)** | **Speaker Positive (+)** | To 3W 8Ω Speaker Lead 1 |
| | **Pin 11 (SPK_2)**| **Speaker Negative (-)** | To 3W 8Ω Speaker Lead 2 |
| **MicroSD Card** | MicroSD Slot | Inserted in DFPlayer | Formatted FAT32 (`0001.mp3`, `0002.mp3`, `0003.mp3`) |

---

## 📐 Visual Schematic & Pinout Diagram

```text
              ESP32 Dev Module                         DFPlayer Mini Pinout
          +-----------------------+                   +--------------------+
          |                       |                   |  [ MicroSD Slot ]  |
          |                  VIN  |===================| Pin 1  (VCC)       |
          |                  GND  |===================| Pin 7  (GND)       |
          |                       |                   |                    |
          |          GPIO 17(TX2) |---[ 1kΩ Resistor ]| Pin 2  (RX)        |
          |          GPIO 16(RX2) |<------------------| Pin 3  (TX)        |
          |                       |                   |                    |
          |                       |                   | Pin 9  (SPK1) ----+-- (Speaker +)
          |                       |                   | Pin 11 (SPK2) ----+-- (Speaker -)
          +-----------------------+                   +--------------------+
```

### DFPlayer Mini Physical Pin Diagram (Top View, Notch Up):

```text
               +---|  |---+
       (VCC) 1 |  v   v   | 16 (BUSY)
        (RX) 2 |          | 15 (USB-)
        (TX) 3 |          | 14 (USB+)
       (DAC_)4 | DFPlayer | 13 (ADKEY2)
       (DAC_)5 |   Mini   | 12 (ADKEY1)
     (LINE_R)6 |          | 11 (SPK2)  ---> Speaker (-)
       (GND) 7 |          | 10 (GND)
     (LINE_L)8 |          | 9  (SPK1)  ---> Speaker (+)
               +----------+
```

---

## ⚡ Power & Resistor Setup Notes

1. **Why the 1kΩ Resistor?**
   - ESP32 uses 3.3V logic signals on its TX pin (GPIO 17), whereas DFPlayer Mini operates on 5V logic.
   - Placing a **1kΩ inline resistor** between ESP32 GPIO 17 (TX2) and DFPlayer Pin 2 (RX) protects the RX pin and eliminates background clicking/static noise from the speaker.

2. **Powering DFPlayer Mini**:
   - Connect DFPlayer Pin 1 (VCC) to the **5V / VIN** pin of the ESP32 while powered via USB cable.

---

## 🎵 MicroSD Card Files Checklist

Ensure your MicroSD card has FAT32 format and contains these files in the root folder:

```text
MicroSD Card/
├── 0001.mp3   <-- System Ready / Welcome prompt
├── 0002.mp3   <-- Access Granted prompt
└── 0003.mp3   <-- Access Denied prompt
```

---

## 🚀 How to Run the Test

1. Wire up the ESP32, DFPlayer Mini, 1kΩ resistor, and Speaker as shown above.
2. Open [`arduino/dfplayer_test/dfplayer_test.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/dfplayer_test/dfplayer_test.ino) in Arduino IDE.
3. Select Board `ESP32 Dev Module` and your COM port.
4. Upload the sketch.
5. Open **Serial Monitor** (`Ctrl + Shift + M`) at **`115200 baud`**.
6. On startup, track 1 (`0001.mp3`) will automatically play!
7. Type in Serial Monitor to test:
   - Type **`1`** + Enter -> Plays `0001.mp3`
   - Type **`2`** + Enter -> Plays `0002.mp3`
   - Type **`3`** + Enter -> Plays `0003.mp3`
   - Type **`+`** + Enter -> Volume Up
   - Type **`-`** + Enter -> Volume Down
