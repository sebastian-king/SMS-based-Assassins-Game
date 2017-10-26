<?php
require("../template/top.php");

echo "<body style='line-height: 18px'><pre>\nASSASSINS			  		TARGET						   STATUS						TIME\n";

$i = 1;

$q = $db->query("SELECT * FROM assassinations WHERE ver != 'both'");
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
		echo "<p " . (($i%2==0) ? " style='background-color:#e1e1e1'" : "") . ">".str_pad(uid2name($r['assassin']), 45)." => ".str_pad(uid2name($r['target']), 45)." => ".$r['ver']." (<a href='http://assassins.in/tmp/rip.php?target_id={$r['target']}'>kill?</a>)(<a href='http://assassins.in/tmp/deny.php?target_id={$r['target']}'>deny?</a>) => ".str_pad(date("H:i:s, d/m/Y", $r['timestamp']), 40, " ", STR_PAD_LEFT)."</p>";
		$i++;
	//}
}