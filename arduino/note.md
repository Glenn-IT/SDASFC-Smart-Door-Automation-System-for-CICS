# SDASFC Hardware Breadboard & Wiring Map

## 1. Complete Physical Wiring Table

| FROM Component / Source | Breadboard Row / Junction | TO Target / ESP32 Pin | Notes & Function |
| :--- | :--- | :--- | :--- |
| **Power Supply (12V/5A UPS)** - GND | — | Magnetic Lock - GND | 12V Common Return |
| **Magnetic Lock** - VCC | — | Relay - NO (Normally Open) | Switched 12V Power |
| **Relay** - COM (Public end) | — | Power Supply - +12V | 12V Constant Input |
| **Relay** - VCC | G1 / Row 50 | 5V Rail (Buck Converter) | 5V Power for Relay Coil |
| **Relay** - GND | G1 / Row 33 | Common GND Rail | Ground |
| **Relay** - IN | — | **ESP32 - GPIO 27** | Door Unlock Control (HIGH = Unlock) |
| **Exit Module (IR No-Touch)** - V+ | — | Power Supply - +12V | 12V Sensor Supply |
| **Exit Module** - GND | — | Power Supply - GND | Sensor Ground |
| **Exit Module** - COM | G1 / Row 30 | Common GND Rail | Ground reference |
| **Exit Module** - NO | — | **ESP32 - GPIO 33** | Wave to Exit (Active LOW / Internal Pull-Up) |
| **Buck Converter (LM2596)** - VIN (+) | — | Power Supply - +12V | 12V Input to Step-Down |
| **Buck Converter** - VIN (-) | — | Power Supply - GND | 12V Return |
| **Buck Converter** - VOUT (+) [5V] | G1 / Row 58 | 5V Main Power Rail | Regulated 5.0V Output |
| **Buck Converter** - VOUT (-) [GND] | G1 / Row 30 | Main Common GND Rail | Regulated GND Return |
| G1 / Row 58 (5V Rail) | G1 / Row 50 | ESP32 - 5V (VIN) / Relay / RTC / DFPlayer | 5V Distribution Bus |
| G1 / Row 30 (GND Rail) | G1 / Row 33 | G1 / Row 40 / ESP32 GND / Power GND | Common Ground Distribution Bus |
| **Capacitor (1000µF 16V)** - Positive | G2 / Row 5 | G1 / Row 58 (5V Rail) | 5V Power Rail Smoothing & Decoupling |
| **Capacitor (1000µF 16V)** - Negative | G2 / Row 10 | G1 / Row 30 (GND Rail) | Filter Ground Return |
| **DS3231 RTC Module** - GND | G1 / Row 40 | Common GND Rail | Ground |
| **DS3231 RTC Module** - VCC | G1 / Row 50 | 5V Rail | 5V Power |
| **DS3231 RTC Module** - SDA | G2 / Row 30 | **ESP32 - GPIO 21** | I2C Data Line |
| **DS3231 RTC Module** - SCL | G2 / Row 28 | **ESP32 - GPIO 22** | I2C Clock Line |
| **ESP32 Dev Module** - 5V (VIN) | G1 / Row 50 | 5V Rail | Main 5V Power from Buck Converter |
| **ESP32 Dev Module** - GND | G1 / Row 40 | Common GND Rail | ESP32 Ground |
| **RFID-RC522 (v133)** - 3.3V | — | **ESP32 - 3V3 Pin** | ⚠️ **3.3V Logic ONLY! (Do NOT connect to 5V)** |
| **RFID-RC522** - GND | G1 / Row 40 | Common GND Rail | Ground |
| **RFID-RC522** - SDA (SS) | — | **ESP32 - GPIO 5** | SPI Chip Select |
| **RFID-RC522** - SCK | — | **ESP32 - GPIO 18** | SPI Clock |
| **RFID-RC522** - MOSI | — | **ESP32 - GPIO 23** | SPI Master Out (Standard VSPI MOSI) |
| **RFID-RC522** - MISO | — | **ESP32 - GPIO 19** | SPI Master In |
| **RFID-RC522** - RST | — | **ESP32 - GPIO 4** | Reset Line |
| **DFPlayer Mini (MP3-TF-16P)** - Pin 1 (VCC) | G1 / Row 58 or 50 | 5V Rail (Buck Converter) | 5V Audio Module Power |
| **DFPlayer Mini** - Pin 2 (RX) | Intermediate Row | **ESP32 - GPIO 17 (TX2)** | ⚠️ **Via 1kΩ Resistor** (Noise reduction) |
| **DFPlayer Mini** - Pin 3 (TX) | Intermediate Row | **ESP32 - GPIO 16 (RX2)** | ⚠️ **Via 1kΩ Resistor** (3.3V logic protection) |
| **DFPlayer Mini** - Pin 6 (SPK1) | Direct Connector | **3W 8Ω Speaker Lead 1 (+)** | Audio Output Channel 1 |
| **DFPlayer Mini** - Pin 7 (GND) | G1 / Row 30 / 33 / 40 | Common GND Rail | Audio Ground Return |
| **DFPlayer Mini** - Pin 8 (SPK2) | Direct Connector | **3W 8Ω Speaker Lead 2 (-)** | Audio Output Channel 2 |

---

## 2. MicroSD Card Audio Tracks Setup

Format a MicroSD card ($\le$ 32GB) to **FAT32 (MBR)** and place the following files in root:
- `0001.mp3` : System Ready / Welcome prompt
- `0002.mp3` : Access Granted prompt (triggers on valid RFID or IR Exit wave)
- `0003.mp3` : Access Denied prompt (triggers on invalid RFID or timeout)
- `0004.mp3` : Door Locked prompt (optional confirmation sound)