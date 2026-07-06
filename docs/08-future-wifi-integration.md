# Future Enhancement — WiFi (Local Network) Arduino Integration

> Status: not part of the current build. USB Serial + bridge script
> (`05-arduino-integration.md`) is the active design for v1. This doc captures
> the plan for a future upgrade once the core system is working.

## Idea
Replace the USB-tethered Arduino Uno + serial bridge script with an
ESP8266/ESP32-based reader that joins the **same local WiFi network** as the
laptop running XAMPP, and calls the backend API directly over HTTP — no
serial bridge, no internet exposure, no USB cable running to the door.

## Why local WiFi, not public/online hosting
- This is a physical access-control system for one building/door — there's no
  need for it to be reachable from the internet.
- Staying on the local network avoids the security exposure of putting an
  RFID access system on a public server, avoids hosting costs, and avoids
  depending on internet uptime for something that should keep working even if
  the internet connection drops.
- Online hosting would only make sense if the admin needed to check the
  dashboard from outside the local network — not a stated requirement.

## What changes vs. the current design
- **Hardware:** Arduino Uno/Nano → ESP8266 (e.g., NodeMCU) or ESP32, which has
  built-in WiFi. RC522 wiring stays conceptually the same (SPI), pin numbers
  differ per board.
- **Sketch behavior:** instead of writing `UID:...` to Serial, the ESP
  connects to the WiFi network (SSID/password stored in the sketch) and sends
  an HTTP POST directly to `http://<laptop-local-ip>/SDASFC.../app/api/rfid_scan.php`
  with the UID, then parses the JSON response (`granted`/`denied`) to drive
  the relay — no PC bridge script needed at all.
- **Network requirement:** the laptop running XAMPP must have a fixed/known
  local IP (or use mDNS/hostname) on the WiFi router so the ESP always knows
  where to send requests.
- **Removed component:** `hardware/bridge/serial_bridge.php` (or `.py`) is no
  longer needed in this design — delete once migrated.

## Migration trigger
Revisit this once Phases 1–6 in `07-development-plan.md` are complete and the
USB-Serial version works end-to-end. Swapping the transport layer at that
point only touches the Arduino sketch and removes the bridge script — the
backend API (`app/api/rfid_scan.php`) and all admin panel logic stay the same.
