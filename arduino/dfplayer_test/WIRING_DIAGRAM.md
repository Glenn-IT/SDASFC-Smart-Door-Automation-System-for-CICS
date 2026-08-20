# ESP32 + DFPlayer Mini (MP3-TF-16P) Standalone Wiring Diagram & Setup Guide

This guide explains how to connect your extra **ESP32 Dev Module**, **DFPlayer Mini MP3 Module (MP3-TF-16P)**, and **3W 8Ω Speaker** to test your voice audio prompts (`0001.mp3`, `0002.mp3`, `0003.mp3`) on your MicroSD card.

---

## 📌 Hardware Pin Mapping Table (ESP32 DevKit V1 Board)

### ESP32 DevKit V1 Board Pinout (30-Pin Layout):
- **Left Side Pins:** `VIN`, `GND`, `D13`, `D14`, `D27`, `D26`, `D25`, `D33`, `D32`, `D35`, `D34`, `VN`, `VP`, `EN`
- **Right Side Pins:** `3V3`, `GND`, `D2`, `D4`, `RX2`, `TX2`, `D5`, `D18`, `D19`, `D21`, `RX0`, `TX0`, `D22`, `D23`

| Component | Pin Label | ESP32 DevKit V1 Connection | Notes |
|-----------|-----------|----------------------------|-------|
| **DFPlayer Mini** | **Pin 1 (VCC)** | **External 5V / VIN** | Powered from External 5V Power Supply |
| | **Pin 2 (RX)** | **Pin TX2** | ⚠️ **Connect through 1kΩ Resistor!** (ESP32 DevKit V1 `TX2` pin) |
| | **Pin 3 (TX)** | **Pin RX2** | ⚠️ **Connect through 1kΩ Resistor!** (ESP32 DevKit V1 `RX2` pin) |
| | **Pin 6 (SPK_1)** | **Speaker Lead 1 (+)**| Speaker Output 1 (6th pin on Left) |
| | **Pin 7 (GND)** | **GND** | Connect to ESP32 DevKit V1 `GND` (7th pin on Left or Right) |
| | **Pin 8 (SPK_2)** | **Speaker Lead 2 (-)**| Speaker Output 2 (Bottom Left pin) |
| **MicroSD Card** | MicroSD Slot | Inserted in DFPlayer | Formatted FAT32 (`0001.mp3`, `0002.mp3`, `0003.mp3`) |

---

## ⚡ Power & Resistor Setup Notes for ESP32 DevKit V1

1. **Why 1kΩ Resistors on BOTH RX2 and TX2 Pins?**
   - **ESP32 DevKit V1 Pin `TX2` -> 1kΩ Resistor -> DFPlayer Pin 2 (RX)**: ESP32 outputs 3.3V logic signals. The 1kΩ resistor limits input current into the DFPlayer's 5V logic input and eliminates background static clicking/popping noise.
   - **DFPlayer Pin 3 (TX) -> 1kΩ Resistor -> ESP32 DevKit V1 Pin `RX2`**: When powered at 5V, the DFPlayer TX pin outputs ~5V logic HIGH levels. ESP32 GPIO pins are strictly 3.3V tolerant! Placing a **1kΩ inline resistor** on the `RX2` line acts as a protective current limiter that prevents over-voltage damage to your ESP32 DevKit V1 board.

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
