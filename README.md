>>This software is an Alpha version and we do not recommend the usage if you don't know what you are doing.
# HOW TO INSTALL?
### CLIENT PRE-REQUISITE:
sudo apt-get install fail2ban php7.0 php-mysqli
### MYSQL SERVER PRE-REQUISITE: 
sudo apt-get install fail2ban php7.0 php-mysqli mysql-server

### Setting up the mySql server:
When you install a fresh mySql server the default configuration disallow connection from "non-localhost" devices.
To handle that:
```sh
$ sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```
>> FIND: 
bind-address = 127.0.0.1
REPLACE IT WITH:
bind-address = 0.0.0.0

Restart the mySql server to make it effective.
```sh
$ sudo systemctl restart mysql.service
```
Create the in the mySql DB:
```
CREATE TABLE IF NOT EXISTS `fail2ban` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `protocol` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `port` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hostname` (`hostname`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
### Setting up each client:
Clone the repository:
```sh
git clone https://github.com/mydomo/fail2ban-central.git
```
Fill "config.default.php" with your data and rename it to "config.php"
```sh
cd fail2ban-central/
sudo nano config.default.php
```
>> EDIT WITH MYSQL DATA
save as config.php
---
IF NOT DONE BEFORE CREATE YOUR JAIL
```sh
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
```
EDIT  IT
```sh
sudo nano /etc/fail2ban/jail.local
```
EXAMPLE JAIL:
```
[fail2ban-central]
# ban ip passed by the script
enabled   = true
filter    = fail2ban-central
banaction = iptables-allports
logpath  = /var/log/fail2ban-central.log
maxretry = 1
findtime = 3600
bantime = 3600
```
Now we have to edit "action" iptables-allports:
```sh
sudo nano /etc/fail2ban/action.d/iptables-allports.conf
```
>>Find:
actionban = <iptables> -I f2b-<name> 1 -s <ip> -j <blocktype>"
>>Replace with:
actionban = <iptables> -I f2b-<name> 1 -s <ip> -j <blocktype>
            php /home/domoticz/fail2ban-central/fail2ban.php <name> <protocol> <port> <ip>

CTRL+X to save it

we need also to create a filter:
```
sudo nano /etc/fail2ban/filter.d/fail2ban-central.conf
```
EXAMPLE FILTER:
```
[Definition]
failregex = ^\[\w{1,3}.\w{1,3}.\d{1,2}.\d{1,2}:\d{1,2}:\d{1,2} \d{1,4}. \[error] \[client.<HOST>].File does not exist:.{1,40}roundcube.{1,200}
ignoreregex =
```
Create the log file:
```
sudo touch /var/log/fail2ban-central.log
```
RESTART TO SEE IF IS WORKING:
```
sudo service fail2ban restart
```
IF NOT CHECK THE LOG:

Now to run the script each 1 minute:
Check the PHP path with the command "which php" in terminal
```
sudo crontab -e
```
```
*/1 * * * * /usr/bin/php /home/user/fail2ban-central/cron2ban.php
```

TO SEE BANNED IP:
```
sudo iptables -L
```

### Cloudflare Integration
If you run CloudFlare it's necessary to sent thru the API the banned IP in order to make Clouflare ban them aswell.
Fail2Ban has an Action that can be used, but uses V1 API, the following use V4

First install cURL as it's a prerequisite
```
sudo apt-get update
sudo apt-get install curl -y
```
#### Now do some tests to see if works
TEST BAN (Replace <REPLACE WITH YOUR EMAIL> and <REPLACE WITH YOUR KEY> with your data of the CloudFlare Account):
```
curl -s -X POST "https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules" \
  -H "X-Auth-Email: <REPLACE WITH YOUR EMAIL>"" \
  -H "X-Auth-Key: <REPLACE WITH YOUR KEY>"" \
  -H "Content-Type: application/json" \
  --data '{"mode":"block","configuration":{"target":"ip","value":"1.2.3.4"},"notes":"Fail2ban"}'
```
TEST UNBAN (Replace <REPLACE WITH YOUR EMAIL> and <REPLACE WITH YOUR KEY> with your data of the CloudFlare Account):
```
curl -s -X DELETE "https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules/$( \
              curl -s -X GET "https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules?mode=block&configuration_target=ip&configuration_value=1.2.3.4&page=1&per_page=1&match=all" \
             -H "X-Auth-Email: <REPLACE WITH YOUR EMAIL>" \
             -H "X-Auth-Key: <REPLACE WITH YOUR KEY>" \
             -H "Content-Type: application/json" | awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'id'\042/){print $(i+1);}}}' | tr -d '"' | sed -e 's/^[ \t]*//' | head -n 1)" \
             -H "X-Auth-Email: <REPLACE WITH YOUR EMAIL>"" \
             -H "X-Auth-Key: <REPLACE WITH YOUR KEY>" \
             -H "Content-Type: application/json"
```
If those tests works you should see in your CloudFlare account that with the first command "1.2.3.4" is added to the firewall, and with the second is removed.
If success go forward, otherwise check internet for more updated commands.

Create Custom CloudFlare Action for Integration with fail2ban
```
sudo mv /etc/fail2ban/action.d/cloudflare.conf /etc/fail2ban/action.d/cloudflare.conf.bak
sudo nano /etc/fail2ban/action.d/cloudflare.conf
```

COPY THAT:
```
#
# Author: Mike Andreasen from https://guides.wp-bullet.com
# Adapted Source: https://github.com/fail2ban/fail2ban/blob/master/config/action.d/cloudflare.conf
# Referenced from: https://www.normyee.net/blog/2012/02/02/adding-cloudflare-support-to-fail2ban by NORM YEE
#
# To get your Cloudflare API key: https://www.cloudflare.com/my-account
#

[Definition]

# Option:  actionstart
# Notes.:  command executed once at the start of Fail2Ban.
# Values:  CMD
#
actionstart =

# Option:  actionstop
# Notes.:  command executed once at the end of Fail2Ban
# Values:  CMD
#
actionstop =

# Option:  actioncheck
# Notes.:  command executed once before each actionban command
# Values:  CMD
#
actioncheck =

# Option:  actionban
# Notes.:  command executed when banning an IP. Take care that the
#          command is executed with Fail2Ban user rights.
# Tags:      IP address
#            number of failures
#            unix timestamp of the ban time
# Values:  CMD

actionban = curl -s -X POST "https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules" \
            -H "X-Auth-Email: <cfuser>" \
            -H "X-Auth-Key: <cftoken>" \
            -H "Content-Type: application/json" \
            --data '{"mode":"block","configuration":{"target":"ip","value":"<ip>"},"notes":"Fail2ban"}'

# Option:  actionunban
# Notes.:  command executed when unbanning an IP. Take care that the
#          command is executed with Fail2Ban user rights.
# Tags:      IP address
#            number of failures
#            unix timestamp of the ban time
# Values:  CMD
#

actionunban = curl -s -X DELETE "https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules/$( \
              curl -s -X GET "https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules?mode=block&configuration_target=ip&configuration_value=$
             -H "X-Auth-Email: <cfuser>" \
             -H "X-Auth-Key: <cftoken>" \
             -H "Content-Type: application/json" | awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'id'\042/){print $(i+1);}}}' | tr -d '"' | sed -e 's/^[ \t]*//$
             -H "X-Auth-Email: <cfuser>" \
             -H "X-Auth-Key: <cftoken>" \
             -H "Content-Type: application/json"

[Init]

# Option: cfuser
# Notes.: Replaces <cfuser> in actionban and actionunban with cfuser value below
# Values: Your CloudFlare user account

cfuser = <REPLACE WITH YOUR EMAIL>

# Option: cftoken
# Notes.: Replaces <cftoken> in actionban and actionunban with cftoken value below
# Values: Your CloudFlare API key 
cftoken = <REPLACE WITH YOUR KEY>
```
Just remember to replace with your email and API key.

CTRL+X to save and reboot Fail2Ban
```
sudo service fail2ban restart
```
Enjoy!

### Credits
This script is just re-adapted from the followings:
https://www.saas-secure.com/online-services/fail2ban-ip-sharing.html
https://serverfault.com/questions/625656/sharing-of-fail2ban-banned-ips
The CloudFlare integration:
https://guides.wp-bullet.com/integrate-fail2ban-cloudflare-api-v4-guide/
https://disq.us/url?url=https%3A%2F%2Fpastebin.com%2FHXuySRae%3Asc8R4UqCLAR076Jk-qBmZKBxyLo&cuid=4411650

