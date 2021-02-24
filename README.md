# OpenGoon Project
Open source tools and code to get gooncode servers working. Currently includes an API implementation, along with SQL schema plus event build scripts for TGS.

# Usage
## API
API setup is fairly simple. Basic step-by-step:
1. Set up a HTTP webserver and bind it to some port. For now, the API doesn't need to be public facing, and ideally shouldn't be. This might change in a future release.
2. Configure the web server with a recent version of PHP (7.4?), and *set your CORS headers* (Google for details regarding your specific server. If you use IIS, set the custom headers manually, don't use the extension).
3. Copy the api directory from this repository into the public html directory for your server.
4. Install and setup a MySQL (or MariaDB, at your own risk) SQL server and configure a new user with *standard* authentication, for the API to use.
5. Create a new database for the API and give the new user access. Then, executed the provided schema file on the new database to configure it for the API.
6. Update the `config.php` file in the API directory to set your desired options, mainly API key and database credentials. You'll also need to set the IP address(es) and port(s) for your servers here, to allow IP checking and callbacks.
7. Update your goonstation config file to point to the API and use the right key. You should point the primary API address, as well as the player notes config options to the API.
8. Done!

## TGS
The API comes with (optional) features to allow interfacing with TGSv4 for map switching, as well as some event scripts for TGSv4 to build TGUI and the CDN files with each new deployment, as well as update the build file. AuStation's goon fork also has some Discord functionality which can be configured in the TGSv4 control panel. Basic setup.
Make sure you have npm, yarn and 7zip CLI installed and added to the *system* path to ensure correct functionality. You'll also need to also install grunt to a folder that's in the system path too. Whether you add your default AppData npm dir or use a custom path is up to you.
1. Install a working TGSv4 deployment. Read the instructions on tgstation/tgstation-server for assistance.
3. Make an instance using a desired goon repo. Note: austation/goonstation has specific changes to allow for good TGS integration. You should fork from there.
2. Drop the event scripts from this repo's tgs folder into the EventScripts folder in the server instance's Configuration folder.
3. Edit the configuration in PostCompile.ps1 to change the output directory for the CDN, RSC and the location of the persistent node modules folder. The persistent node modules folder needs to be built by running `npm install` in the browserassets folder of the goonstation repo, resolving the several install errors present and copying it somewhere for storage.
4. Run a deployment in TGS to update everything.
5. Edit the API's `config.php` file to setup the correct TGS API version and address, as well as credentials. Also make sure to set the instance ID in the servers array.
6. Done! In a best case scenario, everything should work properly.

# Troubleshooting
Q: Icons in the changelog or possibly other panels aren't loading.
A: Your CORS headers probably aren't set. Follow a guide to set them for your chosen webserver.

Q: Map switching isn't working.
A: The map switcher requires a working TGS instance to be configured in the API configuration for map switching to work. Refer to the setup guide.

Q: CDN files aren't being built in the deployment folder, or copied to the chosen directory.
A: Make sure that you have grunt installed and the npm path it's installed to added to the *system* path.

# License
This project is licensed under the same license as Goonstation (CC-BY-NC-SA) for maximum compatibility. See the LICENSE file for details.
