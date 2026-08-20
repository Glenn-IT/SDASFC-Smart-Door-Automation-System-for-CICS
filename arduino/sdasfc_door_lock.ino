/*
 * SDASFC — Smart Door Automation System for CICS
 * Hardware Firmware for ESP32 (Production Firmware)
 * 
 * Hardware Components (from note.md):
 * - ESP32 Dev Module (30-pin board layout)
 * - 12V 5A UPS Access Control Power Supply + 12V Backup Battery
 * - LM2596 Buck Converter (Step-down 12V to 5V + 1000uF 16V filter capacitor)
 * - 1-CH 5V Relay Module (Controls 12V Solenoid / Electric Strike / Magnetic Lock)
 * - RFID RC522 (v133) Reader Module (SPI @ 3.3V Logic)
 * - Access Control Infrared Optical Exit Sensor (No-Touch IR)
 * - DS3231 AT24C32 Real-Time Clock Module (I2C)
 * - DFPlayer Mini MP3 Module (MP3-TF-16P) + 3W 8Ω Speaker
 * 
 * ESP32 Pin Connections:
 * - RFID RC522 (SPI - 3.3V Logic ONLY!):
 *   - SS (SDA) -> GPIO 5
 *   - SCK      -> GPIO 18
 *   - MOSI     -> GPIO 23
 *   - MISO     -> GPIO 19
 *   - RST      -> GPIO 4
 *   - 3.3V     -> ESP32 3V3 Pin
 *   - GND      -> Common GND
 * 
 * - DS3231 RTC (I2C):
 *   - SDA      -> GPIO 21
 *   - SCL      -> GPIO 22
 *   - VCC      -> 5V Rail (Buck Converter Output)
 *   - GND      -> Common GND
 * 
 * - DFPlayer Mini (HardwareSerial2):
 *   - Pin 1 (VCC)  -> 5V Rail (Buck Converter Output)
 *   - Pin 2 (RX)   <- [ 1kΩ Resistor ] <- GPIO 17 (ESP32 TX2)
 *   - Pin 3 (TX)   -> [ 1kΩ Resistor ] -> GPIO 16 (ESP32 RX2)
 *   - Pin 6 (SPK1) -> 3W 8Ω Speaker Lead 1 (+)
 *   - Pin 7 (GND)  -> Common GND
 *   - Pin 8 (SPK2) -> 3W 8Ω Speaker Lead 2 (-)
 * 
 * - Relay Module (1-CH 5V):
 *   - IN / SIG -> GPIO 27 (HIGH = Unlocked, LOW = Locked)
 *   - VCC      -> 5V Rail
 *   - GND      -> Common GND
 * 
 * - IR Exit Sensor:
 *   - OUT / NO -> GPIO 33 (Internal Pull-Up enabled)
 *   - COM      -> Common GND
 *   - V+ / GND -> 12V Power Supply +12V / GND
 * 
 * USB Serial Protocol (115200 Baud):
 * - Transmits : "UID:<HEX_UID>" on RFID card tap
 * - Receives  : "GRANT\n" or "DENY\n" from Host PC Serial Bridge
 * - Broadcasts: "EVENT:EXIT_BUTTON", "EVENT:DOOR_UNLOCKED", "EVENT:DOOR_LOCKED"
 */

#include <SPI.h>
#include <Wire.h>
#include <HardwareSerial.h>
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
HardwareSerial mp3Serial(2); // ESP32 HardwareSerial2
DFRobotDFPlayerMini player;
RTC_DS3231 rtc;

// Status Flags & Settings
bool hasDFPlayer = false;
bool hasRTC = false;
int defaultVolume = 25; // Safe audio volume (0 - 30)

// Function Prototypes
void unlockDoor();
void playIdle();
void playGranted();
void playDenied();
void playLocked();
bool initDFPlayer();
String waitForSerialResponse(unsigned long timeoutMs);
void handleDFPlayerEvents();

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(500);
  Serial.println("\n==================================================");
  Serial.println("  SDASFC ESP32 SMART DOOR LOCK CONTROLLER");
  Serial.println("==================================================");

  // 1. Relay Initialization (Default Locked / De-energized)
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);
  Serial.println("[HW] Relay initialized (State: LOCKED).");

  // 2. IR Exit Sensor Initialization (Active LOW)
  pinMode(EXIT_BUTTON, INPUT_PULLUP);
  Serial.println("[HW] IR Exit Sensor initialized on GPIO 33.");

  // 3. Initialize I2C Bus & DS3231 RTC
  Wire.begin(21, 22); // SDA = GPIO 21, SCL = GPIO 22
  if (rtc.begin()) {
    hasRTC = true;
    if (rtc.lostPower()) {
      rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
    }
    DateTime now = rtc.now();
    Serial.printf("[HW] RTC DS3231 Ready: %04d-%02d-%02d %02d:%02d:%02d\n",
                  now.year(), now.month(), now.day(),
                  now.hour(), now.minute(), now.second());
  } else {
    hasRTC = false;
    Serial.println("[HW] RTC DS3231 not found (skipped).");
  }

  // 4. Initialize SPI Bus & RFID Reader (RC522)
  SPI.begin(18, 19, 23, SS_PIN); // SCK = 18, MISO = 19, MOSI = 23, SS = 5
  rfid.PCD_Init();
  Serial.println("[HW] RFID RC522 Reader Ready.");

  // 5. Initialize DFPlayer Mini MP3 Player
  hasDFPlayer = initDFPlayer();

  Serial.println("--------------------------------------------------");
  Serial.println("SYS:READY — Awaiting RFID taps or Exit button events.");
  Serial.println("--------------------------------------------------\n");

  if (hasDFPlayer) {
    playIdle(); // Play 0001.mp3 (System Ready)
  }
}

void loop() {
  // Feed ESP32 Watchdog
  yield();

  // 1. Process DFPlayer real-time events (insert/remove/errors)
  handleDFPlayerEvents();

  //==================================================
  // 2. INFRARED EXIT SENSOR TRIGGER (Wave to Exit)
  //==================================================
  if (digitalRead(EXIT_BUTTON) == LOW) {
    delay(50); // Debounce check
    if (digitalRead(EXIT_BUTTON) == LOW) {
      Serial.println("\n[EVENT] IR Exit Sensor Triggered!");
      playGranted();
      delay(1000); // Allow initial voice prompt to begin
      unlockDoor();

      // Wait until hand is removed from IR sensor
      while (digitalRead(EXIT_BUTTON) == LOW) {
        yield();
        delay(50);
      }
      return;
    }
  }

  //==================================================
  // 3. MANUAL TEST COMMANDS VIA SERIAL (T1, T2, T3, T4, UNLOCK)
  //==================================================
  if (Serial.available() > 0) {
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toUpperCase();

    if (cmd == "T1") {
      Serial.println("[TEST] Playing Track 1 (0001.mp3)...");
      playIdle();
    } else if (cmd == "T2") {
      Serial.println("[TEST] Playing Track 2 (0002.mp3)...");
      playGranted();
    } else if (cmd == "T3") {
      Serial.println("[TEST] Playing Track 3 (0003.mp3)...");
      playDenied();
    } else if (cmd == "T4") {
      Serial.println("[TEST] Playing Track 4 (0004.mp3)...");
      playLocked();
    } else if (cmd == "UNLOCK") {
      Serial.println("[REMOTE] Manual unlock command received!");
      playGranted();
      unlockDoor();
    }
  }

  //==================================================
  // 4. RFID CARD SCANNING
  //==================================================
  if (!rfid.PICC_IsNewCardPresent()) {
    delay(10);
    return;
  }
  if (!rfid.PICC_ReadCardSerial()) {
    delay(10);
    return;
  }

  // Format Card UID string (e.g. "0A 75 B4 02")
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

  // Print RTC timestamp if available
  if (hasRTC) {
    DateTime now = rtc.now();
    Serial.printf("[RTC %04d-%02d-%02d %02d:%02d:%02d] ",
                  now.year(), now.month(), now.day(),
                  now.hour(), now.minute(), now.second());
  }

  // Clear any residual bytes before sending UID
  while (Serial.available() > 0) {
    Serial.read();
  }

  // Transmit UID to Serial Bridge (e.g. "UID:0A 75 B4 02")
  Serial.print("UID:");
  Serial.println(uidStr);

  // Await decision from Host PC (GRANT or DENY) with 3.5s timeout
  String response = waitForSerialResponse(3500);
  Serial.printf("[HOST RESPONSE] '%s'\n", response.c_str());

  if (response == "GRANT") {
    playGranted(); // 0002.mp3 - Access Granted
    unlockDoor();
  } else {
    // DENY or TIMEOUT
    playDenied();  // 0003.mp3 - Access Denied
    delay(2000);   // Allow "Access Denied" voice prompt to finish cleanly
  }

  // Halt RFID Reader
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  delay(400);
}

/**
 * Initialize DFPlayer Mini with non-blocking check & diagnostics
 */
bool initDFPlayer() {
  mp3Serial.begin(9600, SERIAL_8N1, RX2_PIN, TX2_PIN);
  delay(500);
  yield();

  Serial.print("[HW] Detecting DFPlayer Mini... ");
  if (!player.begin(mp3Serial, false, false)) {
    Serial.println("❌ NOT DETECTED (Running in silent mode).");
    return false;
  }

  Serial.println("✅ Module Online!");
  player.volume(defaultVolume);
  delay(200);
  yield();

  int fileCount = player.readFileCounts();
  if (fileCount > 0) {
    Serial.printf("[HW] MicroSD Card OK: %d readable audio file(s) found.\n", fileCount);
  } else if (fileCount == 0) {
    Serial.println("[HW] ⚠️ MicroSD Card mounted, but 0 audio files found (Ensure FAT32 & 0001.mp3).");
  } else {
    Serial.println("[HW] ⚠️ MicroSD Card not detected or read error.");
  }

  return true;
}

/**
 * Handle real-time hardware status events from DFPlayer Mini
 */
void handleDFPlayerEvents() {
  if (!hasDFPlayer) return;

  if (player.available()) {
    uint8_t type = player.readType();
    int value = player.read();

    switch (type) {
      case DFPlayerCardInserted:
        Serial.println("[AUDIO 💳] MicroSD Card Inserted.");
        break;
      case DFPlayerCardRemoved:
        Serial.println("[AUDIO ⚠️] MicroSD Card Removed.");
        break;
      case DFPlayerPlayFinished:
        // Audio finished playing
        break;
      case DFPlayerError:
        Serial.printf("[AUDIO ❌] DFPlayer Error Code: %d\n", value);
        break;
      default:
        break;
    }
  }
}

/**
 * Read line from Serial until newline or timeout
 */
String waitForSerialResponse(unsigned long timeoutMs) {
  unsigned long start = millis();
  String response = "";

  while (millis() - start < timeoutMs) {
    yield();
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

/**
 * Door Unlock Sequence:
 * - Energize Relay (HIGH) for 5 seconds
 * - De-energize Relay (LOW)
 * - Play Door Locked prompt cleanly
 */
void unlockDoor() {
  Serial.println("[DOOR] >>> EVENT:DOOR_UNLOCKED (Relay HIGH) <<<");
  digitalWrite(RELAY_PIN, HIGH);

  delay(5000); // Keep door unlocked for 5 seconds

  digitalWrite(RELAY_PIN, LOW);
  Serial.println("[DOOR] >>> EVENT:DOOR_LOCKED (Relay LOW) <<<");

  playLocked(); // 0004.mp3 - Door Locked
  delay(1500);  // Allow voice prompt to finish playing cleanly
}

// Voice Prompts (Safely guarded by hasDFPlayer flag)
void playIdle() {
  if (hasDFPlayer) player.play(1); // 0001.mp3 - System Ready / Welcome (Startup only)
}

void playGranted() {
  if (hasDFPlayer) player.play(2); // 0002.mp3 - Access Granted
}

void playDenied() {
  if (hasDFPlayer) player.play(3); // 0003.mp3 - Access Denied
}

void playLocked() {
  if (hasDFPlayer) player.play(4); // 0004.mp3 - Door Locked
}


