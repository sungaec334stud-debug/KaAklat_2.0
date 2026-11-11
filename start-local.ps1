# Starts the maintained local pilot; never seeds, imports or changes database records.
[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$phpExecutable = 'C:\php-8.4.14\php.exe'
$privateConfig = Join-Path $PSScriptRoot 'private\config.local.php'
$pilotSessions = Join-Path $PSScriptRoot 'private\pilot-sessions'

if (-not (Test-Path -LiteralPath $phpExecutable -PathType Leaf)) {
    throw "Selected PHP executable not found: $phpExecutable"
}
if (-not (Test-Path -LiteralPath $privateConfig -PathType Leaf)) {
    throw 'Local configuration is missing. Follow README.md; never import over an existing pilot.'
}
& $phpExecutable (Join-Path $PSScriptRoot 'bin\check_local_pilot.php')
if ($LASTEXITCODE -ne 0) { throw 'Pilot configuration guard failed; no server was started.' }
if (-not (Test-Path -LiteralPath $pilotSessions -PathType Container)) {
    throw 'Private pilot session directory is missing. Restore/setup this directory before starting.'
}

Write-Host 'Starting KaAklat pilot at http://127.0.0.1:8000. Keep this terminal open; Ctrl+C stops it.'
& $phpExecutable -d "session.save_path=$pilotSessions" -S 127.0.0.1:8000 -t (Join-Path $PSScriptRoot 'public')
exit $LASTEXITCODE
