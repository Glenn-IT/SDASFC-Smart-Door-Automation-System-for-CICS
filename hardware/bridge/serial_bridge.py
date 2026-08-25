#!/usr/bin/env python3
"""
SDASFC — Hybrid Serial Bridge Script (Python)
Listens for RFID scan data on the USB Serial port from ESP32,
calls the SDASFC web API, sends GRANT/DENY, and synchronizes active
user whitelists to ESP32 Flash memory for standalone/offline operation.
"""

import sys
import time
import json
import argparse
import urllib.request
import urllib.parse
import serial
import serial.tools.list_ports

DEFAULT_API_URL = "http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php"
DEFAULT_WHITELIST_URL = "http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/whitelist.php"
DEFAULT_BAUD = 115200

def find_arduino_port():
    ports = list(serial.tools.list_ports.comports())
    for p in ports:
        if any(keyword in p.description for keyword in ["ESP32", "CP210", "CH340", "FT232", "USB Serial", "Arduino"]):
            return p.device
    if len(ports) > 0:
        return ports[0].device
    return "COM3"

def send_api_request(api_url, uid):
    payload = json.dumps({"rfid_uid": uid}).encode('utf-8')
    req = urllib.request.Request(
        api_url,
        data=payload,
        headers={"Content-Type": "application/json", "User-Agent": "SDASFC-SerialBridge/2.0"}
    )
    try:
        with urllib.request.urlopen(req, timeout=4) as response:
            res_body = response.read().decode('utf-8')
            data = json.loads(res_body)
            return data.get("access") == "granted"
    except Exception as e:
        print(f"[API ERROR] Failed to connect to Web API: {e}")
        return False

def sync_whitelist(ser, whitelist_url):
    try:
        print(f"[SYNC] Fetching active authorized cards from database...")
        req = urllib.request.Request(whitelist_url, headers={"User-Agent": "SDASFC-SerialBridge/2.0"})
        with urllib.request.urlopen(req, timeout=4) as response:
            data = json.loads(response.read().decode('utf-8'))
            if data.get("status") == "success" and "uids" in data:
                uids_csv = ",".join(data["uids"])
                sync_cmd = f"SYNC_WHITELIST:{uids_csv}\n"
                ser.write(sync_cmd.encode('utf-8'))
                ser.flush()
                print(f"[SYNC] ✅ Synced {data.get('count', 0)} active card(s) to ESP32 Flash Memory!")
    except Exception as e:
        print(f"[SYNC ERROR] Failed to sync whitelist: {e}")

def main():
    parser = argparse.ArgumentParser(description="SDASFC Hybrid Serial Bridge for ESP32 Door Lock")
    parser.add_argument("--port", help="Serial COM Port (e.g. COM3 or /dev/ttyUSB0)")
    parser.add_argument("--baud", type=int, default=DEFAULT_BAUD, help="Serial Baud rate (default 115200)")
    parser.add_argument("--url", default=DEFAULT_API_URL, help="SDASFC API Scan Endpoint URL")
    parser.add_argument("--whitelist-url", default=DEFAULT_WHITELIST_URL, help="SDASFC Whitelist Endpoint URL")
    args = parser.parse_args()

    port = args.port if args.port else find_arduino_port()
    print(f"==================================================")
    print(f" SDASFC Hybrid Hardware Serial Bridge (ESP32)")
    print(f" Target Port       : {port}")
    print(f" Baud Rate         : {args.baud}")
    print(f" Scan API URL      : {args.url}")
    print(f" Whitelist API URL : {args.whitelist_url}")
    print(f"==================================================")

    try:
        ser = serial.Serial(port, args.baud, timeout=1)
        time.sleep(2) # Wait for ESP32 stabilization
        print(f"[STATUS] Connected to {port}. Synchronizing whitelist...")
        sync_whitelist(ser, args.whitelist_url)
        print(f"[STATUS] Ready! Listening for RFID scans and Exit events...\n")
    except Exception as e:
        print(f"[ERROR] Could not open serial port {port}: {e}")
        print("Available COM ports:")
        for p in serial.tools.list_ports.comports():
            print(f" - {p.device}: {p.description}")
        sys.exit(1)

    while True:
        try:
            if ser.in_waiting > 0:
                line = ser.readline().decode('utf-8', errors='ignore').strip()
                if not line:
                    continue
                
                print(f"[SERIAL RX] {line}")

                import re
                if line.startswith("REQ:SYNC") or line == "SYNC":
                    print("[ESP32 REQUEST] Whitelist synchronization requested.")
                    sync_whitelist(ser, args.whitelist_url)
                else:
                    match = re.search(r"UID:\s*([A-F0-9\s]+)", line, re.IGNORECASE)
                    if match:
                        uid = match.group(1).strip()
                        print(f" -> Processing RFID scan: '{uid}'")
                        
                        is_granted = send_api_request(args.url, uid)
                        
                        if is_granted:
                            print(" -> Decision: ACCESS GRANTED. Sending 'GRANT' to ESP32.")
                            ser.write(b"GRANT\n")
                            ser.flush()
                        else:
                            print(" -> Decision: ACCESS DENIED. Sending 'DENY' to ESP32.")
                            ser.write(b"DENY\n")
                            ser.flush()
                    elif "EVENT:" in line:
                        print(f" -> [HARDWARE EVENT]: {line}")
            time.sleep(0.05)
        except KeyboardInterrupt:
            print("\n[STATUS] Stopping Serial Bridge.")
            ser.close()
            break
        except Exception as e:
            print(f"[ERROR] Loop error: {e}")
            time.sleep(1)

if __name__ == "__main__":
    main()
