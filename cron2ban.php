#!/usr/bin/php
<?php
// phpconfig.php will have database configuration settings
require_once((dirname(__FILE__))."/phpconfig.php");

// file with only a line, containing the last id banned
$lastbanfile="/etc/fail2ban/lastban";

$lastban = file_get_contents($lastbanfile);
if ($lastban == "") { $lastban = 0; }
// select only hosts banned after last check
$sql = "SELECT * FROM `".$tablename."` WHERE `id` > `".$lastban."`";
echo $sql;
$result = mysqli_query($link,$sql) or die('Query failed: ' . mysqli_error($link));
mysqli_close($link);

while ($row = mysqli_fetch_array($result)) {
        //
        $id = $row['id'];
        $ip = $row['ip'];

    exec("fail2ban-client set $jail banip $ip");


}

// $id contains the last banned host, add it to the config file
file_put_contents($lastbanfile, $id);
?>