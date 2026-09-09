/*
 * SDASFC — Smart Door Automation System for CICS
 * Hardware Firmware for ESP32 (Production Firmware v3.0 - WiFi + Serial Hybrid + Offline Master Key)
 * 
 * Hardware Components:
 * - ESP32 Dev Module (30-pin board layout)
 * - 12V 5A UPS Access Control Power Supply + 12V Backup Battery
 * - LM2596 Buck Converter (Step-down 12V to 5V + 1000uF 16V filter capacitor)
 * - 1-CH 5V Relay Module (Controls 12V Solenoid / Electric Strike / Magnetic Lock)
 * - RFID RC522 (v133) Reader Module (SPI @ 3.3V Logic)
 * - Access Control Infrared Optical Exit Sensor (No-Touch IR)
 * - DS3231 AT24C32 Real-Time Clock Module (I2C)
 * - DFPlayer Mini MP3 Module (MP3-TF-16P) + 3W 8Ω Speaker
 * 
 * MicroSD Audio Track Mapping:
 * - Track 2 (0002.mp3): "Access granted you may now open the door. Welcome to the CICS laboratory"
 * - Track 1 (0001.mp3): "Access Denied"
 * 
 * Master Emergency Key (Brownout / Offline Hardware Bypass):
 * - UID: "93 39 6E 1B" (Works 100% offline, during power failure/brownouts, or server downtime)
 * 
 * Network Operation:
 * - Local Wi-Fi Direct: Sends HTTP POST directly to Host Laptop XAMPP API
 * - USB Serial Fallback: Bridges via serial_bridge.ps1 / .py / .php if Wi-Fi is unavailable
 * - Relay stays locked during power brownout unless Master Key Card is tapped
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <Wire.h>
#include <HardwareSerial.h>
#include <MFRC522.h>
#include <DFRobotDFPlayerMini.h>
#include <RTClib.h> // Adafruit RTClib for DS3231

//==================================================
// 1. Wi-Fi & Local Server Configuration
//==================================================
// Replace with your router's Wi-Fi credentials
const char* WIFI_SSID     = "PLDT_Home_1D000";
const char* WIFI_PASSWORD = "pldthome";

// Local Laptop IP running XAMPP Apache/MySQL (Presentation Router IP: 192.168.1.208)
const char* API_URL       = "http://192.168.1.208/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php";

// Wi-Fi Connection Settings
const unsigned long WIFI_TIMEOUT_MS = 8000; // 8 seconds timeout for Wi-Fi association on boot
bool wifiEnabled = false;

//==================================================
// 2. Master Emergency Key Card (Offline Brownout Bypass)
//==================================================
const String MASTER_CARD_UID          = "93 39 6E 1B";
const String MASTER_CARD_UID_NO_SPACE = "93396E1B";

//==================================================
// 3. Pin Definitions (ESP32 Dev Module)
//==================================================
#define SS_PIN       5
#define RST_PIN      4
#define RELAY_PIN    27
#define EXIT_BUTTON  33
#define RX2_PIN      16
#define TX2_PIN      17
#define SERIAL_BAUD  115200

// Relay Trigger Mode
#define RELAY_ON     HIGH
#define RELAY_OFF    LOW
#define UNLOCK_HOLD_MS 6000 // Door stays unlocked for 6 seconds

// Hardware Objects
MFRC522 rfid(SS_PIN, RST_PIN);
HardwareSerial mp3Serial(2); // ESP32 HardwareSerial2
DFRobotDFPlayerMini player;
RTC_DS3231 rtc;

// Status Flags & Settings
bool hasDFPlayer = false;
bool hasRTC = false;
int defaultVolume = 30; // Maximum audio volume (0 - 30)

// Function Prototypes
void unlockDoorAndPrompt();
void playGranted();
void playDenied();
bool initDFPlayer();
String verifyViaWiFi(String uid);
String waitForSerialResponse(unsigned long timeoutMs);
void handleDFPlayerEvents();
void connectToWiFi();

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(500);
  Serial.println("\n==================================================");
  Serial.println("  SDASFC ESP32 SMART DOOR LOCK CONTROLLER (v3.0)");
  Serial.println("  Wi-Fi + Serial Hybrid & Offline Master Key Ready");
  Serial.println("==================================================");

  // 1. Relay Initialization (Default Locked / De-energized for Brownout Safety)
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, RELAY_OFF);
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

  // 6. Connect to Wi-Fi Network
  connectToWiFi();

  Serial.println("--------------------------------------------------");
  Serial.printf("MASTER KEY CONFIGURED : '%s'\n", MASTER_CARD_UID.c_str());
  Serial.println("SYS:READY — Awaiting RFID taps or Exit button events.");
  Serial.println("--------------------------------------------------\n");
}

void loop() {
  // Feed ESP32 Watchdog
  yield();

  // 1. Process DFPlayer real-time events
  handleDFPlayerEvents();

  //==================================================
  // 2. INFRARED EXIT SENSOR TRIGGER (Wave to Exit)
  //==================================================
  if (digitalRead(EXIT_BUTTON) == LOW) {
    delay(40); // Debounce check
    if (digitalRead(EXIT_BUTTON) == LOW) {
      Serial.println("\n[EVENT:EXIT_BUTTON] IR Exit Sensor Triggered!");
      unlockDoorAndPrompt();

      // Wait until hand is removed from IR sensor
      unsigned long exitWait = millis();
      while (digitalRead(EXIT_BUTTON) == LOW && (millis() - exitWait < 3000)) {
        yield();
        delay(50);
      }
      rfid.PCD_Init();
      return;
    }
  }

  //==================================================
  // 3. MANUAL TEST COMMANDS VIA SERIAL (T1, T2, UNLOCK)
  //==================================================
  if (Serial.available() > 0) {
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toUpperCase();

    if (cmd == "T2" || cmd == "GRANT") {
      Serial.println("[TEST] Playing Track 2 (Access Granted & Welcome)...");
      playGranted();
    } else if (cmd == "T1" || cmd == "DENY") {
      Serial.println("[TEST] Playing Track 1 (Access Denied)...");
      playDenied();
    } else if (cmd == "UNLOCK") {
      Serial.println("[REMOTE] Manual unlock command received!");
      unlockDoorAndPrompt();
    }
  }

  //==================================================
  // 4. RFID CARD SCANNING
  //==================================================
  if (!rfid.PICC_IsNewCardPresent()) {
    delay(15);
    return;
  }
  if (!rfid.PICC_ReadCardSerial()) {
    delay(15);
    return;
  }

  // Format Card UID string (e.g. "93 39 6E 1B")
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

  Serial.printf("\n[CARD DETECTED] UID: %s\n", uidStr.c_str());

  //==================================================
  // 5. MASTER EMERGENCY KEY CHECK (Hardware-level Bypass)
  //==================================================
  String uidNoSpace = uidStr;
  uidNoSpace.replace(" ", "");

  if (uidStr == MASTER_CARD_UID || uidNoSpace == MASTER_CARD_UID_NO_SPACE) {
    Serial.println("==================================================");
    Serial.println("🔑 [EMERGENCY MASTER KEY] Verified Locally on ESP32!");
    Serial.println("⚡ [BROWNOUT BYPASS] Unlocking Door Immediately...");
    Serial.println("==================================================");

    // Unlocks door immediately without depending on server or Wi-Fi
    unlockDoorAndPrompt();

    // If Wi-Fi is connected, log access event to server in background
    if (WiFi.status() == WL_CONNECTED) {
      verifyViaWiFi(uidStr);
    }

    rfid.PICC_HaltA();
    delay(200);
    return;
  }

  //==================================================
  // 6. STANDARD ACCESS DECISION (Wi-Fi or Serial)
  //==================================================
  String response = "";
  bool evaluatedViaWiFi = false;

  // Option A: Direct Wi-Fi HTTP API Request
  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("[WIFI] Querying Web API (%s)...\n", API_URL);
    response = verifyViaWiFi(uidStr);
    evaluatedViaWiFi = true;
    Serial.printf("[WIFI RESPONSE] '%s'\n", response.c_str());
  }

  // Option B: Fallback to USB Serial Bridge (if Wi-Fi is down / offline)
  if (!evaluatedViaWiFi || response == "DISCONNECTED" || response == "ERROR") {
    Serial.println("[SERIAL BRIDGE] Transmitting UID over USB Serial...");
    while (Serial.available() > 0) {
      Serial.read();
    }
    Serial.print("UID:");
    Serial.println(uidStr);
    Serial.flush();

    response = waitForSerialResponse(3000);
    Serial.printf("[SERIAL RESPONSE] '%s'\n", response.c_str());
  }

  // Execute Decision
  if (response == "GRANT") {
    Serial.println("[ACCESS] ✅ GRANTED - Unlocking Door Immediately!");
    unlockDoorAndPrompt();
  } else if (response == "DENY") {
    Serial.println("[ACCESS] ❌ DENIED - Card Unauthorized or Inactive.");
    playDenied();
    delay(2000);
  } else {
    // Brownout / Offline safety: If server is unreachable, door stays safely locked!
    Serial.println("[ACCESS] ⚠️ OFFLINE / TIMEOUT - Door remains LOCKED for security.");
    playDenied();
    delay(2000);
  }

  // Safely halt card and refresh RFID antenna state
  rfid.PICC_HaltA();
  delay(150);
}

/**
 * Connect to Local Router Wi-Fi
 */
void connectToWiFi() {
  if (String(WIFI_SSID) == "YOUR_WIFI_SSID") {
    Serial.println("[WIFI] Wi-Fi credentials not set (Running in USB Serial mode).");
    wifiEnabled = false;
    return;
  }

  Serial.printf("[WIFI] Connecting to SSID: '%s'...\n", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  unsigned long startAttempt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAttempt < WIFI_TIMEOUT_MS) {
    delay(300);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    wifiEnabled = true;
    Serial.println("✅ [WIFI] Connected successfully!");
    Serial.printf("   ESP32 Local IP : %s\n", WiFi.localIP().toString().c_str());
    Serial.printf("   Target API URL : %s\n", API_URL);
  } else {
    wifiEnabled = false;
    Serial.println("⚠️ [WIFI] Connection failed/timed out. Operating in USB Serial Bridge fallback mode.");
  }
}

/**
 * Send HTTP POST to Web API directly over Wi-Fi
 */
String verifyViaWiFi(String uid) {
  if (WiFi.status() != WL_CONNECTED) {
    return "DISCONNECTED";
  }

  HTTPClient http;
  http.begin(API_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("User-Agent", "SDASFC-ESP32-WiFi/3.0");
  http.setTimeout(3500);

  String payload = "{\"rfid_uid\":\"" + uid + "\"}";
  int httpCode = http.POST(payload);

  if (httpCode == 200 || httpCode == HTTP_CODE_OK) {
    String responseBody = http.getString();
    http.end();
    if (responseBody.indexOf("\"access\":\"granted\"") >= 0) {
      return "GRANT";
    } else {
      return "DENY";
    }
  } else {
    Serial.printf("[WIFI HTTP ERROR] Status Code: %d\n", httpCode);
    http.end();
    return "ERROR";
  }
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
  player.volume(defaultVolume); // Set to 30 (Maximum volume)
  delay(200);
  yield();

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
 * Read response from Serial stream (instant substring GRANT/DENY detection)
 */
String waitForSerialResponse(unsigned long timeoutMs) {
  unsigned long start = millis();
  String buffer = "";

  while (millis() - start < timeoutMs) {
    yield();
    while (Serial.available() > 0) {
      char c = (char)Serial.read();
      buffer += c;

      if (buffer.indexOf("GRANT") >= 0) {
        return "GRANT";
      }
      if (buffer.indexOf("DENY") >= 0) {
        return "DENY";
      }
    }
    delay(10);
  }

  return "TIMEOUT";
}

/**
 * Immediate Door Unlock & Voice Prompt Sequence:
 * 1. Energizes Relay IMMEDIATELY (Door unlocks on millisecond 0)
 * 2. Prompts voice ("Access granted you may now open the door. Welcome to the CICS laboratory")
 * 3. Holds door unlocked for 6 full seconds
 * 4. De-energizes Relay (Locked)
 * 5. Refreshes RFID antenna state
 */
void unlockDoorAndPrompt() {
  Serial.println("[DOOR] >>> 🔓 RELAY ENERGIZED: Door is UNLOCKED <<<");
  digitalWrite(RELAY_PIN, RELAY_ON);

  // Play Access Granted voice prompt while door is already unlocked
  playGranted();

  // Hold door unlocked for 6 seconds
  delay(UNLOCK_HOLD_MS);

  digitalWrite(RELAY_PIN, RELAY_OFF);
  Serial.println("[DOOR] >>> 🔒 RELAY DE-ENERGIZED: Door is LOCKED <<<");

  // Re-initialize RFID antenna after relay de-energizes
  rfid.PCD_Init();
}

/**
 * Play Access Granted & Welcome:
 * Plays Track 2 ("Access granted you may now open the door. Welcome to the CICS laboratory")
 */
void playGranted() {
  if (!hasDFPlayer) return;
  Serial.println("[AUDIO] Playing: Access Granted & Welcome (Track 2) @ Volume 30");
  player.play(2);
}

/**
 * Play Access Denied:
 * Plays Track 1 ("Access Denied")
 */
void playDenied() {
  if (!hasDFPlayer) return;
  Serial.println("[AUDIO] Playing: Access Denied (Track 1) @ Volume 30");
  player.play(1);
}
