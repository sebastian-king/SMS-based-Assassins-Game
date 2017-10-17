#!/usr/bin/php
<?php
require("/var/www/assassins/template/top.php");
require_once("/var/www/t/PlancakeEmailParser.php");

$sender = $argv[1];
$size = $argv[2];
$recipient = $argv[3];

$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
        $line = fread($fd, 1024);
        $email .= $line;
}
$emailParser = new PlancakeEmailParser($email);
$body = $emailParser->getBody();
$_from = $emailParser->rawFields['from'];

preg_match("/Content-Transfer-Encoding: base64\n\n(.*)\n--/si", $body, $m);
$b = base64_decode(str_replace("\n", "", $m[1]));
if ($b) {
	preg_match("/<PRE>\n(.*?)\n\n<\/PRE>/si", $b, $m); //sprint
	$body = $m[1];
} else if(preg_match("/^\n(.*?)[ ]*\n\n[ ]*-----Original Message-----/si", $body, $m)) { //at&t
	$body = $m[1];
} else { // metropcs && verizon
	file_put_contents("/var/www/assassins/api/mail2php.log", "b was null\n", FILE_APPEND);
	//die();
}
$body = trim($body);
	file_put_contents("/var/www/assassins/api/mail2php.log", "BODY: '$body'" . PHP_EOL, FILE_APPEND);

$sreirrac = array(
	"pm.sprint.com" => "sprint",
	"messaging.sprintpcs.com" => "sprint", 
	"txt.att.net" => "att", 
	"tmomail.net" => "tmobile/metropcs", 
	"tmomail.net"  => "metropcs/tmobile", 
	"vtext.com" => "verizon"
);

$from = str_replace("+1", "", current(explode("@", $_from)));
$carrier = @$sreirrac[next(explode("@", $_from))];
file_put_contents("/var/www/assassins/api/mail2php.log", "From: $from, Carrier: $carrier, (".next(explode("@", $_from)).")[".$_from."]" . PHP_EOL, FILE_APPEND);

if (preg_match("/^subscribe$/i", $body)) {
	if (isset($carrier)) {
		$q = $db->query("SELECT * FROM subscriptions WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
		if ($q->num_rows == 0) {
			$db->query("INSERT INTO subscriptions (`phone`,`carrier`) VALUES('".$db->real_escape_string($from)."', '$carrier')");
			smsUpdate("You have subscribed to instant updates. To unsubscribe reply here with unsubscribe.", $from, $carrier);
		} else {
			smsUpdate("You are already subscribed.", $from, $carrier);
		}
	} else {
		smtp2sms("Your carrier is not supported, sorry.", $from, "Error");
	}
} else if (preg_match("/^unsubscribe$/i", $body)) {
	$q = $db->query("SELECT * FROM subscriptions WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$db->query("DELETE FROM subscriptions WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
		smsUpdate("You have been unsubscribed.", $from, $carrier);
	} else {
		smsUpdate("You are not subscribed.", $from, $carrier);
	}
} else {
	smsUpdate("We did not understand your message. We are delivering messages through $carrier.", $from, $carrier);
}

?>
