/*
 * SDASFC — Smart Door Automation System for CICS
 * Hardware Firmware for ESP32 (Production Firmware v3.1 - WiFi + Serial Hybrid + NVS Flash Whitelist + Anti-Loop Protection)
 * 
 * ============================================================================
 * HYBRID OPERATION MODES (USB Tethered, Standalone Offline, & Direct Wi-Fi):
 * ============================================================================
 * 1. STANDALONE OFFLINE MODE (No USB, No Wi-Fi):
 *    - All registered card UIDs are saved in ESP32 Non-Volatile Flash (Preferences/NVS).
 *    - Master Emergency Key Card ("93 39 6E 1B") has hardware-level bypass.
 *    - When offline/unplugged, the ESP32 verifies against internal flash memory.
 *    - If the tapped card is registered, the door unlocks IMMEDIATELY.
 * 
 * 2. USB SERIAL BRIDGE MODE:
 *    - Connected to PC via USB running serial_bridge (PowerShell, Python, or PHP).
 *    - Transmits "UID:<HEX_UID>" and awaits live server decision.
 *    - Automatically synchronizes the entire database whitelist to ESP32 Flash memory on connect!
 * 
 * 3. DIRECT WI-FI MODE:
 *    - If configured, ESP32 joins local Wi-Fi and communicates directly with XAMPP API.
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
 * MicroSD Audio Track Mapping:
 * - Track 2 (0002.mp3): "Access granted you may now open the door. Welcome to the CICS laboratory" (Card tap only)
 * - Track 1 (0001.mp3): "Access Denied" (Card tap rejected / timeout)
 * - IR Exit Button: SILENT UNLOCK (No welcome voice prompt on exit)
 * 
 * Master Emergency Key (Brownout / Offline Hardware Bypass):
 * - UID: "93 39 6E 1B" (Works 100% offline, during power failure/brownouts, or server downtime)
 * 
 * ESP32 Pin Mapping:
 * - RC522 RFID (SPI Bus):
 *   - SDA (SS) -> GPIO 5
 *   - SCK      -> GPIO 18
 *   - MOSI     -> GPIO 23
 *   - MISO     -> GPIO 19
 *   - RST      -> GPIO 4
 *   - 3.3V     -> ESP32 3.3V Pin
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
 * 
 * Network & Safety:
 * - Local Wi-Fi Direct: Sends HTTP POST directly to Host Laptop XAMPP API (No internet needed)
 * - USB Serial Fallback: Bridges via serial_bridge.ps1 / .py / .php if Wi-Fi is unavailable
 * - Anti-Loop Protection: Edge-triggered detection with 2.5s cooldown prevents continuous looping
 * - Brownout Protection: Disables brownout detector to prevent reboot loops during inductive relay switching
 */

#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <Wire.h>
#include <HardwareSerial.h>
#include <MFRC522.h>
#include <DFRobotDFPlayerMini.h>
#include <RTClib.h>
#include <Preferences.h>

//==================================================
// 1. Wi-Fi & Local Server Configuration
//==================================================
// Presentation router credentials (No internet required)
const char* WIFI_SSID     = "PLDT_Home_1D000";
const char* WIFI_PASSWORD = "pldthome";

// Local Laptop IP running XAMPP Apache/MySQL
const char* API_URL       = "http://192.168.1.166/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php";
const char* WHITELIST_URL = "http://192.168.1.166/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/whitelist.php";

// Wi-Fi Connection Settings
const unsigned long WIFI_TIMEOUT_MS = 6000; // 6 seconds timeout for Wi-Fi association on boot
bool wifiEnabled = false;

//==================================================
// 2. Master Emergency Key Card (Offline Brownout Bypass)
//==================================================
const String MASTER_CARD_UID          = "93 39 6E 1B";
const String MASTER_CARD_UID_NO_SPACE = "93396E1B";

// Hardcoded Fallback Cards (Always authorized even on fresh flash)
const char* FALLBACK_CARDS[] = {
  "93 39 6E 1B"  // Master Emergency Key Card
};
const int NUM_FALLBACK = sizeof(FALLBACK_CARDS) / sizeof(FALLBACK_CARDS[0]);

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
int defaultVolume = 30; // Maximum audio volume (0 - 30)

// Anti-Loop & Debounce State Tracking for Exit Sensor
bool lastExitState = HIGH;
unsigned long lastExitTriggerTime = 0;
const unsigned long EXIT_COOLDOWN_MS = 2500; // 2.5-second cooldown after relocking to prevent looping

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
void unlockDoorExitSilent();
void playGranted();
void playDenied();
bool initDFPlayer();
void handleDFPlayerEvents();
void processSerialCommands();
String checkOnlineVerification(String uidStr);
String verifyViaWiFi(String uid);
String waitForSerialResponse(unsigned long timeoutMs);
void syncWhitelistFromWiFi();
void connectToWiFi();

void setup() {
  // Disable ESP32 Brownout Detector to prevent inductive relay click reboot loops
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  Serial.begin(SERIAL_BAUD);
  delay(500);
  Serial.println("\n==================================================");
  Serial.println("  SDASFC ESP32 SMART DOOR LOCK CONTROLLER (v3.1)");
  Serial.println("  Wi-Fi + Serial Hybrid & Offline Master Key Ready");
  Serial.println("==================================================");

  // 1. Relay Initialization (Default Locked / De-energized for Brownout Safety)
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, RELAY_OFF);
  Serial.println("[HW] Relay initialized (State: LOCKED).");

  // 2. IR Exit Sensor Initialization (Active LOW / Internal Pullup)
  pinMode(EXIT_BUTTON, INPUT_PULLUP);
  delay(100);

  // Safety Boot Check: Verify Exit Sensor State on Startup
  if (digitalRead(EXIT_BUTTON) == LOW) {
    Serial.println("[HW ALERT] ⚠️ IR Exit Sensor (GPIO 33) is LOW on startup!");
    Serial.println("          Checking for stuck sensor or NC wiring...");
    unsigned long bootWait = millis();
    while (digitalRead(EXIT_BUTTON) == LOW && (millis() - bootWait < 2000)) {
      delay(50);
    }
    if (digitalRead(EXIT_BUTTON) == LOW) {
      Serial.println("[HW ALERT] ⚠️ Sensor is continuously LOW. Please verify it is wired to NO (Normally Open) and COM, not NC.");
    } else {
      Serial.println("[HW] Sensor cleared to HIGH.");
    }
  } else {
    Serial.println("[HW] IR Exit Sensor initialized on GPIO 33 (State: IDLE / HIGH).");
  }

  lastExitState = digitalRead(EXIT_BUTTON);

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

  // 7. Connect to Wi-Fi (if configured)
  connectToWiFi();

  // Request sync from USB Serial Bridge on startup
  Serial.println("REQ:SYNC");

  Serial.println("--------------------------------------------------");
  Serial.printf("MASTER KEY CONFIGURED : '%s'\n", MASTER_CARD_UID.c_str());
  Serial.printf("SYS:READY — %d Card(s) Cached in Flash. Ready to scan.\n", whitelistCount);
  Serial.println("SYS:READY — Awaiting RFID taps or Exit button events.");
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
  // 4. INFRARED EXIT SENSOR TRIGGER (Edge-Triggered Anti-Loop)
  // ==================================================
  int currentExitState = digitalRead(EXIT_BUTTON);

  // Trigger ONLY on HIGH -> LOW transition AND after cooldown period has elapsed
  if (currentExitState == LOW && lastExitState == HIGH && (millis() - lastExitTriggerTime > EXIT_COOLDOWN_MS)) {
    delay(50); // Debounce confirmation
    if (digitalRead(EXIT_BUTTON) == LOW) {
      Serial.println("\n[EVENT:EXIT_BUTTON] IR Exit Sensor Triggered!");
      
      // Unlock door SILENTLY without welcome prompt!
      unlockDoorExitSilent();
      lastExitTriggerTime = millis();

      // Wait until hand / object is removed from sensor before re-arming
      unsigned long waitRelease = millis();
      while (digitalRead(EXIT_BUTTON) == LOW) {
        yield();
        delay(50);
        if (millis() - waitRelease > 5000) {
          Serial.println("[HW ALERT] Exit Sensor is continuously active! Check if wired to NC instead of NO, or adjust sensor sensitivity.");
          break;
        }
      }
      delay(200); // Post-release stabilization
      rfid.PCD_Init();
      lastExitState = digitalRead(EXIT_BUTTON);
      return;
    }
  }
  lastExitState = currentExitState;

  // ==================================================
  // 5. RFID CARD SCANNING (HYBRID DECISION ENGINE)
  // ==================================================
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    delay(15);
    return;
  }

  // Format Card UID string (e.g. "93 39 6E 1B")
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

  Serial.printf("\n[CARD DETECTED] UID: %s\n", uidStr.c_str());

  // Check Master Emergency Key Check (Hardware-level Bypass)
  String uidNoSpace = uidStr;
  uidNoSpace.replace(" ", "");

  if (uidStr == MASTER_CARD_UID || uidNoSpace == MASTER_CARD_UID_NO_SPACE) {
    Serial.println("==================================================");
    Serial.println("🔑 [EMERGENCY MASTER KEY] Verified Locally on ESP32!");
    Serial.println("⚡ [BROWNOUT BYPASS] Unlocking Door Immediately...");
    Serial.println("==================================================");

    unlockDoorAndPrompt();

    // If Wi-Fi is connected, log access event to server in background
    if (wifiEnabled && WiFi.status() == WL_CONNECTED) {
      verifyViaWiFi(uidStr);
    }

    rfid.PICC_HaltA();
    delay(200);
    return;
  }

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
  // Option A: If Wi-Fi connected, try HTTP POST first
  if (wifiEnabled && WiFi.status() == WL_CONNECTED) {
    String wifiResp = verifyViaWiFi(uidStr);
    if (wifiResp == "GRANT" || wifiResp == "DENY") {
      return wifiResp;
    }
  }

  // Option B: Transmit UID via USB Serial Bridge
  while (Serial.available() > 0) { Serial.read(); }
  Serial.print("UID:");
  Serial.println(uidStr);
  Serial.flush();

  // Await USB Serial response with 1200ms timeout
  unsigned long start = millis();
  String buffer = "";
  while (millis() - start < 1200) {
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
  http.addHeader("User-Agent", "SDASFC-ESP32-WiFi/3.1");
  http.setTimeout(2500);

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
 * Connect to Local Router Wi-Fi
 */
void connectToWiFi() {
  if (String(WIFI_SSID) == "" || String(WIFI_SSID) == "YOUR_WIFI_SSID") {
    Serial.println("[WIFI] Wi-Fi credentials not set (Running in USB Serial mode).");
    wifiEnabled = false;
    return;
  }

  Serial.printf("[WIFI] Connecting to SSID: '%s'...\n", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  unsigned long startAttempt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAttempt < WIFI_TIMEOUT_MS) {
    delay(250);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    wifiEnabled = true;
    Serial.println("✅ [WIFI] Connected successfully!");
    Serial.printf("   ESP32 Local IP : %s\n", WiFi.localIP().toString().c_str());
    Serial.printf("   Target API URL : %s\n", API_URL);
    syncWhitelistFromWiFi();
  } else {
    wifiEnabled = false;
    Serial.println("⚠️ [WIFI] Connection failed/timed out. Operating in USB Serial Bridge fallback mode.");
  }
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
  http.begin(WHITELIST_URL);
  http.setTimeout(2500);

  int httpCode = http.GET();
  if (httpCode == 200) {
    String payload = http.getString();
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
  delay(200);
  player.volume(defaultVolume); // Set to 30 (Maximum volume)
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

/**
 * Card Tap Unlock & Voice Prompt Sequence:
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
 * Silent Door Unlock Sequence for Exit Sensor:
 * 1. Energizes Relay IMMEDIATELY (Door unlocks on millisecond 0)
 * 2. NO VOICE PROMPT (Silent exit operation — no welcome message)
 * 3. Holds door unlocked for 6 full seconds
 * 4. De-energizes Relay (Locked)
 * 5. Refreshes RFID antenna state
 */
void unlockDoorExitSilent() {
  Serial.println("[DOOR] >>> 🔓 RELAY ENERGIZED: Exit Wave Detected (Silent Unlock) <<<");
  digitalWrite(RELAY_PIN, RELAY_ON);

  // Hold door unlocked for 6 seconds WITHOUT voice prompt
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
  player.volume(defaultVolume); // Re-assert maximum volume 30
  delay(30);
  Serial.println("[AUDIO] Playing: Access Granted & Welcome (Track 2) @ Volume 30");
  player.play(2);
}

/**
 * Play Access Denied:
 * Plays Track 1 ("Access Denied")
 */
void playDenied() {
  if (!hasDFPlayer) return;
  player.volume(defaultVolume); // Re-assert maximum volume 30
  delay(30);
  Serial.println("[AUDIO] Playing: Access Denied (Track 1) @ Volume 30");
  player.play(1);
}
