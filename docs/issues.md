[SERIAL RX] SDASFC ESP32 SMART DOOR LOCK CONTROLLER
[SERIAL RX] ==================================================
[SERIAL RX] [HW] Relay initialized (State: LOCKED).
[SERIAL RX] [HW] IR Exit Sensor initialized on GPIO 33.
[SERIAL RX] [HW] RTC DS3231 Ready: 2026-08-20 18:00:42
[SERIAL RX] [HW] RFID RC522 Reader Ready.
[SERIAL RX] [HW] Detecting DFPlayer Mini... ✅ Module Online!
[SERIAL RX] [HW] MicroSD Card OK: 4 readable audio file(s) found.
[SERIAL RX] --------------------------------------------------
[SERIAL RX] SYS:READY — Awaiting RFID taps or Exit button events.
[SERIAL RX] --------------------------------------------------
[SERIAL RX] [RTC 2026-08-20 18:00:50] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS GRANTED. Replying 'GRANT'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:01:09] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS GRANTED. Replying 'GRANT'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:01:27] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS DENIED. Replying 'DENY'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:01:41] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS DENIED. Replying 'DENY'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:01:50] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS DENIED. Replying 'DENY'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:15:36] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS DENIED. Replying 'DENY'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:16:05] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS DENIED. Replying 'DENY'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:16:29] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS GRANTED. Replying 'GRANT'
[SERIAL RX] [HOST RESPONSE] 'TIMEOUT'
[SERIAL RX] [RTC 2026-08-20 18:16:58] UID:0A 75 B4 02
-> Processing scan for UID: '0A 75 B4 02'
-> ACCESS GRANTED. Replying 'GRANT'

Aug 20, 2026 7:40 PM Sample 0A 75 B4 02 Granted ok
Aug 20, 2026 7:40 PM Sample 0A 75 B4 02 Granted ok
Aug 20, 2026 7:39 PM Sample 0A 75 B4 02 Denied inactive_user
Aug 20, 2026 7:39 PM Sample 0A 75 B4 02 Denied inactive_user
Aug 20, 2026 7:26 PM Sample 0A 75 B4 02 Denied inactive_user
Aug 20, 2026 7:24 PM Sample 0A 75 B4 02 Denied inactive_user
Aug 20, 2026 7:24 PM Sample 0A 75 B4 02 Denied inactive_user
Aug 20, 2026 7:24 PM Sample 0A 75 B4 02 Granted ok
Aug 20, 2026 7:24 PM Sample 0A 75 B4 02 Granted ok
Aug 20, 2026 7:21 PM Sample 0A 75 B4 02 Granted ok

- if i tap the rfid card it always welcome even its deactivated
- if i tap the rfid card the door is still locked even if the card is active
- ifi tap the exit button it i will open thats correct but if i tap again the rfid card now the welcome messgae is not responding and it doesnt record on the cmd taht i tap the card
