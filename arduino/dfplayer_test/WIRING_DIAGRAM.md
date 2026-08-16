# ESP32 + DFPlayer Mini (MP3-TF-16P) Standalone Wiring Diagram & Setup Guide

This guide explains how to connect your extra **ESP32 Dev Module**, **DFPlayer Mini MP3 Module (MP3-TF-16P)**, and **3W 8Ω Speaker** to test your voice audio prompts (`0001.mp3`, `0002.mp3`, `0003.mp3`) on your MicroSD card.

---

## 📌 Hardware Pin Mapping Table

### For Board Layout shown in your Image (SPK_1 & SPK_2 on Left Side):

| Component | Pin Label | ESP32 Connection | Notes |
|-----------|-----------|------------------|-------|
| **DFPlayer Mini** | **Pin 1 (VCC)** | **5V / VIN** | Connect to ESP32 5V (or USB 5V rail) |
| | **Pin 2 (RX)** | **GPIO 17 (TX2)** | ⚠️ **MUST put a 1kΩ Resistor in series!** |
| | **Pin 3 (TX)** | **GPIO 16 (RX2)** | Direct connection to ESP32 RX2 |
| | **Pin 6 (SPK_1)** | **Speaker Lead 1 (+)**| Speaker Output 1 (6th pin on Left) |
| | **Pin 7 (GND)** | **GND** | Connect to ESP32 GND (7th pin on Left) |
| | **Pin 8 (SPK_2)** | **Speaker Lead 2 (-)**| Speaker Output 2 (Bottom Left pin) |
| **MicroSD Card** | MicroSD Slot | Inserted in DFPlayer | Formatted FAT32 (`0001.mp3`, `0002.mp3`, `0003.mp3`) |

*Note: If your board has `SPK_1` on Pin 9 (Bottom Right) and `SPK_2` on Pin 11 (Right side), connect speaker to Pin 9 and Pin 11 instead.*

---

## 📐 Visual Schematic & Pinout Diagrams

### 1. Board Layout from your Uploaded Diagram (SPK on Left):
```text
               +---|  |---+
       (VCC) 1 |  v   v   | 16 (BUSY)
        (RX) 2 |          | 15 (USB-)
        (TX) 3 |          | 14 (USB+)
     (DAC_R) 4 | MP3-TF-  | 13 (ADKEY_2)
     (DAC_L) 5 |   16P    | 12 (ADKEY_1)
     (SPK_1) 6 |          | 11 (IO_2)  
       (GND) 7 |          | 10 (GND)
     (SPK_2) 8 |          | 9  (IO_1)
               +----------+
```

### 2. Standard DFPlayer Mini Variant (SPK on Right):
```text
               +---|  |---+
       (VCC) 1 |  v   v   | 16 (BUSY)
        (RX) 2 |          | 15 (USB-)
        (TX) 3 |          | 14 (USB+)
     (DAC_R) 4 | DFPlayer | 13 (ADKEY2)
     (DAC_L) 5 |   Mini   | 12 (ADKEY1)
    (LINE_R) 6 |          | 11 (SPK2)  ---> Speaker (-)
       (GND) 7 |          | 10 (GND)
    (LINE_L) 8 |          | 9  (SPK1)  ---> Speaker (+)
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
