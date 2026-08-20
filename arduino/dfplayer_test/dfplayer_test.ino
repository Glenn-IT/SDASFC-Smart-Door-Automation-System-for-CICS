/*
 * DFPlayer Mini + Speaker Standalone Test Sketch for ESP32 DevKit V1
 * SDASFC Project - Hardware Component Test & Comprehensive Diagnostic
 * 
 * Hardware Required:
 * - ESP32 DevKit V1 (30-pin board layout)
 * - DFPlayer Mini Module (MP3-TF-16P) + MicroSD Card (FAT32, containing 0001.mp3, 0002.mp3, 0003.mp3)
 * - 3W 8Ω Speaker
 * - 2x 1kΩ Resistors (for TX noise dampening & 3.3V GPIO RX over-voltage protection)
 * 
 * ESP32 DevKit V1 Pin Connections:
 * - ESP32 DevKit Pin TX2 ---> [ 1kΩ Resistor ] ---> DFPlayer Pin 2 (RX)
 * - ESP32 DevKit Pin RX2 <--- [ 1kΩ Resistor ] <--- DFPlayer Pin 3 (TX) (3.3V protection)
 * - External 5V Power Supply ---------------------- DFPlayer Pin 1 (VCC)
 * - ESP32 GND (Common GND) ------------------------ DFPlayer Pin 7 (GND)
 * - Speaker Leads         ------------------------ DFPlayer Pin 6 (SPK1) & Pin 8 (SPK2)
 * 
 * Serial Commands (Open Serial Monitor at 115200 Baud):
 * - Type 'd' + Enter : Run Full Hardware Diagnostic (Detect DFPlayer, SD Card & Audio Files)
 * - Type '1' + Enter : Play 0001.mp3 (System Ready)
 * - Type '2' + Enter : Play 0002.mp3 (Access Granted)
 * - Type '3' + Enter : Play 0003.mp3 (Access Denied)
 * - Type 'f' + Enter : Re-check SD Card File Count
 * - Type '+' + Enter : Increase Volume
 * - Type '-' + Enter : Decrease Volume
 */

#include <HardwareSerial.h>
#include <DFRobotDFPlayerMini.h>

#define RX2_PIN 16
#define TX2_PIN 17

HardwareSerial mp3Serial(2); // Use ESP32 HardwareSerial2
DFRobotDFPlayerMini player;

int currentVolume = 12; // Initial safe volume level (0 - 30)
bool isModuleOnline = false;

// Forward declaration of diagnostic function
bool runHardwareDiagnostics();

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("\n==================================================");
  Serial.println("  ESP32 + MP3-TF-16P HARDWARE TEST & DIAGNOSTICS  ");
  Serial.println("==================================================");

  // Initialize HardwareSerial2 at 9600 baud
  mp3Serial.begin(9600, SERIAL_8N1, RX2_PIN, TX2_PIN);
  delay(1000);

  // Run full initial hardware detection scan
  runHardwareDiagnostics();

  Serial.println("\n--- CONTROLS MENU ---");
  Serial.println(" Send 'd' -> Run Full Hardware Diagnostic Scan");
  Serial.println(" Send '1' -> Play Track 1 (0001.mp3)");
  Serial.println(" Send '2' -> Play Track 2 (0002.mp3)");
  Serial.println(" Send '3' -> Play Track 3 (0003.mp3)");
  Serial.println(" Send 'f' -> Re-check SD Card File Count");
  Serial.println(" Send '+' -> Increase Volume | '-' -> Decrease Volume");
  Serial.println("---------------------\n");
  Serial.println("System Ready! Type a command in Serial Monitor.");
}

void loop() {
  // 1. Process Serial User Commands
  if (Serial.available() > 0) {
    char cmd = Serial.read();

    if (cmd == 'd' || cmd == 'D') {
      runHardwareDiagnostics();
    }
    else if (cmd == '1') {
      if (!isModuleOnline) {
        Serial.println("[ERROR] Cannot play! DFPlayer module is NOT connected or offline.");
      } else {
        Serial.println("[PLAY] Requesting Track 1: 0001.mp3");
        player.play(1);
      }
    } 
    else if (cmd == '2') {
      if (!isModuleOnline) {
        Serial.println("[ERROR] Cannot play! DFPlayer module is NOT connected or offline.");
      } else {
        Serial.println("[PLAY] Requesting Track 2: 0002.mp3");
        player.play(2);
      }
    } 
    else if (cmd == '3') {
      if (!isModuleOnline) {
        Serial.println("[ERROR] Cannot play! DFPlayer module is NOT connected or offline.");
      } else {
        Serial.println("[PLAY] Requesting Track 3: 0003.mp3");
        player.play(3);
      }
    } 
    else if (cmd == 'f' || cmd == 'F') {
      if (!isModuleOnline) {
        Serial.println("[ERROR] DFPlayer is offline. Run 'd' to diagnose.");
      } else {
        Serial.println("[DIAGNOSTIC] Checking MicroSD card file count...");
        int count = player.readFileCounts();
        if (count < 0) {
          Serial.println("[ERROR] Failed to read SD card. Check if card is inserted.");
        } else {
          Serial.printf("[DIAGNOSTIC] Total readable files on SD Card: %d\n", count);
        }
      }
    }
    else if (cmd == '+') {
      if (currentVolume < 30) currentVolume += 2;
      player.volume(currentVolume);
      Serial.printf("[VOLUME] Set to: %d / 30\n", currentVolume);
    } 
    else if (cmd == '-') {
      if (currentVolume > 2) currentVolume -= 2;
      player.volume(currentVolume);
      Serial.printf("[VOLUME] Set to: %d / 30\n", currentVolume);
    }
  }

  // 2. Real-Time Hardware Event Listener (Detects SD card insert/remove & play status)
  if (player.available()) {
    uint8_t type = player.readType();
    int value = player.read();

    switch (type) {
      case DFPlayerCardInserted:
        Serial.println("\n[EVENT 💳] MicroSD Card INSERTED into MP3 Module!");
        delay(200);
        yield();
        {
          int fileCount = player.readFileCounts();
          if (fileCount > 0) {
            Serial.printf("[EVENT 🎵] MicroSD Mounted Successfully! %d readable track(s) detected.\n", fileCount);
          } else {
            Serial.println("[WARNING ⚠️] MicroSD detected, but 0 audio files found. Ensure FAT32 format & named 0001.mp3.");
          }
        }
        break;

      case DFPlayerCardRemoved:
        Serial.println("\n[WARNING ⚠️] MicroSD Card REMOVED from MP3 Module!");
        break;

      case DFPlayerCardOnline:
        Serial.println("[EVENT 💳] MicroSD Card Status: ONLINE");
        break;

      case DFPlayerPlayFinished:
        Serial.printf("[STATUS 🔊] Track %d finished playing.\n", value);
        break;

      case DFPlayerError:
        Serial.printf("[ERROR ❌] DFPlayer Error Code (%d): ", value);
        switch (value) {
          case Busy:             Serial.println("Module Busy"); break;
          case Sleeping:         Serial.println("Module Sleeping"); break;
          case SerialWrongStack: Serial.println("Serial Frame Error"); break;
          case CheckSumNotMatch: Serial.println("Checksum Mismatch"); break;
          case FileIndexOut:     Serial.println("File Index Out of Bounds (Track file not found!)"); break;
          case FileMismatch:     Serial.println("File Mismatch"); break;
          case Advertise:        Serial.println("In Advertise Mode"); break;
          default:               Serial.println("Unknown Error"); break;
        }
        break;

      default:
        break;
    }
  }

  // Feed ESP32 Watchdog Timer to prevent reboot loops
  yield();
  delay(10);
}

/**
 * Run comprehensive hardware detection scan:
 * 1. Checks DFPlayer module serial connection (is it plugged in?)
 * 2. Checks MicroSD card presence (is an SD card inserted?)
 * 3. Checks audio file readability (are MP3 files readable?)
 */
bool runHardwareDiagnostics() {
  Serial.println("\n==================================================");
  Serial.println("  🔍 EXECUTING DFPLAYER HARDWARE DETECTION SCAN");
  Serial.println("==================================================");

  // STEP 1: Detect DFPlayer Module Serial Connection
  Serial.print("[STEP 1/3] Detecting DFPlayer Mini Module... ");
  
  // Try initializing DFPlayer library communication
  isModuleOnline = player.begin(mp3Serial, false, false);
  delay(500);
  yield();

  if (!isModuleOnline) {
    Serial.println("❌ [FAIL]");
    Serial.println(" -> ERROR: DFPlayer Mini module NOT DETECTED or NOT RESPONDING!");
    Serial.println(" -> Hardware Check List:");
    Serial.println("    1. Is DFPlayer Pin 1 (VCC) receiving 5V?");
    Serial.println("    2. Is DFPlayer Pin 7 (GND) tied to ESP32 DevKit GND (Common GND)?");
    Serial.println("    3. Are RX2/TX2 pins connected correctly?");
    Serial.println("       - ESP32 DevKit Pin TX2 ---> [ 1kΩ ] ---> DFPlayer Pin 2 (RX)");
    Serial.println("       - ESP32 DevKit Pin RX2 <--- [ 1kΩ ] <--- DFPlayer Pin 3 (TX)");
    return false;
  }
  Serial.println("✅ [PASS] Module Online!");

  // Set default volume
  player.volume(currentVolume);
  delay(200);
  yield();

  // STEP 2 & 3: Check MicroSD Card & Readable Audio Files
  Serial.print("[STEP 2/3] Detecting MicroSD Card... ");
  int fileCount = player.readFileCounts();
  delay(300);
  yield();

  if (fileCount < 0) {
    Serial.println("❌ [FAIL]");
    Serial.printf(" -> ERROR: MicroSD Card NOT DETECTED or Serial Read Error (Code: %d)\n", fileCount);
    Serial.println(" -> Check List:");
    Serial.println("    1. Is a MicroSD card inserted into the DFPlayer slot?");
    Serial.println("    2. Is the SD card formatted FAT32 (MBR, <= 32GB)?");
    Serial.println("    3. Is ESP32 DevKit Pin RX2 correctly connected to DFPlayer Pin 3 (TX)?");
    return false;
  }
  Serial.println("✅ [PASS] MicroSD Card Detected!");

  Serial.print("[STEP 3/3] Checking Readable Audio Files... ");
  if (fileCount == 0) {
    Serial.println("⚠️ [WARN] 0 Files Found!");
    Serial.println(" -> MicroSD card is mounted, but NO readable MP3 files were detected.");
    Serial.println(" -> Make sure your audio files are placed in the root directory:");
    Serial.println("    - 0001.mp3 (System Ready)");
    Serial.println("    - 0002.mp3 (Access Granted)");
    Serial.println("    - 0003.mp3 (Access Denied)");
  } else {
    Serial.printf("✅ [PASS] %d Readable Audio File(s) Found!\n", fileCount);
  }

  Serial.println("==================================================");
  Serial.println("  🎯 DIAGNOSTIC RESULT: HARDWARE OK & READY!");
  Serial.println("==================================================\n");

  return true;
}


