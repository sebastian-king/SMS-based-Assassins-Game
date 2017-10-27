<?php
require("../../template/top.php");

echo '<pre>';

$q = $db->query("SELECT * FROM players WHERE id = '{$_GET['target_id']}'");

if ($q->num_rows == 1) {
	$target = $q->fetch_array(MYSQLI_ASSOC);

	$q = $db->query("SELECT * FROM assassinations WHERE target = '".$db->real_escape_string($target['id'])."' LIMIT 1");

	if ($q->num_rows == 1) {
		$assassination = $q->fetch_array(MYSQLI_ASSOC);
		
		$db->query("UPDATE assassinations SET ver = 'both' WHERE target = '".$db->real_escape_string($target['id'])."' AND ver = 'assassin'") or die($db->error);
		
		$db->query("UPDATE players SET assassinated = '1', target = '' WHERE id = '".$db->real_escape_string($target['id'])."' LIMIT 1") or die($db->error);
		
		assassination($assassination['id']);
		
		$q = $db->query("SELECT * FROM players WHERE target = '".$db->real_escape_string($target['id'])."' LIMIT 1");
		if ($q->num_rows == 1) {
			$assassin = $q->fetch_array(MYSQLI_ASSOC);
			
			$db->query("UPDATE players SET target = '".$db->real_escape_string($target['target'])."' WHERE id = '".$db->real_escape_string($assassin['id'])."' LIMIT 1") or die($db->error);
			
			try {
				$sms = send_sms(PHONE_NUMBER, $assassin['phone'], 
				(($assassination['assassin'] != $assassin['id']) ?
				"Your target has fallen victim to a previous assassin" : "Assassination confirmed")
				. ", your new target is: " . uid2name($target['target']));
			} catch (Twilio\Exceptions\RestException $e) {
				echo var_dump("ERROR CAUGHT $e") . PHP_EOL;
			}
		}
		
		try {
			$sms = send_sms(PHONE_NUMBER, $target['phone'], 
			"You have been assassinated, an administrator has confirmed your death.");
		} catch (Twilio\Exceptions\RestException $e) {
			echo var_dump("ERROR CAUGHT $e") . PHP_EOL;
		}
	}
}

header("Location: ./");