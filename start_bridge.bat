@echo off
title SDASFC Smart Door Serial Bridge
cd /d "%~dp0"
echo ==================================================
echo Starting SDASFC Hardware Serial Bridge...
echo ==================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0hardware\bridge\serial_bridge.ps1"
if errorlevel 1 (
    echo.
    echo Bridge exited with an error.
    pause
)
