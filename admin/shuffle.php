<?php

if (php_sapi_name() !== 'cli') {
        die("This is a CLI only script, please run it on the server's terminal."); // the script cannot be cancelled via the web if there is an error
}

set_time_limit(300); // WARNING: This script can take a long time to run, it will time out after 5 minutes simply because this can stop the server crashing if somehow the recursion ends in an infinite loop

require("../template/top.php");

//
if (preg_match("/sandbox/i", @$argv[1])) {
	$SANDBOX = true;
} else {
	$SANDBOX = false;
}
//

echo date('l jS \of F Y h:i:s A'), PHP_EOL;
$q = $db->query("SELECT * FROM players WHERE target > ''") or die($db->error);
echo "Total number of players to be shuffled: ", $q->num_rows, PHP_EOL;

$players = array();
$shuffled_players = array();

while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
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
	echo "Starting with player: ", $player, PHP_EOL;
	echo "Ending with player: ", pool($player), PHP_EOL;
	echo "Players remaining: ", count($players), PHP_EOL, "------", PHP_EOL;
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

echo "No. of players that haven't been shuffled in the pool: ", count($players) , PHP_EOL;

if ($SANDBOX) {
	die(PHP_EOL . "The sandbox parameter was specified, therefore we are exiting now without writing changes to the database and without texting anyone." . PHP_EOL);
}

echo PHP_EOL, "If the number above is larger than 0, kill this program NOW by pressing Ctrl+C.", PHP_EOL;

$i = 0;
while ($i < 10) {
	echo "Seconds before the shuffle goes live: $i	\r";
	$i++;
	sleep(1);
}

foreach ($shuffled_players as $key => $val) {
	echo "(ID:$key) NAME:{$val['name']} => TARGET:{$shuffled_players[$shuffled_players[$key]['target']]['name']} TARGET_ID:({$shuffled_players[$key]['target']})", PHP_EOL;
	$db->query("UPDATE players SET target = '".$db->real_escape_string($shuffled_players[$key]['target'])."' WHERE id = '$key' LIMIT 1") or die($db->error);
}

// shuffle complete & writen to db
// now start sending texts with people's new targets

$q = $db->query("SELECT * FROM players WHERE target > '' ORDER BY id ASC");
while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
		echo "VALIDATED:(", str_pad($r['validated'], 5), ") NAME:", str_pad($r['name'], 25, " "), " PIN:(", $r['pin'], ") TARGET:", str_pad(uid2name($r['target']), 25, " "), PHP_EOL;
		try {
				$sms = $twilio_client->messages->create(
						$r['phone'],
						array(
								'from' => PHONE_NUMBER,
								'body' => uid2name($r['target'])
						)
				);
		} catch (Twilio\Exceptions\RestException $e) {
			echo var_dump("ERROR CAUGHT $e") . PHP_EOL;
		}
		//usleep(100000); // this might be helpful if Twilio enforces a rate limit
}
echo "DONE";
