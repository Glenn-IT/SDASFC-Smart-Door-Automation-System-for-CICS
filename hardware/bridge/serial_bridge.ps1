param(
    [string]$Port = "COM9",
    [int]$Baud = 115200,
    [string]$ApiUrl = "http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php"
)

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " SDASFC Native Windows Serial Bridge (ESP32)" -ForegroundColor Cyan
Write-Host " Target Port  : $Port" -ForegroundColor White
Write-Host " Baud Rate    : $Baud" -ForegroundColor White
Write-Host " API Endpoint : $ApiUrl" -ForegroundColor White
Write-Host "==================================================" -ForegroundColor Cyan

$availablePorts = [System.IO.Ports.SerialPort]::GetPortNames()
if ($Port -notin $availablePorts) {
    Write-Host "[ERROR] Port $Port not found. Available: $($availablePorts -join ', ')" -ForegroundColor Red
    exit 1
}

$serial = New-Object System.IO.Ports.SerialPort
$serial.PortName = $Port
$serial.BaudRate = $Baud
$serial.DataBits = 8
$serial.StopBits = [System.IO.Ports.StopBits]::One
$serial.Parity = [System.IO.Ports.Parity]::None
$serial.Handshake = [System.IO.Ports.Handshake]::None
$serial.ReadTimeout = 500
$serial.WriteTimeout = 1000
$serial.DtrEnable = $true
$serial.RtsEnable = $true

try {
    $serial.Open()
    Write-Host "[STATUS] Connected to $Port successfully!" -ForegroundColor Green
    Write-Host "[STATUS] Listening for RFID scans, Exit sensor, and System events..." -ForegroundColor Green
    Write-Host "--------------------------------------------------" -ForegroundColor DarkGray
}
catch {
    Write-Host "[ERROR] Could not open $Port : $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "💡 If Arduino IDE Serial Monitor is open, please CLOSE it so this bridge can use the port." -ForegroundColor Yellow
    exit 1
}

function Process-RfidScan([string]$uid) {
    Write-Host " ➔ Processing RFID Scan: '$uid'" -ForegroundColor Yellow
    try {
        $body = @{ rfid_uid = $uid } | ConvertTo-Json
        $response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $body -ContentType "application/json" -TimeoutSec 5
        if ($response.access -eq "granted") {
            Write-Host " ➔ Decision: ACCESS GRANTED ($($response.reason)). Replying 'GRANT'" -ForegroundColor Green
            $serial.WriteLine("GRANT")
        }
        else {
            Write-Host " ➔ Decision: ACCESS DENIED ($($response.reason)). Replying 'DENY'" -ForegroundColor Red
            $serial.WriteLine("DENY")
        }
    }
    catch {
        Write-Host " ➔ [API ERROR] Could not reach Web API: $($_.Exception.Message)" -ForegroundColor Red
        $serial.WriteLine("DENY")
    }
}

try {
    while ($serial.IsOpen) {
        try {
            $line = $serial.ReadLine().Trim()
            if ($line.Length -gt 0) {
                Write-Host "[SERIAL RX] $line" -ForegroundColor DarkCyan

                if ($line -match "UID:\s*([A-F0-9\s]+)") {
                    $scannedUid = $matches[1].Trim()
                    Process-RfidScan -uid $scannedUid
                }
                elseif ($line -match "EVENT:EXIT_BUTTON") {
                    Write-Host " ➔ [EVENT] No-Touch IR Exit Sensor triggered!" -ForegroundColor Green
                }
                elseif ($line -match "EVENT:DOOR_UNLOCKED") {
                    Write-Host " ➔ [DOOR] Relay Energized (Door Unlocked)" -ForegroundColor Green
                }
                elseif ($line -match "EVENT:DOOR_LOCKED") {
                    Write-Host " ➔ [DOOR] Relay De-energized (Door Locked)" -ForegroundColor DarkGray
                }
            }
        }
        catch [System.TimeoutException] {
            # normal read timeout
        }
        catch {
            Write-Host "[WARN] Read error: $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
}
finally {
    if ($serial.IsOpen) {
        $serial.Close()
        Write-Host "[STATUS] Serial port closed." -ForegroundColor Yellow
    }
}
