param (
	$game_directory,
	$repo
)

# Config
# Server ID number. This should match what's in your goon server config.
$server_id = 1

# Get the commit ID first
Set-Location "$game_directory\..\..\Repository"
$commit = git rev-parse --short HEAD
$commit = $commit.trim()

# Start by switching to the game's folder
Set-Location $game_directory

# Build file so I don't have to keep typing it
Write-Host "Resetting build file..."
$build_file = ".\_std\__build.dm"

# We have the technology to rebuild him.
New-Item -Path ".\_std\" -Name "__build.dm" -Force

Add-Content -Path $build_file -Value "#define DEBUG"

# Look for the file in the static files directory, where the map file will be
# If it exists, the first thing we'll write to the file is the map override define.
Write-Host "Setting map override..."
if((Test-Path -Path "..\..\Configuration\GameStaticFiles\data\map-override") -and (Get-Content -Path "..\..\Configuration\GameStaticFiles\data\map-override")) {
	# Should work(tm)
	Add-Content -Path $build_file -Value "#define MAP_OVERRIDE_$(Get-Content -Path '..\..\Configuration\GameStaticFiles\data\map-override')"
}

# Toggles for events
Write-Host "Setting event toggles..."
#Add-Content -Path $build_file -Value "#define RP_MODE"
#Add-Content -Path $build_file -Value "#define HALLOWEEN 1"
#Add-Content -Path $build_file -Value "#define XMAS 1"
#Add-Content -Path $build_file -Value "#define CANADADAY 1"
#Add-Content -Path $build_file -Value "#define FOOTBALL_MODE 1"

#Add-Content -Path $build_file -Value "#define ASS_JAM_ENABLED 1"

# Version Control
Write-Host "Setting version control..."
Add-Content -Path $build_file -Value "var/global/vcs_revision = `"$commit`""
# Without github API requests I can't get this, so we'll bodge it.
Add-Content -Path $build_file -Value "var/global/vcs_author = `"AuStation`""

# BYOND Version
Write-Host "Setting BYOND version..."
$byondVersion = "Error", "Error"
if((Test-Path -Path "..\..\Byond\ActiveVersion.txt") -and (Get-Content -Path "..\..\Byond\ActiveVersion.txt")) {
	# Should work(tm)
	$str = Get-Content -Path "..\..\Byond\ActiveVersion.txt"
	$byondVersion = $str.Split(".")
}
Add-Content -Path $build_file -Value "var/global/ci_dm_version_major = `"$($byondVersion[0])`""
Add-Content -Path $build_file -Value "var/global/ci_dm_version_minor = `"$($byondVersion[1])`""

# Build times
Write-Host "Setting timezone and build date/time..."
$timezone = Get-TimeZone
$timezone = $timezone[0]
Add-Content -Path $build_file -Value "#define BUILD_TIME_TIMEZONE_ALPHA `"$([Regex]::Replace($timezone.StandardName, '([A-Z])\w+\s*', '$1'))`"" # shamelessly stolen from SO
Add-Content -Path $build_file -Value "#define BUILD_TIME_TIMEZONE_OFFSET $(($timezone.BaseUtcOffset.Hours * 100) + ($timezone.BaseUtcOffset.Minutes))" # gamer math
Add-Content -Path $build_file -Value "#define BUILD_TIME_FULL `"$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')`""
Add-Content -Path $build_file -Value "#define BUILD_TIME_YEAR $(Get-Date -Format 'yyyy')"
Add-Content -Path $build_file -Value "#define BUILD_TIME_MONTH $(Get-Date -Format '%M')"
Add-Content -Path $build_file -Value "#define BUILD_TIME_DAY $(Get-Date -Format '%d')"
Add-Content -Path $build_file -Value "#define BUILD_TIME_HOUR $(Get-Date -Format '%H')"
Add-Content -Path $build_file -Value "#define BUILD_TIME_MINUTE $(Get-Date -Format '%m')"
Add-Content -Path $build_file -Value "#define BUILD_TIME_SECOND $(Get-Date -Format '%s')"
Add-Content -Path $build_file -Value "#define BUILD_TIME_UNIX $(Get-Date -UFormat %s -Millisecond 0)"

# Server ID
Write-Host "Setting server ID number..."
Add-Content -Path $build_file -Value "#define SERVER_NUMBER $server_id"

# Preload - This is just a static string
Write-Host "Setting preload URL..."
Add-Content -Path $build_file -Value "#define PRELOAD_RSC_URL `"http://rsc.austation.net/goonstation.zip`""

# With the build file updated, let's update the sound cache next...
# All we need to do is just run the script.
.\buildSoundList.ps1
