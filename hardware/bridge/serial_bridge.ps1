# SDASFC — High Performance Native Windows Serial Bridge for ESP32
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

param(
    [string]$Port = "",
    [int]$Baud = 115200,
    [string]$ApiUrl = "http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php"
)

Clear-Host
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   SDASFC SMART DOOR AUTOMATION SYSTEM" -ForegroundColor Yellow
Write-Host "   Native Windows Hardware Serial Bridge (v2.0)" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# Auto-detect COM port if not specified
if ([string]::IsNullOrWhiteSpace($Port)) {
    $availablePorts = [System.IO.Ports.SerialPort]::GetPortNames()
    if ($availablePorts.Count -eq 0) {
        Write-Host "[ERROR] No COM ports detected! Please connect ESP32 USB cable." -ForegroundColor Red
        Write-Host "Press any key to exit..."
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
        exit 1
    }
    # Pick the highest COM port (usually the USB device, e.g. COM9)
    $Port = $availablePorts | Sort-Object -Descending | Select-Object -First 1
    Write-Host "[AUTO-DETECT] Found and selected port: $Port" -ForegroundColor Green
}

Write-Host " Target Port  : $Port" -ForegroundColor White
Write-Host " Baud Rate    : $Baud" -ForegroundColor White
Write-Host " API Endpoint : $ApiUrl" -ForegroundColor White
Write-Host "--------------------------------------------------" -ForegroundColor DarkGray

$serial = New-Object System.IO.Ports.SerialPort($Port, $Baud, [System.IO.Ports.Parity]::None, 8, [System.IO.Ports.StopBits]::One)
$serial.ReadTimeout = 1000
$serial.WriteTimeout = 1000
$serial.DtrEnable = $true
$serial.RtsEnable = $true
$serial.NewLine = "`n"

try {
    $serial.Open()
    Write-Host "[STATUS] Connected to $Port successfully!" -ForegroundColor Green
    Write-Host "[STATUS] Web Database Integration: ACTIVE" -ForegroundColor Green
    Write-Host "[STATUS] Ready! Listening for RFID scans & Exit events..." -ForegroundColor Yellow
    Write-Host "==================================================`n" -ForegroundColor DarkGray
} catch {
    Write-Host "[ERROR] Could not open $Port : $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "💡 Note: If Arduino IDE Serial Monitor is open, please CLOSE it." -ForegroundColor Yellow
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

function Handle-RfidScan([string]$uid) {
    Write-Host "`n--------------------------------------------------" -ForegroundColor DarkGray
    Write-Host "[CARD TAP] Scanned RFID UID: '$uid'" -ForegroundColor Yellow
    
    try {
        $body = @{ rfid_uid = $uid } | ConvertTo-Json
        $response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $body -ContentType "application/json" -TimeoutSec 4

        if ($response.access -eq "granted") {
            Write-Host "[WEB API]  Decision: ACCESS GRANTED (Reason: $($response.reason))" -ForegroundColor Green
            Write-Host "[ESP32]    Sending 'GRANT' command -> Unlocking Door..." -ForegroundColor Green
            $serial.WriteLine("GRANT")
        } else {
            Write-Host "[WEB API]  Decision: ACCESS DENIED (Reason: $($response.reason))" -ForegroundColor Red
            Write-Host "[ESP32]    Sending 'DENY' command -> Door Stays Locked." -ForegroundColor Red
            $serial.WriteLine("DENY")
        }
    } catch {
        Write-Host "[WEB API ERROR] Failed to connect to $ApiUrl : $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "[ESP32] Sending 'DENY' fallback." -ForegroundColor Red
        $serial.WriteLine("DENY")
    }
    Write-Host "--------------------------------------------------`n" -ForegroundColor DarkGray
}

try {
    while ($serial.IsOpen) {
        try {
            $line = $serial.ReadLine().Trim()
            if ([string]::IsNullOrWhiteSpace($line)) { continue }

            # Match RFID UID (e.g. UID:0A 75 B4 02 or UID:C6 85 C6 01)
            if ($line -match "UID\s*:\s*([A-F0-9\s]+)") {
                $scannedUid = $matches[1].Trim()
                Handle-RfidScan -uid $scannedUid
            }
            elseif ($line -match "EVENT:EXIT_BUTTON") {
                Write-Host "[EVENT] No-Touch IR Exit Sensor triggered! (Door Unlocked)" -ForegroundColor Green
            }
            elseif ($line -match "DOOR is UNLOCKED") {
                Write-Host "[HARDWARE] 🔓 Relay ON: Door is Open" -ForegroundColor Green
            }
            elseif ($line -match "DOOR is LOCKED") {
                Write-Host "[HARDWARE] 🔒 Relay OFF: Door is Locked" -ForegroundColor DarkGray
            }
            else {
                Write-Host "[ESP32 LOG] $line" -ForegroundColor DarkCyan
            }
        } catch [System.TimeoutException] {
            # Normal idle timeout, continue loop
        } catch {
            Write-Host "[SERIAL WARN] $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
} finally {
    if ($serial.IsOpen) {
        $serial.Close()
        Write-Host "`n[STATUS] Serial port $Port closed." -ForegroundColor Yellow
    }
}
