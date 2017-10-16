<?php
require("../../template/top.php");
require("../../api/Services/Twilio.php");

header('Content-Type: text/html; charset=utf-8');

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

$sid = $_GET['last'];

$q = $db->query("SELECT phone, name FROM players");
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	$players[$r['phone']] = $r['name'];
}

$a = new stdClass();
$a->messages = array();
$a->time = time();

foreach ($client->account->sms_messages as $message) {
	if ($message->to != "+18173693691" && $message->from != "+18173693691") { continue; }
	if ($message->sid == $sid) { break; }
	$message->body = htmlspecialchars($message->body);
	if (preg_match("/^MM/i", $message->sid)) {
		$process = curl_init("https://api.twilio.com/2010-04-01/Accounts/AC220f04b1ab251758b0cb7279b1184cc9/Messages/" . $message->sid . "/Media.json");
		curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($process, CURLOPT_USERPWD, "AC220f04b1ab251758b0cb7279b1184cc9:32a60dc935f29520ef43c77f3b6e2a36");
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_HEADER, 0);  
		curl_setopt($process, CURLOPT_RETURNTRANSFER, true);  
		$return = json_decode(curl_exec($process));
		curl_close($process);
		foreach ($return->media_list as $key => $val) {
			$message->body .= "<img src='https://api.twilio.com".substr($val->uri, 0, -5)."'/>\n";
		}
	}
	$a->messages[] = array(
							"sid" => $message->sid,
							"from" => ids($message->from),
							"to" => ids($message->to),
							"date" => date("H:i:s D j, M, Y", strtotime($message->date_sent ? $message->date_sent : $message->date_created)),
							"body" => $message->body
						);
}

echo json_encode($a);

function ids($id) {
	global $players;
	if ($id == "+18173693691") {
		return "ASSASSINS";
	} else {
		$id = preg_replace("/^\+1/", "", $id);
		if (isset($players[$id])) {
			return trim($players[$id]);
		} else {
			return $id;
		}
	}
}