<?php
$to = $_GET['to'];
$body = $_GET['body'];

require "Services/Twilio.php";
 
$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

$sms = $client->account->messages->sendMessage(
	"8173693691", 
	$to,
	$body
);