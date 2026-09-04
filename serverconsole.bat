@echo off
setlocal
cd /d "%~dp0"
title Metal Slug Warzone - WorldServer Console

if not exist "_server_console" mkdir "_server_console" >nul 2>&1
if not exist "_server_console\events.ndjson" type nul > "_server_console\events.ndjson"

powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0serverconsole.ps1"
endlocal
