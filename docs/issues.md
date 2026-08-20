# Audio Track Mapping (Updated)
- `0001.mp3` - "Access granted you may now open the door. Welcome to the CICS laboratory" (Access Granted & Welcome)
- `0002.mp3` - "Access Denied" (Access Denied)
- `0004.mp3` - Door Lock (Removed from SD card & code; lock is silent)

### Behavior Verification:
1. **Registered / Active RFID Card Tap:**
   - Plays `0001.mp3` ("Access granted you may now open the door. Welcome to the CICS laboratory")
   - Unlocks relay (GPIO 27 HIGH) for 5 seconds
   - Relocks relay (GPIO 27 LOW) silently
2. **Unregistered / Inactive / Deactivated RFID Card Tap:**
   - Plays `0002.mp3` ("Access Denied")
   - Keeps relay locked
   - Completely silent afterwards
3. **No-Touch Optical IR Wave-to-Exit Sensor:**
   - Plays `0001.mp3` ("Access granted you may now open the door. Welcome to the CICS laboratory")
   - Unlocks relay for 5 seconds, relocks silently
4. **Boot / Startup:**
   - Silent initialization (no audio played on boot) 
