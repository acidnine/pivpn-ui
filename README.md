
# pivpn-ui (Wireguard)

pivpn-ui is a web app which makes managing clients more convenient for PiVPN.

Runs on apache/php/sqlite3, no docker required `¯\_(ツ)_/¯`, as simple as copy/pasting a few commands into the terminal.

<img src="./img/screenshot.png"/>

## Functions

pivpn-ui functions:

- [x] Initial setup to manage users from an sqlite database.
- [x] Manage sqlite user name and password.
- [x] Create clients for Wireguard configurations.
- [x] Enable / Disable / Delete clients.
- [x] Display configurations as QR codes.
- [x] Download configuration files.
- [x] Copy client configuration file contents to the clipboard.
- [x] View client details.
- [x] Responsive interface which should work well with tiny screens.
- [x] Verify Wireguard integration works.

## Planned

- [ ] Create an installer script, commands are already copy/paste ready.
- [ ] Logging for all actions taken, not just login.
- [ ] Implement the ability to add more sqlite users.
- [ ] Permissions and management of said user permissions.
- [ ] Replace Moment.js with native javascript.
- [ ] Consider adding OpenVPN (or just use /pivpn-web/ )...
- [ ] Utilize lighttpd instead of apache..

## Requirements

- Debian OS (Raspbian OS, Debian, Ubuntu)
- PiVPN installed and configured with Wireguard.
- Apache2, PHP, PHP-SQLite3, Git

## Installation

To install pivpn-ui, follow these steps (commands can be copy/pasted into the shell).

1.  Install PiVPN and follow the instructions to configure Wireguard.
```bash
curl -L https://install.pivpn.io | bash
```
2.  Install Apache2, PHP and Git.
```bash
sudo apt update -y && sudo apt install apache2 php php-sqlite3 git -y
```
3.  Edit the file `/etc/apache2/apache2.conf`  and change the default `User` and `Group` to your user and group.
```bash
sudo cp /etc/apache2/apache2.conf /etc/apache2/apache2.conf.bak
sudo sed -i -e "s@User \${APACHE_RUN_USER}@User $(`echo id -un`)@g" /etc/apache2/apache2.conf
sudo sed -i -e "s@Group \${APACHE_RUN_GROUP}@Group $(`echo id -gn`)@g" /etc/apache2/apache2.conf
```
4. Restart apache.
```bash
sudo systemctl restart apache2.service
```
5. Clone this repository.
```bash
sudo git clone https://github.com/acidnine/pivpn-ui.git /var/www/html/pivpn-ui/
```
6. Change permissions of the folder.
```bash
sudo chown -R $(id -un):$(id -gn) /var/www/html/pivpn-ui/
```
<!--
7. Remove the requirement to enter a password when using sudo: (leaving comment because the command was hard to figure out)
```bash
echo "$(id -un) ALL=(ALL) NOPASSWD:/opt/pivpn/openvpn/*" | sudo EDITOR='tee -a' visudo
```
-->
7. Create a redirect from the site root if you aren't using the Pi for other web apps.
```bash
sudo sh -c 'echo "<?php header(\"Location: /pivpn-ui/\"); ?>" > /var/www/html/index.php'
sudo mv /var/www/html/index.html /var/www/html/index.html.bak
```
7. [ALT] If using Pi-Hole as well, you can do this:
```bash
sudo printf '<!DOCTYPE html>\n<html lang="en">\n  <head>\n    <meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n    <meta http-equiv="X-UA-Compatible" content="ie=edge">\n    <title>PiHome</title>\n    <style>\n      *,::after,::before{box-sizing:border-box}*{margin:0}body{line-height:1.5;-webkit-font-smoothing:antialiased}canvas,img,picture,svg,video{display:block;max-width:100\%}button,input,select,textarea{font:inherit}h1,h2,h3,h4,h5,h6,p{overflow-wrap:break-word}p{text-wrap:pretty}h1,h2,h3,h4,h5,h6{text-wrap:balance}#__next,#root{isolation:isolate}\n      body {\n        font-size: 2rem;\n      }\n      main {\n        margin: 0 auto;\n        padding-top: 20vh;\n        text-align: center;\n      }\n      a {\n        padding: 10px 20px;\n        border: 1px outset buttonborder;\n        border-radius: 3px;\n        color: buttontext;\n        background-color: buttonface;\n        text-decoration: none;\n      }\n    </style>\n  </head>\n  <body>\n    <main>\n      <a href="/pivpn-ui/">pivpn-ui</a> &nbsp;&nbsp;&nbsp; <a href="/admin/">pi-hole</a>\n    </main>\n  </body>\n</html>\n' > /var/www/html/index.php
sudo mv /var/www/html/index.html /var/www/html/index.html.bak
```

## Usage

To use pivpn-ui, follow these steps:

1. Open your web browser and navigate to `http://localhost/pivpn-ui/` (or the appropriate IP address if running remotely).
2. Complete the initial user creation to login and enjoy the ease of management.

## Credit

- General ideas/inspiration: https://github.com/g8998/pivpn-web
- JS Libraries: jQuery, Bootstrap, https://github.com/moment/moment, https://github.com/jeromeetienne/jquery-qrcode 
