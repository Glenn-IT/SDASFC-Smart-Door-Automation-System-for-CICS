/*
 * SDASFC — Smart Door Automation System for CICS
 * Hardware Firmware for ESP32 (Hybrid Production Firmware v3.0)
 * 
 * ============================================================================
 * HYBRID OPERATION MODES (USB Tethered, Standalone Offline, & Optional Wi-Fi):
 * ============================================================================
 * 1. STANDALONE OFFLINE MODE (No USB, No Wi-Fi):
 *    - All registered card UIDs are saved in ESP32 Non-Volatile Flash (Preferences/NVS).
 *    - When the USB cord is unplugged, the ESP32 checks its internal flash memory.
 *    - If the tapped card is registered, the door unlocks IMMEDIATELY and plays voice prompt.
 * 
 * 2. USB SERIAL BRIDGE MODE:
 *    - Connected to PC via USB running serial_bridge (PowerShell, Python, or PHP).
 *    - Transmits "UID:<HEX_UID>" and awaits live server decision.
 *    - Automatically synchronizes the entire database whitelist to ESP32 Flash memory on connect!
 * 
 * 3. OPTIONAL DIRECT WI-FI MODE:
 *    - If configured, ESP32 joins your local Wi-Fi and communicates directly with XAMPP API.
 * 
 * Hardware Components:
 * - ESP32 Dev Module (30-pin)
 * - 12V 5A UPS Access Control Power Supply + 12V Backup Battery
 * - LM2596 Buck Converter (12V to 5V Step-Down + 1000uF Filter Capacitor)
 * - 1-CH 5V Relay Module (Controls 12V Solenoid / Maglock)
 * - RFID RC522 Reader Module (SPI @ 3.3V Logic)
 * - Access Control Infrared Optical Exit Sensor (No-Touch IR)
 * - DS3231 AT24C32 Real-Time Clock Module (I2C)
 * - DFPlayer Mini MP3 Module + 3W 8Ω Speaker
 * 
 * MicroSD Audio Tracks:
 * - Track 2 (0002.mp3): "Access granted you may now open the door. Welcome to the CICS laboratory"
 * - Track 1 (0001.mp3): "Access Denied"
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
 *   - VCC      -> 5V Rail (Buck Converter Output)
 *   - RX       <- [ 1kΩ Resistor ] <- GPIO 17 (ESP32 TX2)
 *   - TX       -> [ 1kΩ Resistor ] -> GPIO 16 (ESP32 RX2)
 *   - SPK1/SPK2-> 3W 8Ω Speaker
 *   - GND      -> Common GND
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
 */

#include <SPI.h>
#include <Wire.h>
#include <HardwareSerial.h>
#include <MFRC522.h>
#include <DFRobotDFPlayerMini.h>
#include <RTClib.h>
#include <Preferences.h>
#include <WiFi.h>
#include <HTTPClient.h>

// ============================================================================
// OPTIONAL WI-FI CONFIGURATION (Leave SSID blank if using pure USB / Offline)
// ============================================================================
const char* WIFI_SSID     = "";  // e.g. "Your_WiFi_SSID"
const char* WIFI_PASSWORD = "";  // e.g. "Your_WiFi_Password"
const char* SERVER_HOST   = "http://192.168.1.100/SDASFC-Smart-Door-Automation-System-for-CICS"; // Local PC XAMPP URL

// ============================================================================
// HARDCODED FALLBACK WHITELIST (Always authorized, even if flash is empty)
// ============================================================================
const char* FALLBACK_CARDS[] = {
  "08 E9 1E 26",  // Admin / Master Card 1
  "C6 85 C6 01"   // Master Card 2
};
const int NUM_FALLBACK = sizeof(FALLBACK_CARDS) / sizeof(FALLBACK_CARDS[0]);

// Pin Definitions
#define SS_PIN       5
#define RST_PIN      4
#define RELAY_PIN    27
#define EXIT_BUTTON  33
#define RX2_PIN      16
#define TX2_PIN      17
#define SERIAL_BAUD  115200

// Relay Settings
#define RELAY_ON       HIGH
#define RELAY_OFF      LOW
#define UNLOCK_HOLD_MS 6000 // Door stays unlocked for 6 seconds

// Max Offline Whitelist Storage
#define MAX_WHITELIST_ENTRIES 100

// Hardware & Storage Objects
MFRC522 rfid(SS_PIN, RST_PIN);
HardwareSerial mp3Serial(2);
DFRobotDFPlayerMini player;
RTC_DS3231 rtc;
Preferences prefs;

// Status Flags & Runtime Variables
bool hasDFPlayer = false;
bool hasRTC = false;
bool wifiEnabled = false;
int defaultVolume = 25; // Safe audio volume (0 - 30)

// In-Memory Whitelist Cache
String whitelist[MAX_WHITELIST_ENTRIES];
int whitelistCount = 0;
unsigned long lastWifiSyncTime = 0;
const unsigned long WIFI_SYNC_INTERVAL = 300000; // 5 minutes

// Function Prototypes
void loadWhitelistFromNVS();
void saveWhitelistToNVS();
bool isCardAuthorized(String uid);
void addCardToWhitelist(String uid);
void removeCardFromWhitelist(String uid);
void parseWhitelistCsv(String csv);
String normalizeUid(String rawUid);
void unlockDoorAndPrompt();
void playGranted();
void playDenied();
bool initDFPlayer();
void handleDFPlayerEvents();
void processSerialCommands();
String checkOnlineVerification(String uidStr);
void syncWhitelistFromWiFi();

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(500);
  Serial.println("\n==================================================");
  Serial.println("  SDASFC ESP32 SMART DOOR CONTROLLER (HYBRID v3.0)");
  Serial.println("==================================================");

  // 1. Relay Initialization (Default Locked)
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, RELAY_OFF);
  Serial.println("[HW] Relay initialized (State: LOCKED).");

  // 2. IR Exit Sensor Initialization (Active LOW)
  pinMode(EXIT_BUTTON, INPUT_PULLUP);
  Serial.println("[HW] IR Exit Sensor initialized on GPIO 33.");

  // 3. Initialize Flash Storage (Preferences) & Load Whitelist
  prefs.begin("sdasfc", false);
  loadWhitelistFromNVS();

  // 4. Initialize I2C Bus & DS3231 RTC
  Wire.begin(21, 22);
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

  // 5. Initialize SPI Bus & RFID Reader (RC522)
  SPI.begin(18, 19, 23, SS_PIN);
  rfid.PCD_Init();
  Serial.println("[HW] RFID RC522 Reader Ready.");

  // 6. Initialize DFPlayer Mini MP3 Player
  hasDFPlayer = initDFPlayer();

  // 7. Optional Wi-Fi Setup
  if (strlen(WIFI_SSID) > 0) {
    Serial.printf("[WIFI] Connecting to '%s'...\n", WIFI_SSID);
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    unsigned long wifiStart = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - wifiStart < 5000) {
      delay(250);
      Serial.print(".");
    }
    if (WiFi.status() == WL_CONNECTED) {
      wifiEnabled = true;
      Serial.printf("\n[WIFI] ✅ Connected! IP: %s\n", WiFi.localIP().toString().c_str());
      syncWhitelistFromWiFi();
    } else {
      Serial.println("\n[WIFI] ⚠️ Connection timed out. Running in Offline/USB mode.");
    }
  }

  // Request sync from USB Serial Bridge on startup
  Serial.println("REQ:SYNC");

  Serial.println("--------------------------------------------------");
  Serial.printf("SYS:READY — %d Card(s) Cached in Flash. Ready to scan.\n", whitelistCount);
  Serial.println("--------------------------------------------------\n");
}

void loop() {
  yield();

  // 1. Process DFPlayer real-time events
  handleDFPlayerEvents();

  // 2. Process incoming Serial commands from Host PC Bridge
  processSerialCommands();

  // 3. Periodic Wi-Fi Whitelist Sync (if Wi-Fi active)
  if (wifiEnabled && (millis() - lastWifiSyncTime > WIFI_SYNC_INTERVAL)) {
    lastWifiSyncTime = millis();
    syncWhitelistFromWiFi();
  }

  // ==================================================
  // 4. INFRARED EXIT SENSOR TRIGGER (Wave to Exit)
  // ==================================================
  if (digitalRead(EXIT_BUTTON) == LOW) {
    delay(40); // Debounce
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

  // ==================================================
  // 5. RFID CARD SCANNING (HYBRID DECISION ENGINE)
  // ==================================================
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    delay(15);
    return;
  }

  // Format Card UID string (e.g. "0A 75 B4 02")
  String uidStr = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uidStr += "0";
    uidStr += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) uidStr += " ";
  }
  uidStr.toUpperCase();

  // Print RTC timestamp
  if (hasRTC) {
    DateTime now = rtc.now();
    Serial.printf("[RTC %04d-%02d-%02d %02d:%02d:%02d] ",
                  now.year(), now.month(), now.day(),
                  now.hour(), now.minute(), now.second());
  }

  Serial.println("[SCAN] Scanned RFID UID: " + uidStr);

  // Check local offline whitelist first
  bool localMatch = isCardAuthorized(uidStr);

  // Check Online Verification (USB Bridge or Direct Wi-Fi)
  String onlineResult = checkOnlineVerification(uidStr);

  if (onlineResult == "GRANT") {
    Serial.println("[ACCESS] ✅ GRANTED (Online Server Decision)");
    addCardToWhitelist(uidStr); // Auto-cache to flash
    unlockDoorAndPrompt();
  } 
  else if (onlineResult == "DENY") {
    Serial.println("[ACCESS] ❌ DENIED (Online Server Decision)");
    removeCardFromWhitelist(uidStr); // Remove deactivated card from flash
    playDenied();
    delay(2000);
  } 
  else {
    // OFFLINE / STANDALONE FALLBACK (USB unplugged or server offline)
    Serial.println("[STANDALONE] ⚡ Host unreachable. Checking Flash Whitelist...");
    if (localMatch) {
      Serial.println("[ACCESS] ✅ GRANTED (Verified from ESP32 Flash Memory)");
      unlockDoorAndPrompt();
    } else {
      Serial.println("[ACCESS] ❌ DENIED (Card not registered in Flash)");
      playDenied();
      delay(2000);
    }
  }

  rfid.PICC_HaltA();
  delay(150);
}

// ============================================================================
// ONLINE VERIFICATION (USB Serial + Wi-Fi HTTP)
// ============================================================================
String checkOnlineVerification(String uidStr) {
  // Clear any stale serial incoming bytes
  while (Serial.available() > 0) { Serial.read(); }

  // 1. Send UID via USB Serial
  Serial.print("UID:");
  Serial.println(uidStr);
  Serial.flush();

  // 2. If Wi-Fi connected, try HTTP POST concurrently
  if (wifiEnabled && WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(SERVER_HOST) + "/public/api/rfid_scan.php";
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(800);

    String payload = "{\"rfid_uid\":\"" + uidStr + "\"}";
    int httpCode = http.POST(payload);

    if (httpCode == 200) {
      String response = http.getString();
      http.end();
      if (response.indexOf("\"granted\"") >= 0) return "GRANT";
      if (response.indexOf("\"denied\"") >= 0)  return "DENY";
    }
    http.end();
  }

  // 3. Await USB Serial response with fast 700ms timeout
  unsigned long start = millis();
  String buffer = "";
  while (millis() - start < 700) {
    yield();
    while (Serial.available() > 0) {
      char c = (char)Serial.read();
      buffer += c;
      if (buffer.indexOf("GRANT") >= 0) return "GRANT";
      if (buffer.indexOf("DENY") >= 0)  return "DENY";
    }
    delay(10);
  }

  return "OFFLINE";
}

// ============================================================================
// LOCAL FLASH WHITELIST MANAGEMENT (Preferences / NVS)
// ============================================================================
void loadWhitelistFromNVS() {
  whitelistCount = 0;
  String storedCsv = prefs.getString("whitelist", "");
  
  if (storedCsv.length() > 0) {
    parseWhitelistCsv(storedCsv);
    Serial.printf("[STORAGE] Loaded %d card(s) from Flash NVS.\n", whitelistCount);
  } else {
    // Populate with Fallback cards on clean flash
    for (int i = 0; i < NUM_FALLBACK; i++) {
      addCardToWhitelist(FALLBACK_CARDS[i]);
    }
    Serial.println("[STORAGE] Clean Flash detected. Loaded default fallback cards.");
  }
}

void saveWhitelistToNVS() {
  String csv = "";
  for (int i = 0; i < whitelistCount; i++) {
    if (i > 0) csv += ",";
    csv += whitelist[i];
  }
  prefs.putString("whitelist", csv);
}

bool isCardAuthorized(String uid) {
  String norm = normalizeUid(uid);
  if (norm.length() == 0) return false;

  // Check fallback cards
  for (int i = 0; i < NUM_FALLBACK; i++) {
    if (norm.equalsIgnoreCase(normalizeUid(FALLBACK_CARDS[i]))) {
      return true;
    }
  }

  // Check flash cache
  for (int i = 0; i < whitelistCount; i++) {
    if (norm.equalsIgnoreCase(normalizeUid(whitelist[i]))) {
      return true;
    }
  }

  return false;
}

void addCardToWhitelist(String uid) {
  String norm = normalizeUid(uid);
  if (norm.length() == 0) return;

  // Check if already in whitelist
  for (int i = 0; i < whitelistCount; i++) {
    if (norm.equalsIgnoreCase(normalizeUid(whitelist[i]))) return;
  }

  if (whitelistCount < MAX_WHITELIST_ENTRIES) {
    whitelist[whitelistCount++] = uid;
    saveWhitelistToNVS();
    Serial.println("[CACHE] Added UID '" + uid + "' to Flash storage.");
  }
}

void removeCardFromWhitelist(String uid) {
  String norm = normalizeUid(uid);
  if (norm.length() == 0) return;

  for (int i = 0; i < whitelistCount; i++) {
    if (norm.equalsIgnoreCase(normalizeUid(whitelist[i]))) {
      for (int j = i; j < whitelistCount - 1; j++) {
        whitelist[j] = whitelist[j + 1];
      }
      whitelistCount--;
      saveWhitelistToNVS();
      Serial.println("[CACHE] Removed UID '" + uid + "' from Flash storage.");
      return;
    }
  }
}

void parseWhitelistCsv(String csv) {
  whitelistCount = 0;
  int start = 0;
  while (start < csv.length() && whitelistCount < MAX_WHITELIST_ENTRIES) {
    int comma = csv.indexOf(',', start);
    String token = (comma == -1) ? csv.substring(start) : csv.substring(start, comma);
    token.trim();
    if (token.length() > 0) {
      whitelist[whitelistCount++] = token;
    }
    if (comma == -1) break;
    start = comma + 1;
  }
}

String normalizeUid(String rawUid) {
  String clean = "";
  for (unsigned int i = 0; i < rawUid.length(); i++) {
    char c = rawUid.charAt(i);
    if (c != ' ' && c != ':' && c != '-') {
      clean += (char)toupper(c);
    }
  }
  return clean;
}

// ============================================================================
// SERIAL COMMAND PROCESSOR
// ============================================================================
void processSerialCommands() {
  if (Serial.available() <= 0) return;

  String cmd = Serial.readStringUntil('\n');
  cmd.trim();
  if (cmd.length() == 0) return;

  if (cmd.startsWith("SYNC_WHITELIST:")) {
    String csv = cmd.substring(15);
    parseWhitelistCsv(csv);
    saveWhitelistToNVS();
    Serial.printf("[SYNC] ✅ Whitelist updated via Serial (%d active cards stored).\n", whitelistCount);
  } 
  else if (cmd.startsWith("ADD:")) {
    addCardToWhitelist(cmd.substring(4));
  } 
  else if (cmd.startsWith("DEL:") || cmd.startsWith("REMOVE:")) {
    int idx = cmd.indexOf(':');
    removeCardFromWhitelist(cmd.substring(idx + 1));
  } 
  else if (cmd == "LIST" || cmd == "LIST_WHITELIST") {
    Serial.printf("\n--- ESP32 FLASH WHITELIST (%d Cards) ---\n", whitelistCount);
    for (int i = 0; i < whitelistCount; i++) {
      Serial.printf(" [%d] %s\n", i + 1, whitelist[i].c_str());
    }
    Serial.println("----------------------------------------\n");
  } 
  else if (cmd == "CLEAR_WHITELIST") {
    whitelistCount = 0;
    prefs.remove("whitelist");
    Serial.println("[STORAGE] Whitelist cleared from Flash.");
  } 
  else if (cmd == "UNLOCK") {
    Serial.println("[REMOTE] Remote unlock command received.");
    unlockDoorAndPrompt();
  } 
  else if (cmd == "T1" || cmd == "DENY") {
    playDenied();
  } 
  else if (cmd == "T2" || cmd == "GRANT") {
    playGranted();
  }
}

// ============================================================================
// WI-FI SYNC
// ============================================================================
void syncWhitelistFromWiFi() {
  if (!wifiEnabled || WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = String(SERVER_HOST) + "/public/api/whitelist.php";
  http.begin(url);
  http.setTimeout(2000);

  int httpCode = http.GET();
  if (httpCode == 200) {
    String payload = http.getString();
    // Parse JSON uids: ["UID1", "UID2"]
    int uidsStart = payload.indexOf("\"uids\":[");
    if (uidsStart >= 0) {
      int uidsEnd = payload.indexOf("]", uidsStart);
      if (uidsEnd > uidsStart) {
        String uidsJson = payload.substring(uidsStart + 8, uidsEnd);
        uidsJson.replace("\"", "");
        parseWhitelistCsv(uidsJson);
        saveWhitelistToNVS();
        Serial.printf("[WIFI SYNC] ✅ Synchronized %d cards into Flash!\n", whitelistCount);
      }
    }
  }
  http.end();
}

// ============================================================================
// DOOR CONTROL & AUDIO PROMPTS
// ============================================================================
void unlockDoorAndPrompt() {
  Serial.println("[DOOR] >>> 🔓 RELAY ENERGIZED: Door is UNLOCKED <<<");
  digitalWrite(RELAY_PIN, RELAY_ON);

  playGranted();

  // Keep door unlocked for specified duration
  delay(UNLOCK_HOLD_MS);

  digitalWrite(RELAY_PIN, RELAY_OFF);
  Serial.println("[DOOR] >>> 🔒 RELAY DE-ENERGIZED: Door is LOCKED <<<");

  rfid.PCD_Init();
}

void playGranted() {
  if (!hasDFPlayer) return;
  Serial.println("[AUDIO] Playing: Access Granted & Welcome (Track 2)");
  player.play(2);
}

void playDenied() {
  if (!hasDFPlayer) return;
  Serial.println("[AUDIO] Playing: Access Denied (Track 1)");
  player.play(1);
}

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

  return true;
}

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
