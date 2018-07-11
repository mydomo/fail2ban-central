#!/usr/bin/php
<?php
//REQUIREMENTS:
//sudo apt-get install php7.0 php-mysql

//MANUAL COMMANDS:
//
//UNBAN: sudo fail2ban-client set domoticz unbanip Ban 5.90.201.166


/*
Open the "jail.local" file and find the "banaction" used by the rule


It's necessary to add the following line to the "banaction" rule used.
php /home/domoticz/fail2ban-central/fail2ban.php <name> <protocol> <port> <ip>
EXAMPLE: if you use "iptables-multiport.conf" replace:
---------------------------------------------------------
actionban = <iptables> -I f2b-<name> 1 -s <ip> -j <blocktype>
---------------------------------------------------------
with:
---------------------------------------------------------
actionban = <iptables> -I f2b-<name> 1 -s <ip> -j <blocktype>
            php /home/domoticz/fail2ban-central/fail2ban.php <name> <protocol> <port> <ip>
---------------------------------------------------------
*/


require_once((dirname(__FILE__))."/phpconfig.php");

$name = $_SERVER["argv"][1];
$protocol = $_SERVER["argv"][2];
$port = $_SERVER["argv"][3];
if (!preg_match('/^\d{1,5}$/', $port))
    $port = getservbyname($_SERVER["argv"][3], $protocol);
$ip = $_SERVER["argv"][4];

$hostname = gethostname();


$query = "INSERT INTO `".$tablename."`(`hostname`, `created`, `name`, `protocol`, `port`, `ip`) VALUES ('".addslashes($hostname)."',NOW(),'".addslashes($name)."','".addslashes($protocol)."','".addslashes($port)."','".addslashes($ip)."')";

if (mysqli_query($link, $query)) {
    echo "Ip to BAN added to DATABASE";
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($link);
}
mysqli_close($link);
exit;
?>
