#include <SPI.h>
#include <MFRC522.h>
#include <SoftwareSerial.h>
#include <DFRobotDFPlayerMini.h>

//========================
// RC522 RFID
//========================
#define SS_PIN 5
#define RST_PIN 4

MFRC522 rfid(SS_PIN, RST_PIN);

//========================
// DFPlayer Mini
//========================
SoftwareSerial mp3Serial(16,17); // RX, TX
DFRobotDFPlayerMini player;

//========================
// Pins
//========================
#define RELAY_PIN 27
#define EXIT_BUTTON 33

//========================
// Authorized UID
//========================
byte authorizedUID[] = {
0x0A,
0x75,
0xB4,
0x02
};

//========================
// Function Prototypes
//========================
bool checkUID();
void unlockDoor();
void playIdle();
void playGranted();
void playDenied();
void playLocked();

void setup() {

Serial.begin(115200);

pinMode(RELAY_PIN, OUTPUT);
pinMode(EXIT_BUTTON, INPUT_PULLUP);

// Door Locked
digitalWrite(RELAY_PIN, LOW);

SPI.begin();
rfid.PCD_Init();

mp3Serial.begin(9600);

if (!player.begin(mp3Serial)) {
Serial.println("DFPlayer Error!");
while(true);
}

player.volume(25);

Serial.println("System Ready");

delay(1000);

playIdle();

}

void loop() {

//========================
// EXIT BUTTON
//========================

if(digitalRead(EXIT_BUTTON)==LOW){

    delay(30);

    if(digitalRead(EXIT_BUTTON)==LOW){

      playGranted();

      delay(3500);

      unlockDoor();

      while(digitalRead(EXIT_BUTTON)==LOW);

    }

}

//========================
// WAIT FOR RFID
//========================

if(!rfid.PICC_IsNewCardPresent())
return;

if(!rfid.PICC_ReadCardSerial())
return;

Serial.print("UID : ");

for(byte i=0;i<rfid.uid.size;i++){

    if(rfid.uid.uidByte[i]<0x10)
      Serial.print("0");

    Serial.print(rfid.uid.uidByte[i],HEX);
    Serial.print(" ");

}

Serial.println();

if(checkUID()){

      playGranted();

      delay(3500);

      unlockDoor();

}
else{

      playDenied();

      delay(3500);

}

rfid.PICC_HaltA();
rfid.PCD_StopCrypto1();

delay(500);

playIdle();

}

//========================
// CHECK AUTHORIZED UID
//========================
bool checkUID() {

if (rfid.uid.size != sizeof(authorizedUID))
return false;

for (byte i = 0; i < rfid.uid.size; i++) {

    if (rfid.uid.uidByte[i] != authorizedUID[i]) {
      return false;
    }

}

return true;
}

//========================
// UNLOCK DOOR
//========================
void unlockDoor() {

Serial.println("Door Unlocked");

// Relay ON (Unlock)
digitalWrite(RELAY_PIN, HIGH);

delay(5000);

// Relay OFF (Lock)
digitalWrite(RELAY_PIN, LOW);

playLocked();

delay(1500);

playIdle();

}

//========================
// VOICE PROMPTS
//========================

//0001.mp3
void playIdle() {

player.play(1);

}

//0002.mp3
void playGranted() {

player.play(2);

}

//0003.mp3
void playDenied() {

player.play(3);

}

//0004.mp3
void playLocked() {

player.play(4);

}
