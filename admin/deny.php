<?php
require("../template/top.php");

require("../api/Services/Twilio.php");

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

echo '<pre>';

$q = $db->query("SELECT * FROM players WHERE id = '{$_GET['target_id']}' LIMIT 1"); // TARGET TARGET TARGET TARGET
if ($q->num_rows == 1) {
	$r = $q->fetch_array(MYSQL_ASSOC);
	$q2 = $db->query("SELECT * FROM assassinations WHERE target = '".$db->real_escape_string($r['id'])."' LIMIT 1");
	$r2 = $q2->fetch_array(MYSQL_ASSOC);
	$db->query("DELETE FROM assassinations WHERE target = '".$db->real_escape_string($r['id'])."' AND ver = 'assassin'") or error_log($db->error);
	$q5 = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($r2['assassin'])."' LIMIT 1");
	if ($q5->num_rows == 1) { // if not, huh!?
		$r5 = $q5->fetch_array();
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$r5['phone'],
			"An administrator has denied the assassination. If you wish to dispute this, please contact us on Twitter @assassins2k15."
		);
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$r['phone'],
		"An administrator denied your death, you are still in in the game"
	);
}

header("Location: http://www.assassins.in/tmp/assassinations.php");