# WiFi Local Network Integration & Offline Master Key Guide

> **Status: IMPLEMENTED & ACTIVE (Firmware v3.0)**  
> The ESP32 firmware [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino) natively supports Wi-Fi Direct HTTP requests to the host laptop's XAMPP API, with automatic USB Serial Bridge fallback and hardware-level offline Master Key protection.

---

## 1. How It Works

The ESP32 and the host laptop connect to the **same local Wi-Fi router network**:

```
[ ESP32 Door Controller ]
   │
   ├─► (Wi-Fi Online) ────────► Direct HTTP POST to http://192.168.1.13/.../rfid_scan.php
   │                               └─► No Serial Bridge Needed!
   │
   ├─► (Wi-Fi Offline/Dropped) ► Fallback: Transmits UID over USB Serial to start_bridge.bat
   │
   └─► (Brownout / Server Off) ► Offline Master Key ("93 39 6E 1B")
                                   └─► Bypasses network and unlocks door immediately!
```

---

## 2. Configuration Parameters

In [`arduino/sdasfc_door_lock.ino`](file:///C:/xampp/htdocs/SDASFC-Smart-Door-Automation-System-for-CICS/arduino/sdasfc_door_lock.ino):

```cpp
// Wi-Fi Credentials
const char* WIFI_SSID     = "YOUR_ROUTER_SSID";
const char* WIFI_PASSWORD = "YOUR_ROUTER_PASSWORD";

// Local Laptop IP running XAMPP Apache/MySQL
const char* API_URL       = "http://192.168.1.13/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php";

// Master Emergency Key (Works 100% offline, during power outages/brownouts)
const String MASTER_CARD_UID = "93 39 6E 1B";
```

---

## 3. Brownout & Power Failure Safety

1. **Lock State:** The 12V UPS power supply and battery backup keep the ESP32 and lock powered. During a building power outage, the relay remains **locked** (`digitalWrite(RELAY_PIN, LOW)`).
2. **Offline Security:** Unauthorized cards and normal cards are rejected when the server/router is unreachable.
3. **Master Key Hardware Override:** Tapping the Master Key Card (`93 39 6E 1B`) triggers an immediate hardware-level bypass on the ESP32, unlocking the door for 6 seconds and playing the Access Granted voice prompt at maximum volume (30).

