/*
 * SDASFC — Smart Door Automation System for CICS
 * Hardware Firmware for ESP32
 * 
 * Hardware Components (from note.md):
 * - ESP32 Dev Module
 * - 12V 5A UPS Access Control Power Supply + LM2596 Buck Converter (5V to ESP32/Relay)
 * - 12V Backup Battery
 * - 1-CH 5V Relay Module (Solenoid / Electric Strike Lock)
 * - RFID RC522 (v133) Reader (SPI)
 * - Infrared Sensor Exit Button (No-Touch IR Optical Exit Sensor)
 * - DS3231 AT24C32 RTC Module (I2C)
 * - DFPlayer Mini + 3W 8Ω Speaker (Optional / Auto-detected when attached)
 * 
 * ESP32 Pin Connections:
 * - RFID RC522 (SPI):
 *   - SS (SDA) -> GPIO 5
 *   - RST      -> GPIO 4
 *   - SCK      -> GPIO 18
 *   - MOSI     -> GPIO 23
 *   - MISO     -> GPIO 19
 * - DS3231 RTC (I2C):
 *   - SDA      -> GPIO 21
 *   - SCL      -> GPIO 22
 * - DFPlayer Mini (HardwareSerial2 - Optional):
 *   - TX       -> GPIO 16 (ESP32 RX2)
 *   - RX       -> GPIO 17 (ESP32 TX2 via 1kΩ resistor)
 *   - VCC      -> 5V / VIN
 *   - GND      -> GND
 * - Relay Module:
 *   - IN / SIG -> GPIO 27 (HIGH = Unlocked, LOW = Locked)
 * - IR Exit Sensor:
 *   - OUT      -> GPIO 33 (Internal Pull-Up enabled)
 * 
 * USB Serial Protocol (115200 Baud):
 * - Transmits: "UID:<HEX_UID>" on RFID tap
 * - Receives : "GRANT\n" or "DENY\n" from host PC serial bridge
 */

#include <SPI.h>
#include <Wire.h>
#include <MFRC522.h>
#include <DFRobotDFPlayerMini.h>
#include <RTClib.h> // Adafruit RTClib for DS3231

// Pin Definitions
#define SS_PIN       5
#define RST_PIN      4
#define RELAY_PIN    27
#define EXIT_BUTTON  33
#define RX2_PIN      16
#define TX2_PIN      17
#define SERIAL_BAUD  115200

// Hardware Objects
MFRC522 rfid(SS_PIN, RST_PIN);
HardwareSerial mp3Serial(2);
DFRobotDFPlayerMini player;
RTC_DS3231 rtc;

// Status Flags
bool hasDFPlayer = false;
bool hasRTC = false;

// Function Prototypes
void unlockDoor();
void playIdle();
void playGranted();
void playDenied();
void playLocked();
String waitForSerialResponse(unsigned long timeoutMs);

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(500);
  Serial.println("\n=================================");
  Serial.println(" SDASFC ESP32 Door Lock Controller");
  Serial.println("=================================");

  // 1. Relay Initialization (Default Locked)
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);

  // 2. IR Exit Sensor Initialization
  pinMode(EXIT_BUTTON, INPUT_PULLUP);

  // 3. Initialize I2C Wire & DS3231 RTC
  Wire.begin(21, 22); // SDA = GPIO 21, SCL = GPIO 22
  if (rtc.begin()) {
    hasRTC = true;
    if (rtc.lostPower()) {
      // RTC lost power, set to compile date/time
      rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
    }
    DateTime now = rtc.now();
    Serial.printf("[HW] RTC DS3231 Ready: %04d-%02d-%02d %02d:%02d:%02d\n",
                  now.year(), now.month(), now.day(),
                  now.hour(), now.minute(), now.second());
  } else {
    Serial.println("[HW] RTC DS3231 not found (skipped).");
  }

  // 4. Initialize SPI & RFID Reader
  SPI.begin(18, 19, 23, SS_PIN); // SCK, MISO, MOSI, SS
  rfid.PCD_Init();
  Serial.println("[HW] RFID RC522 Ready.");

  // 5. Initialize DFPlayer Mini (Non-blocking check)
  mp3Serial.begin(9600, SERIAL_8N1, RX2_PIN, TX2_PIN);
  delay(300);
  
  if (player.begin(mp3Serial, false, false)) { // Fast init without blocking timeout
    hasDFPlayer = true;
    player.volume(25);
    delay(200);
    playIdle();
    Serial.println("[HW] DFPlayer Mini & Speaker Ready.");
  } else {
    hasDFPlayer = false;
    Serial.println("[HW] DFPlayer Mini NOT detected (System running without audio prompts).");
  }

  Serial.println("SYS:READY");
}

void loop() {
  //========================
  // 1. INFRARED EXIT SENSOR
  //========================
  if (digitalRead(EXIT_BUTTON) == LOW) {
    delay(50); // Debounce check for IR sensor
    if (digitalRead(EXIT_BUTTON) == LOW) {
      Serial.println("EVENT:EXIT_BUTTON");
      playGranted();
      delay(1000);
      unlockDoor();

      // Wait until IR sensor output clears
      while (digitalRead(EXIT_BUTTON) == LOW) {
        delay(50);
      }
      return;
    }
  }

  //========================
  // 2. RFID SCAN CHECK
  //========================
  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }
  if (!rfid.PICC_ReadCardSerial()) {
    return;
  }

  // Format UID string (e.g. "0A 75 B4 02")
  String uidStr = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) {
      uidStr += "0";
    }
    uidStr += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) {
      uidStr += " ";
    }
  }
  uidStr.toUpperCase();

  // Print optional RTC timestamp to Serial log
  if (hasRTC) {
    DateTime now = rtc.now();
    Serial.printf("[RTC TIME] %04d-%02d-%02d %02d:%02d:%02d | ",
                  now.year(), now.month(), now.day(),
                  now.hour(), now.minute(), now.second());
  }

  // Transmit UID to Host Serial Bridge
  Serial.print("UID:");
  Serial.println(uidStr);

  // Await decision from Host PC (GRANT or DENY) with 3.5s timeout
  String response = waitForSerialResponse(3500);

  if (response == "GRANT") {
    playGranted();
    delay(1000);
    unlockDoor();
  } else {
    // DENY or Timeout
    playDenied();
    delay(2000);
    playIdle();
  }

  // Halt RFID Reader
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  delay(500);
}

// Read line from Serial until newline or timeout
String waitForSerialResponse(unsigned long timeoutMs) {
  unsigned long start = millis();
  String response = "";

  while (millis() - start < timeoutMs) {
    if (Serial.available() > 0) {
      char c = Serial.read();
      if (c == '\n' || c == '\r') {
        response.trim();
        if (response.length() > 0) {
          return response;
        }
      } else {
        response += c;
      }
    }
  }

  return "TIMEOUT";
}

// Unlock door routine
void unlockDoor() {
  Serial.println("EVENT:DOOR_UNLOCKED");

  // Energize Relay (Unlock)
  digitalWrite(RELAY_PIN, HIGH);

  delay(5000); // Keep unlocked for 5 seconds

  // De-energize Relay (Lock)
  digitalWrite(RELAY_PIN, LOW);
  Serial.println("EVENT:DOOR_LOCKED");

  playIdle(); // Return to idle prompt
}

// Voice Prompts (Safely guarded by hasDFPlayer flag)
void playIdle() {
  if (hasDFPlayer) player.play(1); // 0001.mp3 - System Ready / Idle
}

// 0002.mp3 - Access Granted
void playGranted() {
  if (hasDFPlayer) player.play(2);
}

// 0003.mp3 - Access Denied
void playDenied() {
  if (hasDFPlayer) player.play(3);
}

