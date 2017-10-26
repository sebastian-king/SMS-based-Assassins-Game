<?php
require("../template/top.php");
require("../api/Services/Twilio.php");

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

echo '<pre>';

$q = $db->query("SELECT * FROM players WHERE id = '{$_GET['target_id']}'"); // TARGET TARGET TARGET TARGET
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	$q2 = $db->query("SELECT * FROM assassinations WHERE target = '".$db->real_escape_string($r['id'])."' LIMIT 1");
	if ($q2->num_rows == 1) {
		$r2 = $q2->fetch_array();
		$db->query("UPDATE assassinations SET ver = 'both' WHERE target = '".$db->real_escape_string($r['id'])."' AND ver = 'assassin'") or error_log($db->error);
		$db->query("UPDATE players SET assassinated = '1', target = '' WHERE id = '".$db->real_escape_string($r['id'])."' LIMIT 1") or error_log($db->error);
		assassination($r2['id']);
		$q5 = $db->query("SELECT * FROM players WHERE target = '".$db->real_escape_string($r['id'])."' LIMIT 1");
		if ($q5->num_rows == 1) { // if not, huh!?
			$r5 = $q5->fetch_array();
			$db->query("UPDATE players SET target = '".$db->real_escape_string($r['target'])."' WHERE id = '".$db->real_escape_string($r5['id'])."' LIMIT 1") or error_log($db->error);
			$sms = $client->account->messages->sendMessage(
				"8173693691", 
				$r5['phone'],
				(($r2['assassin'] != $r5['id']) ? "Your target has fallen victim to a previous assassin" : "Assassination confirmed") . ", your new target is: ".uid2name($r['target'])
			);
		}
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$r['phone'],
			"You have been assassinated, since you did not confirm this it was done so by an administrator"
		);
	}
}

header("Location: http://www.assassins.in/tmp/assassinations.php");