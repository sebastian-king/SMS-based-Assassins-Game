<?php
require("../template/top.php");
require("Services/Twilio.php");
$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

function handle_blocked($exception) {
	global $number;
	if (get_class($exception) == "Services_Twilio_RestException") {
		groupme_sys_bot("BLOCKED, could not deliver your message from $number.");
		die();
	}
}

set_exception_handler("handle_blocked");

$a = json_decode(file_get_contents('php://input')); // because WTF why is $_POST empty!?!?!?!?
if ($a->sender_type == "user") {
	if (preg_match("/^to(2)?\((.+)\):?[ ]*(.+)$/i", $a->text, $m)) {
		$number = (($m[1] == 2) ? "8722218220" : "8173693691");
		if (is_numeric($m[2])) {
			$sms = $client->account->messages->sendMessage(
				$number, 
				$m[2],
				$m[3]
			);
		} else {
			$q = $db->query("SELECT * FROM players WHERE name = '".$db->real_escape_string($m[2])."' LIMIT 1");
			if ($q->num_rows == 1) {
				$r = $q->fetch_array();
				//groupme_user_bot("TO: '$m[1]'({$r['phone']}), TEXT: '$m[2]'");
				$sms = $client->account->messages->sendMessage(
					$number, 
					$r['phone'],
					$m[3]
				);
			} else {
				groupme_sys_bot("ERROR: Unknown user '$m[2]'");
			}
		}
	}
}