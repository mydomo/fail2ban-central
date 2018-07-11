#!/usr/bin/php
<?php
////////////////////////////////
// DEFAULT CONFIGURATION FILE //
// -------------------------- //
//     CHANGE PARAMETERS      //
//      AND SAVE IT AS:       //
//        "config.php"        //
////////////////////////////////

// jail to be used
$jail = "fail2ban-central";
// you can use one of your jail or create a specific one

// file to keep the last ban
$lastbanfile="/etc/fail2ban/lastban";

// database configuration, use only one central mysql server
$dbserver="localhost";
$dbuser="user";
$dbpass="pass";
$dbname="database_1";
$tablename="fail2ban";

// connect to database
$link = mysqli_connect($dbserver, $dbuser, $dbpass, $dbname);
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
?>

