Set-StrictMode -Version 2.0
$ErrorActionPreference = 'SilentlyContinue'

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$FeedDir = Join-Path $Root '_server_console'
$FeedPath = Join-Path $FeedDir 'events.ndjson'

if (-not (Test-Path -LiteralPath $FeedDir)) { New-Item -ItemType Directory -Path $FeedDir -Force | Out-Null }
if (-not (Test-Path -LiteralPath $FeedPath)) { New-Item -ItemType File -Path $FeedPath -Force | Out-Null }

$host.UI.RawUI.WindowTitle = 'Metal Slug Warzone - WorldServer Console'

function Write-Part([string]$Text, [ConsoleColor]$Color = [ConsoleColor]::Gray, [switch]$NoNewline) {
    $old = [Console]::ForegroundColor
    [Console]::ForegroundColor = $Color
    if ($NoNewline) { [Console]::Write($Text) } else { [Console]::WriteLine($Text) }
    [Console]::ForegroundColor = $old
}

function Category-Color([string]$Category) {
    switch ($Category.ToUpperInvariant()) {
        'AUTH'       { return [ConsoleColor]::Green }
        'WEB'        { return [ConsoleColor]::DarkGray }
        'COMBAT'     { return [ConsoleColor]::Red }
        'RECOVERY'   { return [ConsoleColor]::Magenta }
        'MISSION'    { return [ConsoleColor]::Yellow }
        'R&D'        { return [ConsoleColor]::Cyan }
        'BASE'       { return [ConsoleColor]::Green }
        'DISPATCH'   { return [ConsoleColor]::Blue }
        'STRATEGIC'  { return [ConsoleColor]::DarkYellow }
        'FOB'        { return [ConsoleColor]::DarkMagenta }
        'PVP'        { return [ConsoleColor]::DarkRed }
        'SOCIAL'     { return [ConsoleColor]::DarkCyan }
        'PROFILE'    { return [ConsoleColor]::Cyan }
        default      { return [ConsoleColor]::Gray }
    }
}

function Show-Banner {
    Clear-Host
    Write-Part '===============================================================================' ([ConsoleColor]::DarkCyan)
    Write-Part ' METAL SLUG WARZONE // LOCAL WORLDSERVER ACTIVITY CONSOLE' ([ConsoleColor]::Cyan)
    Write-Part '===============================================================================' ([ConsoleColor]::DarkCyan)
    Write-Part ' Human player traffic + gameplay actions only. Movement/presence/error logs are excluded.' ([ConsoleColor]::Gray)
    Write-Part ' Local filesystem feed only; there is no remote browser console endpoint.' ([ConsoleColor]::DarkGray)
    Write-Part ' Keys: [C] Clear screen   [Q] Quit' ([ConsoleColor]::DarkGray)
    Write-Part ''
    Write-Part ' COLOR CHANNELS' ([ConsoleColor]::White)
    Write-Part ' AUTH ' ([ConsoleColor]::Green) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'COMBAT ' ([ConsoleColor]::Red) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'RECOVERY ' ([ConsoleColor]::Magenta) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'MISSION ' ([ConsoleColor]::Yellow) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'R&D ' ([ConsoleColor]::Cyan) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'BASE ' ([ConsoleColor]::Green) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'DISPATCH ' ([ConsoleColor]::Blue) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'FOB ' ([ConsoleColor]::DarkMagenta) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'PVP ' ([ConsoleColor]::DarkRed) -NoNewline; Write-Part '  ' -NoNewline
    Write-Part 'SOCIAL' ([ConsoleColor]::DarkCyan)
    Write-Part '-------------------------------------------------------------------------------' ([ConsoleColor]::DarkGray)
}

function Format-Time($Value) {
    try { return ([DateTimeOffset]::Parse([string]$Value)).ToLocalTime().ToString('HH:mm:ss') }
    catch { return (Get-Date).ToString('HH:mm:ss') }
}

function Render-Event([string]$Line) {
    if ([string]::IsNullOrWhiteSpace($Line)) { return }
    try { $evt = $Line | ConvertFrom-Json } catch { return }
    if ($null -eq $evt) { return }

    $category = [string]$evt.category
    if ([string]::IsNullOrWhiteSpace($category)) { return }
    if ($category -match 'ERROR|EXCEPTION|WARNING|NOTICE|DEBUG') { return }

    $color = Category-Color $category
    $time = Format-Time $evt.ts
    $player = [string]$evt.player
    $playerId = [int]$evt.player_id
    $ip = [string]$evt.ip
    $action = [string]$evt.action
    $message = [string]$evt.message
    $route = [string]$evt.route

    Write-Part ('[{0}] ' -f $time) ([ConsoleColor]::DarkGray) -NoNewline
    Write-Part ('[{0,-10}] ' -f $category) $color -NoNewline
    Write-Part ('[{0}#{1}@{2}] ' -f $player,$playerId,$ip) ([ConsoleColor]::White) -NoNewline

    if ($category.ToUpperInvariant() -eq 'WEB') {
        Write-Part ('{0,-5} ' -f $action) ([ConsoleColor]::DarkGray) -NoNewline
        Write-Part $route ([ConsoleColor]::Gray) -NoNewline
        if (-not [string]::IsNullOrWhiteSpace($message)) {
            Write-Part ('  {0}' -f $message) ([ConsoleColor]::DarkGray) -NoNewline
        }
        if ($null -ne $evt.meta -and $null -ne $evt.meta.ms) {
            Write-Part ('  ({0} ms)' -f [int]$evt.meta.ms) ([ConsoleColor]::DarkGray)
        } else { Write-Part '' }
        return
    }

    Write-Part ('{0,-12} ' -f $action) $color -NoNewline
    Write-Part $message ([ConsoleColor]::Gray)
}

Show-Banner

# Show recent useful history on launch without replaying the entire bounded feed.
Get-Content -LiteralPath $FeedPath -Tail 80 | ForEach-Object { Render-Event $_ }

$info = Get-Item -LiteralPath $FeedPath
$position = if ($info) { [int64]$info.Length } else { [int64]0 }
$identity = if ($info) { [string]$info.CreationTimeUtc.Ticks } else { '' }

while ($true) {
    if ([Console]::KeyAvailable) {
        $key = [Console]::ReadKey($true)
        if ($key.Key -eq [ConsoleKey]::Q) { break }
        if ($key.Key -eq [ConsoleKey]::C) { Show-Banner }
    }

    $info = Get-Item -LiteralPath $FeedPath
    if ($null -eq $info) { Start-Sleep -Milliseconds 250; continue }

    $currentIdentity = [string]$info.CreationTimeUtc.Ticks
    if ($currentIdentity -ne $identity -or [int64]$info.Length -lt $position) {
        $position = 0
        $identity = $currentIdentity
    }

    if ([int64]$info.Length -gt $position) {
        $share = [System.IO.FileShare]::ReadWrite -bor [System.IO.FileShare]::Delete
        $stream = [System.IO.File]::Open($FeedPath,[System.IO.FileMode]::Open,[System.IO.FileAccess]::Read,$share)
        if ($stream) {
            [void]$stream.Seek($position,[System.IO.SeekOrigin]::Begin)
            $reader = New-Object -TypeName System.IO.StreamReader -ArgumentList $stream
            while (-not $reader.EndOfStream) {
                $line = $reader.ReadLine()
                Render-Event $line
            }
            $position = [int64]$stream.Length
            $reader.Dispose()
            $stream.Dispose()
        }
    }

    Start-Sleep -Milliseconds 250
}

Write-Part ''
Write-Part 'Local WorldServer console closed.' ([ConsoleColor]::DarkGray)
