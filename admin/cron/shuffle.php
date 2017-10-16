<?php
if (php_sapi_name() !== 'cli') {
	die("This is a CLI only script");
}
set_time_limit(0);
require("/var/www/assassins/template/top.php");
require("/var/www/assassins/api/Services/Twilio.php");
$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

//
if (preg_match("/sandbox/i", @$argv[1])) {
	$SANDBOX = true;
} else {
	$SANDBOX = false;
}
//

echo "<pre>\n".exec("date")."\n";
$q = $db->query("SELECT * FROM players WHERE target > ''") or die($db->error);
echo $q->num_rows . "\n";

$players = array();
$shuffled_players = array();

while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	$players[$r['id']] = array("id" => $r['id'], "name" => $r['name'], "target" => $r['target']);
}

// start set targets
$keys = array_keys($players);
shuffle($keys);
foreach($keys as $key) {
	$tmp[$key] = $players[$key];
}
$players = $tmp;
$tmp = -1;
foreach ($players as $key => $val) {
	$players[$key]['target'] = $tmp;
	$tmp = $key;
}
reset($players);
$players[key($players)]['target'] = $tmp;
// end set targets

while (count($players) >= 1) {
	$player = current(array_keys($players));
	echo "Starting with player: ".$player."\n";
	echo "Ending with player: ".pool($player)."\n";
	echo "Players remaining: ".count($players)."\n------\n";
}

function pool($id) {
	global $players, $shuffled_players;
	if (isset($players[$id])) {
		$target = $players[$id]['target'];
		$shuffled_players[$id] = $players[$id];
		unset($players[$id]);
		return pool($target);
	} else {
		return $id;
	}
}

echo var_export($players, true),"\n";

if ($SANDBOX) {
	die("\nSANDBOX\n");
}

echo "\nKILL NOW IF POOL LARGER THAN 1\n";

$i = 0;
while ($i < 60) {
	echo "$i		\r";
	$i++;
	sleep(1);
}

foreach ($shuffled_players as $key => $val) {
	//echo "($key) {$val['name']} => {$shuffled_players[$shuffled_players[$key]['target']]['name']} ({$shuffled_players[$key]['target']})\n";
	$db->query("UPDATE players SET target = '".$db->real_escape_string($shuffled_players[$key]['target'])."' WHERE id = '$key' LIMIT 1") or die($db->error);
}

// shuffle complete & writen to db
// now start sending

$q = $db->query("SELECT * FROM players WHERE target > '' ORDER BY id ASC");
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
		echo "(".str_pad($r['validated'], 5).") " . str_pad($r['name'], 25, " ") . " => 0 (".$r['pin'].") => ".str_pad(uid2name($r['target']), 25, " ");
		try {
			$sms = $client->account->messages->sendMessage(
				"8173693691", 
				$r['phone'],
				uid2name($r['target'])
			);
			echo var_export($sms->status, true)."\n";
		} catch (Exception $e) {
			echo var_export("ERROR CAUGHT", true)."\n";
		}
		//usleep(100000);
}
echo "DONE";