  /*
  * DFPlayer Mini + Speaker Standalone Test Sketch for ESP32
  * SDASFC Project - Hardware Component Test
  * 
  * Hardware Required:
  * - ESP32 Dev Module
  * - DFPlayer Mini Module (MP3-TF-16P) + MicroSD Card (FAT32, containing 0001.mp3, 0002.mp3, 0003.mp3)
  * - 3W 8Ω Speaker
  * - 1kΩ Resistor (placed between ESP32 TX2 GPIO 17 and DFPlayer RX)
  * 
  * ESP32 Pin Connections:
  * - ESP32 GPIO 17 (TX2) ---> [ 1kΩ Resistor ] ---> DFPlayer Pin 2 (RX)
  * - ESP32 GPIO 16 (RX2) <------------------------ DFPlayer Pin 3 (TX)
  * - ESP32 5V / VIN      ------------------------ DFPlayer Pin 1 (VCC)
  * - ESP32 GND           ------------------------ DFPlayer Pin 7 (GND)
  * - Speaker Leads       ------------------------ DFPlayer Pin 9 (SPK1) & Pin 11 (SPK2)
  * 
  * Serial Commands (Open Serial Monitor at 115200 Baud):
  * - Type '1' + Enter : Play 0001.mp3 (System Ready)
  * - Type '2' + Enter : Play 0002.mp3 (Access Granted)
  * - Type '3' + Enter : Play 0003.mp3 (Access Denied)
  * - Type '+' + Enter : Increase Volume
  * - Type '-' + Enter : Decrease Volume
  */

  #include <HardwareSerial.h>
  #include <DFRobotDFPlayerMini.h>

  #define RX2_PIN 16
  #define TX2_PIN 17

  HardwareSerial mp3Serial(2); // Use ESP32 HardwareSerial2
  DFRobotDFPlayerMini player;

  int currentVolume = 12; // Lower volume (0 - 30) to prevent USB power brownouts

  void setup() {
    Serial.begin(115200);
    delay(1000);

    Serial.println("\n============================================");
    Serial.println("  ESP32 + MP3-TF-16P Standalone Test");
    Serial.println("============================================");

    // Initialize HardwareSerial2 at 9600 baud
    mp3Serial.begin(9600, SERIAL_8N1, RX2_PIN, TX2_PIN);
    delay(1000);

    Serial.println("[INIT] Connecting to MP3-TF-16P...");

    // Initialize player object pointer first to prevent Null Pointer Crash
    bool isOnline = player.begin(mp3Serial, false, false);
    delay(500);
    yield();

    if (!isOnline) {
      Serial.println("[ERROR] MP3-TF-16P initialization failed!");
    } else {
      Serial.println("[SUCCESS] MP3-TF-16P Library Initialized!");
      delay(300);
      yield();

      Serial.println("[DIAGNOSTIC] Checking Serial Response & SD Card Files...");
      int fileCount = player.readFileCounts();
      delay(300);
      yield();

      if (fileCount < 0) {
        Serial.printf("[WARNING] Serial Communication Error: %d\n", fileCount);
        Serial.println(" -> -1 means ESP32 RX2 (GPIO 16) is NOT receiving serial data from MP3 TX pin.");
        Serial.println(" -> Check: GPIO 16 -> MP3 Pin 3 (TX), GPIO 17 + 1kΩ -> MP3 Pin 2 (RX), Common GND.");
      } else if (fileCount == 0) {
        Serial.println("[WARNING] MP3 Module online, but 0 audio files found on MicroSD card.");
      } else {
        Serial.printf("[SUCCESS] Communication Verified! MicroSD Files Found: %d\n", fileCount);
      }

      Serial.println("\n--- CONTROLS ---");
      Serial.println("Send '1' in Serial Monitor -> Play 0001.mp3");
      Serial.println("Send '2' in Serial Monitor -> Play 0002.mp3");
      Serial.println("Send '3' in Serial Monitor -> Play 0003.mp3");
      Serial.println("Send 'f' in Serial Monitor -> Re-check SD Card File Count");
      Serial.println("Send '+' -> Volume Up | '-' -> Volume Down");
      Serial.println("----------------\n");
      Serial.println("Ready! Send '1', '2', '3', or 'f' in Serial Monitor.");
    }
  }

  void loop() {
    if (Serial.available() > 0) {
      char cmd = Serial.read();

      if (cmd == '1') {
        Serial.println("[PLAY] Requesting Track 1: 0001.mp3");
        player.play(1);
      } 
      else if (cmd == '2') {
        Serial.println("[PLAY] Requesting Track 2: 0002.mp3");
        player.play(2);
      } 
      else if (cmd == '3') {
        Serial.println("[PLAY] Requesting Track 3: 0003.mp3");
        player.play(3);
      } 
      else if (cmd == 'f' || cmd == 'F') {
        Serial.println("[DIAGNOSTIC] Re-checking MicroSD card file count...");
        int count = player.readFileCounts();
        Serial.printf("[DIAGNOSTIC] Total files on SD Card: %d\n", count);
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

    // Print MP3 status/messages safely without blocking Watchdog
    if (player.available()) {
      uint8_t type = player.readType();
      int value = player.read();
      if (type == DFPlayerPlayFinished) {
        Serial.printf("[STATUS] Track %d finished playing.\n", value);
      } else if (type == DFPlayerError) {
        Serial.printf("[ERROR] MP3 Error Code: %d\n", value);
      }
    }

    // Crucial: yield and delay to feed ESP32 Watchdog Timer (prevents TG1WDT_SYS_RESET)
    yield();
    delay(10);
  }

