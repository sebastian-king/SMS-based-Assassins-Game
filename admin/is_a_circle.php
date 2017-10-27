<?php

require("../template/top.php");

if (php_sapi_name() !== 'cli') {
	echo '<a href=\'/admin/\'>Admin Home</a><pre>';
}

echo date('l jS \of F Y h:i:s A'), PHP_EOL;
$q = $db->query("SELECT * FROM players WHERE target > ''") or die($db->error);
echo "Total number of players still alive: ", $q->num_rows, PHP_EOL;

$players = array();

while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
	$players[$r['id']] = array("id" => $r['id'], "name" => $r['name'], "target" => $r['target']);
}

while (count($players) >= 1) {
	$player = current(array_keys($players));
	echo "Starting with player: ", uid2name($player), PHP_EOL;
	echo "Ending with player: ", uid2name(pool($player)), PHP_EOL;
	echo "Players remaining: ", count($players), PHP_EOL, "------", PHP_EOL;
}

function pool($id) {
	global $players, $shuffled_players;
	if (isset($players[$id])) {
		$target = $players[$id]['target'];
		unset($players[$id]);
		return pool($target);
	} else {
		return $id;
	}
}

if (count($players)) {
	echo "There is a cyclical target reference, and more than two players.", PHP_EOL;
	echo "This needs to be addressed because it likely means that someone doesn't have an assassin, and instead two people have each other as targets, even though there is more than two people left.", PHP_EOL;
} else {
	echo "It looks like everything is properly assigned, each player has a unique target and assassin.", PHP_EOL;
}
