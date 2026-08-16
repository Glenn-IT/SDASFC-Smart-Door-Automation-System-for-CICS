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
    Serial.println("  ESP32 + DFPlayer Mini Standalone Test");
    Serial.println("============================================");

    // Initialize HardwareSerial2 at 9600 baud
    mp3Serial.begin(9600, SERIAL_8N1, RX2_PIN, TX2_PIN);
    delay(500);

    Serial.println("[INIT] Connecting to DFPlayer Mini...");

    int attempts = 0;
    while (!player.begin(mp3Serial, false, false) && attempts < 5) {
      attempts++;
      Serial.printf("[INIT] Attempt %d failed. Retrying in 1s...\n", attempts);
      delay(1000);
    }

    if (attempts >= 5) {
      Serial.println("[ERROR] DFPlayer Mini not detected after 5 attempts!");
      Serial.println("Troubleshooting Checklist:");
      Serial.println(" 1. Swap RX and TX wires (ESP32 TX2 GPIO17 -> DFPlayer RX, ESP32 RX2 GPIO16 -> DFPlayer TX).");
      Serial.println(" 2. Ensure MicroSD card is inserted and formatted FAT32.");
      Serial.println(" 3. Check 5V VCC and GND connections.");
    } else {
      Serial.println("[SUCCESS] DFPlayer Mini Online!");
      player.volume(currentVolume);
      delay(200);

      Serial.println("\n--- CONTROLS ---");
      Serial.println("Send '1' in Serial Monitor -> Play 0001.mp3");
      Serial.println("Send '2' in Serial Monitor -> Play 0002.mp3");
      Serial.println("Send '3' in Serial Monitor -> Play 0003.mp3");
      Serial.println("Send '+' -> Volume Up");
      Serial.println("Send '-' -> Volume Down");
      Serial.println("----------------\n");
      Serial.println("Ready! Send '1', '2', or '3' in Serial Monitor to test audio.");
    }
  }

  void loop() {
    if (Serial.available() > 0) {
      char cmd = Serial.read();

      if (cmd == '1') {
        Serial.println("[PLAY] Playing Track 1: 0001.mp3");
        player.play(1);
      } 
      else if (cmd == '2') {
        Serial.println("[PLAY] Playing Track 2: 0002.mp3");
        player.play(2);
      } 
      else if (cmd == '3') {
        Serial.println("[PLAY] Playing Track 3: 0003.mp3");
        player.play(3);
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

    // Print DFPlayer status/messages if any occur
    if (player.available()) {
      uint8_t type = player.readType();
      int value = player.read();
      if (type == DFPlayerPlayFinished) {
        Serial.printf("[STATUS] Track %d finished playing.\n", value);
      } else if (type == DFPlayerError) {
        Serial.printf("[ERROR] DFPlayer Error Code: %d\n", value);
      }
    }
  }
