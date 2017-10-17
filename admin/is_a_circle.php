<?php

require("../template/top.php");

echo '<pre>';
$q = $db->query("SELECT * FROM players WHERE target > ''") or die($db->error);

echo $q->num_rows . "\n";

$players = array();
//$shuffled_players = array();

while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
	$players[$r['id']] = array("id" => $r['id'], "name" => $r['name'], "target" => $r['target']);
}

// start set targets
/*
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
*/
// end set targets

while (count($players) >= 1) {
	$player = current(array_keys($players));
	echo "Starting with player: ".uid2name($player)."\n";
	echo "Ending with player: ".uid2name(pool($player))."\n";
	echo "Players remaining: ".count($players)."\n------\n";
}

function pool($id) {
	global $players, $shuffled_players;
	if (isset($players[$id])) {
		$target = $players[$id]['target'];
		//$shuffled_players[$id] = $players[$id];
		unset($players[$id]);
		return pool($target);
	} else {
		return $id;
	}
}

echo var_export($players, true);