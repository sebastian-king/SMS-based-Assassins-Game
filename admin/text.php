<?php

require("../template/top.php");
require("../api/Services/Twilio.php");

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

if (php_sapi_name() !== 'cli') {
	die("This is a CLI only script");
}
if (!@$argv[1]) {
	die("You haven't specified a message" . PHP_EOL);
} else if (!@$argv[2]) {
	die("You haven't specified a phone number" . PHP_EOL);
} else {
	$msg = $argv[1];
	$num = $argv[2];
}

//$players = array(14,83,364,443);

//foreach ($players as $key => $val) {
	//$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($val)."' LIMIT 1");
	//$r = $q->fetch_array(MYSQL_ASSOC);
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$num,
		$msg
	);
	echo "$sms => $sms->status" . PHP_EOL;
//}