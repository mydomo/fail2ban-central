<?php
//REQUIREMENTS:
//sudo apt-get install php7.0 php-mysql

require_once((dirname(__FILE__))."/phpconfig.php");

$name = $_SERVER["argv"][1];
$protocol = $_SERVER["argv"][2];
$port = $_SERVER["argv"][3];
if (!preg_match('/^\d{1,5}$/', $port))
    $port = getservbyname($_SERVER["argv"][3], $protocol);
$ip = $_SERVER["argv"][4];

$hostname = gethostname();


$query = "INSERT INTO `".$tablename."`(`hostname`, `created`, `name`, `protocol`, `port`, `ip`) VALUES ([".addslashes($hostname)."],[NOW()],[".addslashes($name)."],[".addslashes($protocol)."],[".addslashes($port)."],[".addslashes($ip)."])";

if (mysqli_query($link, $query)) {
    echo "Ip to BAN added to DATABASE";
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($link);
}
mysqli_close($link);
exit;
?>
