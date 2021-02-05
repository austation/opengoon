param (
	$game_directory
)

# CONFIG

# CDN Path is the location the built CDN files will be copied to.
$cdn_path = "C:\inetpub\wwwroot-rsc\goon-cdn"
# RSC Path is the path to send the RSC File zip to
$rsc_path = "C:\inetpub\wwwroot-rsc"
# Persistent node modules path. Location to copy the node_modules for compiling browserassets from
# This exists because on windows, "npm install" crashes out when installing SAAS, resulting in a broken installation, and requires manual installation.
# So while it may be jank, it does work.
$node_path = "C:\TGS\Persistent\browserassets\node_modules"

# Get the commit ID first
Set-Location "$game_directory\..\..\Repository"
$commit = git rev-parse --short HEAD

# First, recompile tgui
Write-Host "Recompiling tgui..."
Set-Location "$game_directory\tgui"

# Build tgui for production
.\bin\tgui.ps1

# This should have outputted the built tgui to browserassets. We can handle the CDN now.

# cd to it
Write-Host "Building and copying CDN files..."
Set-Location "$game_directory\browserassets"

# Now write the revision to file
New-Item -Path . -Name "revision" -Force
Add-Content -Path ".\revision" -Value "$commit"

# Symlink the cached node_modules folder
Write-Host "Symlinking node_modules folder..."
New-Item -ItemType SymbolicLink -Path . -Name "node_modules" -Value $node_path

# build the CDN with grunt
grunt build-cdn

# Make the folder
Write-Host "CDN built, making folder and copying files..."
New-Item -ItemType Directory -Path $cdn_path -Name $commit

# Copy the CDN back to its correct location, overwriting
Copy-Item -Path ".\build\*" -Destination "$cdn_path\$commit" -Recurse -Force

# Because image compression is disabled, we need to manually copy those...
Copy-Item -Path ".\images" -Destination "$cdn_path\$commit" -Recurse -Force

# Symlink the latest version of the CDN
New-Item -ItemType SymbolicLink -Path $cdn_path -Name "latest" -Value "$cdn_path\$commit" -Force

# While we're at it, let's rebuild the preload rsc file.

# cd to the main folder
Write-Host "Zipping and copying rsc file..."
Set-Location $game_directory

# zip it up. This requires 7z cli to be installed
7z a goonstation.zip goonstation.rsc

# Just copy that out to the the rsc path
Copy-Item -Path ".\goonstation.zip" -Destination $rsc_path -Force
