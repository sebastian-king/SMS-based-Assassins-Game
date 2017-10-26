<?php
require("../template/top.php");
require("../api/Services/Twilio.php");

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

$q = $db->query("SELECT * FROM players WHERE id = '{$_GET['assassin_id']}' LIMIT 1"); // ASSASSIN ASSASSIN ASSASSIN
if ($q->num_rows == 1) {
	$r = $q->fetch_array(MYSQL_ASSOC);
	$from = $r['phone'];
	if (time() < $start) {
		$a = "The game has not yet started";
	} else if ($r['assassinated'] == 1) {
		$q = $db->query("SELECT * FROM assassinations WHERE target = '{$r['id']}' LIMIT 1");
		$r2 = $q->fetch_array(MYSQL_ASSOC);
		$a = "You were assassinated by ".uid2name($r2['assassin'])." @ ".ago($r2['timestamp']);
	} else if ($r['suicided'] == 1) {
		$a = "You commited sudoku";
	} else {
		$q3 = $db->query("SELECT * FROM assassinations WHERE target = '".$db->real_escape_string($r['target'])."' AND assassin = '".$db->real_escape_string($r['id'])."' LIMIT 1");
		if ($q3->num_rows == 1) {
			$a = "The target has not yet confirmed or denied the assassination";
		} else {
			$a = "Nice work! Once your target confirms they were assassinated you will be assigned a new target";
			$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($r['target'])."' LIMIT 1");
			$db->query("INSERT INTO assassinations (`assassin`,`target`,`timestamp`,`ver`) VALUES ('".$db->real_escape_string($r['id'])."','".$db->real_escape_string($r['target'])."','".time()."','assassin');") or error_log("ERROR!".$db->error);
			if ($q->num_rows == 1) {
			$r = $q->fetch_array(MYSQL_ASSOC);
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					$r['phone'],
					"Your assassin says they have eliminated you. Reply with rip <pin> to confirm this or norip <pin> if they haven't assassinated you. E.G: rip 1234 or norip 1234."
				);
			} // else o.O!!
		}
	}
} else {
	$a = "You are not registered";
}
$sms = $client->account->messages->sendMessage(
	"8173693691", 
	$from,
	$a
);