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

# First, recompile tgui
Write-Host "Recompiling tgui..."
cd "$game_directory\tgui"

# Build tgui for production
.\bin\tgui.ps1

# This should have outputted the built tgui to browserassets. We can handle the CDN now.

# cd to it
Write-Host "Building and copying CDN files..."
cd "$game_directory\browserassets"

# Now copy the node modules folder in. This might take a bit.
Copy-Item -Path $node_path -Destination "." -Recurse

# build the CDN with grunt
grunt build-cdn

# Copy the CDN back to its correct location, overwriting
Copy-Item -Path ".\build\*" -Destination $cdn_path -Recurse -Force

# Because image compression is disabled, we need to manually copy those...
Copy-Item -Path ".\images" -Destination $cdn_path -Recurse -Force

# While we're at it, let's rebuild the preload rsc file.

# cd to the main folder
Write-Host "Zipping and copying rsc file..."
cd $game_directory

# zip it up. This requires 7z cli to be installed
7z a goonstation.zip goonstation.rsc

# Just copy that out to the the rsc path
Copy-Item -Path ".\goonstation.zip" -Destination $rsc_path -Force
