#!/usr/bin/php
<?php
// This must be added to Cronjob to run each 1 minute
//Check the PHP path with the command "which php" in terminal

// sudo crontab -e
// */1 * * * * /usr/bin/php /home/domoticz/fail2ban-central/cron2ban.php

// phpconfig.php will have database configuration settings

require_once((dirname(__FILE__))."/config.php");

// file with only a line, containing the last id banned the path is present in the config file.
$lastban = "";
if (file_exists ($lastbanfile)) {
	$lastban = file_get_contents($lastbanfile);
	}
if ($lastban == "") { $lastban = 0; }

// select only hosts banned after last check
$sql = "SELECT * FROM `".$tablename."` WHERE `id` > ".$lastban;
//echo $sql;
$result = mysqli_query($link,$sql) or die('Query failed: ' . mysqli_error($link));
mysqli_close($link);

if (mysqli_num_rows($result) >= 1) {
	while ($row = mysqli_fetch_array($result)) {
        //
        $id = $row['id'];
        $ip = $row['ip'];

    exec("fail2ban-client set $jail banip $ip");


}

// $id contains the last banned host, add it to the config file
file_put_contents($lastbanfile, $id);
	}
?>