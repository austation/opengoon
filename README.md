# OpenGoon Project
Open source tools and code to get gooncode servers working. Currently includes an API implementation, along with SQL schema.

# Usage
Because it's PHP, usage is braindead simple, as long as you have a DB setup.
1. Install a HTTP server like apache, nginx or IIS, make a site and bind it to **HTTP**. BYOND communicates over insecure HTTP, and can't use HTTPS.
2. Setup the latest version of PHP on your server (probably 7.4). Follow a relevant guide for this.
3. Copy and paste the api folder into the public HTML directory of your server.
4. Install a MySQL derived SQL server, like MySQL itself or MariaDB, and create a new database, and a user that uses **standard** authentication, with access to the new database.
5. Edit the `config.php` file in the web server's api folder to setup the auth key and SQL server authentication. You'll also need to configure the IP and Ports of your goon server. Note, the key (number) in the list is the "Server Key" for the matching goon server.
6. Next, edit the goon config file for the server. I'll add an example soon, but mainly, set the goonhub api key to the one used in the config, set the goonhub api endpoint to the address of your API server, set the raw IP address for your api too, set the notes api endpoint to the same address as the main goonhub api, except append `/api/notes` on the end, and set the notes api key to the **MD5 Hash** of the main api key, because gooncode doesn't actually hash the key for the notes api. You can read the configuration.dm file in gooncode for the full list of options, a lot of which aren't actually in the default config.txt for unknown reasons.
7. Finally, with config done, connect into your SQL server and execute the provided SQL file to generate the schema the API will use.
8. Start up the goon server and enjoy API functionality for bans, antag rep, player information, notes, critter gauntlet, numbers station and jobbans!

## Regarding Ban Panel
The panel (and almost all other html interfaces ingame for that matter) is loaded from goon's CDN server.
Not using the CDN will break a lot of icons and interfaces in game that rely on it.
The problem is that the ban panel requires some coding changes to work with the new API address (without DNS overrides).
These changes are not reflected on the goonstation CDN, so if you want all interfaces to work AND the ban panel to work, you'll need to host your own CDN.
I scraped the files from goon's CDN, but for ethical and copyright purposes I won't be hosting them here. If you're interested, shoot me a DM on Discord (Terra#4852) and I can send you a copy of the files if needed.
Suffice it to say, you'll want to run the server through cloudflare or a similar service to decrease origin load.

Also note, the CDN address is actually hardcoded into the server in a few spots. You'll need to change the address to match yours. You can see an example on austation's goon fork [https://github.com/austation/goonstation/commit/77c1b4b58878c09c0c98c9a2bfd09eea37baba33](here).

# Known Issues
Currently, there are a few issues I'm aware of. Firstly, VPN checks don't work quite now but I was finishing the rest of the API and haven't looked into it just yet, because it's low priority. Second, the ban panel doesn't work quite yet because I didn't find it until late in development, since it uses a hardcoded API address and is written in JS. Also it has terrible logging because I wanted to announce this for internet points, I will polish this very soon, once I get all features in. Also I need to add cloud saves.

# Disclaimer
I wrote this API out of desire to create my own, since bee is making theirs closed source until some time next year at least. I don't yet know how well this will hold up in production, but I will probably run an austation goon server at some point to test things out and finally experience low ping gooncode.
