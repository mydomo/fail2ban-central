<?php
// phpconfig.php will have database configuration settings
require_once((dirname(__FILE__))."/phpconfig.php");

// file with only a line, containing the last id banned
$lastbanfile="/etc/fail2ban/lastban";

$lastban = file_get_contents($lastbanfile);

// select only hosts banned after last check
$sql = "select id, ip from ".$tablename." where id > $lastban";
$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
mysql_close($link);

while ($row = mysql_fetch_array($result)) {
        //
        $id = $row['id'];
        $ip = $row['ip'];

    exec("fail2ban-client set $jail banip $ip");


}

// $id contains the last banned host, add it to the config file
file_put_contents($lastbanfile, $id);
?>
