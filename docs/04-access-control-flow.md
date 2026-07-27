# Access Control Flow

## Sequence when a card is tapped

1. Arduino reads the RFID tag's UID via the RC522 module.
2. Arduino sends the UID over Serial (USB) to the bridge script running on the
   host PC.
3. Bridge script POSTs the UID to `app/api/rfid_scan.php`
   (e.g., `POST /app/api/rfid_scan.php` with body `{"rfid_uid": "A1B2C3D4"}`).
4. Backend logic (`AccessController` / `AccessLog` model):
   1. Look up `users` by `rfid_uid`.
      - Not found → result = `denied`, reason = `unknown_uid`.
   2. If found, check `users.status`.
      - `inactive` → result = `denied`, reason = `inactive_user`.
   3. Otherwise → result = `granted`, reason = `ok`.
5. Insert a row into `access_logs` regardless of outcome.
6. Backend responds to the bridge script with a simple JSON:
   `{"access": "granted"}` or `{"access": "denied", "reason": "unknown_uid"}`.
7. Bridge script relays a single character/command back to Arduino over Serial
   (e.g., `'1'` = unlock, `'0'` = stay locked).
8. Arduino actuates the relay/solenoid to unlock the door (briefly, e.g. 5
   seconds) only if it received the unlock command; otherwise it can flash an
   LED/buzzer to indicate denied access.

## Edge cases to handle
- Clock sync: PHP server time must be correct; recommend setting the timezone
  explicitly in `app/config/config.php` (`date_default_timezone_set('Asia/Manila')`)
  so `access_logs.scanned_at` timestamps are accurate.
- Access is not time-restricted: an active user's card opens the door at any hour.
  Revoking access means setting the user to `inactive` (or deleting the user).
- If the bridge script can't reach the backend (network/XAMPP down), Arduino
  should default to **deny** (fail-safe/locked) and optionally log locally.
