<?php
require("../template/top.php");
$q = $db->query("
	SELECT a1.assassin, a1.target, a1.assassin as id,
		(SELECT COUNT(*) FROM assassinations AS a3
				WHERE a3.assassin = a1.assassin AND ver = 'both') AS kills,
		MAX(timestamp) AS killtime,
		EXISTS (SELECT * FROM assassinations AS a2
				WHERE a2.target = a1.assassin AND ver = 'both') AS killed
	FROM assassinations AS a1
	WHERE ver = 'both' AND dq = 0
	GROUP BY assassin
	UNION
	SELECT (SELECT '') as assassin, p.target, p.id,
		EXISTS (SELECT * FROM assassinations AS a
				WHERE a.target = p.id AND ver = 'both') AS killed,
		(SELECT COUNT(*) FROM assassinations AS a
				WHERE a.assassin = p.id AND ver = 'both') AS kills,
		(SELECT 0) as killtime
	FROM players as p
	WHERE target > ''
	GROUP BY p.id
	HAVING killed = 0 AND kills = 0
	UNION
	SELECT a1.assassin, a1.target, a1.target as id,
		(SELECT COUNT(*) FROM assassinations AS a3
				WHERE a3.assassin = a1.target AND ver = 'both') AS kills,
		MAX(timestamp) AS killtime,
		EXISTS (SELECT * FROM assassinations AS a2
				WHERE a2.target = a1.target AND ver = 'both') AS killed
	FROM assassinations AS a1
	WHERE ver = 'both'
	GROUP BY target
	HAVING kills = 0 AND killed = 1
	ORDER BY kills DESC, killed ASC, killtime DESC
	") or die($db->error);
	
	echo "<pre>\nRANK				NAME				KILLS		DEAD		TIME OF LAST KILL/DEATH\n";
$i = 1;
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	$Q = $db->query("SELECT uid FROM players WHERE id = '".$db->real_escape_string($r['id'])."' LIMIT 1");
	$u = $Q->fetch_array(MYSQL_NUM);
	echo "$i				<a href='/u/{$u[0]}' style='text-decoration:none;'>".str_pad(trim(uid2name($r['id']))."</a>", 29)."	".$r['kills']."		".(($r['killed'] == 1) ? "YES" : "NO")."		".(($r['killtime']) ? ago($r['killtime']) : "No deaths/kills")."\n";
	$i++;
}

// assassin, target, id, kills, killed, killtime