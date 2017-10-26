<?php
require("../template/top.php");

if (isset($_GET['toggle'])) {
	$id = $_GET['toggle'];
	$q = $db->query("SELECT eligible FROM players WHERE id = '".$db->real_escape_string($id)."'") or die($db->error);
	$r = $q->fetch_array(MYSQLI_ASSOC);
	if ($r['eligible'] == "unsure") {
		$s = "yes";
	} else if ($r['eligible'] == "yes") {
		$s = "no";
	} else {
		$s = "unsure";
	}
	$db->query("UPDATE players SET eligible = '$s' WHERE id = '".$db->real_escape_string($id)."' LIMIT 1") or die($db->error);
	die(header("Location: {$_SERVER['HTTP_REFERER']}#pos=" . $_GET['pos']));
}
?><html>
	<head>
		<style>
			html {
				height: 100%;
				overflow:auto;
			}
			body {
				height: 100%;
				margin: 0;
				font-family: Lato, "Helvetica Neue", Helvetica, sans-serif;
			}
			table, th, td {
				border: 1px solid black;
				border-collapse: collapse;
			}
			tr:nth-child(odd) {
				background-color: #B6D1EB;
			}
			p:first-of-type {
				text-align: center;
			}
			#overlay {
				background: #ffffff;
				color: #666666;
				position: fixed;
				height: 100%;
				width: 100%;
				z-index: 5000;
				top: 0;
				left: 0;
				float: left;
				text-align: center;
				padding-top: 25%;
			}
		</style>
	</head>
	<body>
		<p><a href="/admin/">Admin Home</a></p>
		<table style="width:100%">
		  <tr>
			<th>ID #</th>
			<th>Verified</th>
			<th>Eligible</th>
			<th>Name</th> 
			<th>Assinations<br>(# unconfirmed)</th> 
			<th>PIN</th> 
			<th>Alive<br>(killed by)</th>
			<th>Target</th>
			<th>E-mail</th> 
			<th>Phone number</th>
		  </tr>
			<?php
			$i = 1;

			$q = $db->query("SELECT * FROM players ORDER BY name ASC");
			while ($player = $q->fetch_array(MYSQLI_ASSOC)) {
				preg_match( '/^(?:\+1)(\d{3})(\d{3})(\d{4})$/', $r['phone'],  $matches );
				$result = '('.$matches[1] . ')-' .$matches[2] . '-' . $matches[3];

				$assassinations = $db->query("SELECT count(*) FROM assassinations WHERE assassin = '".$db->real_escape_string($player['id'])."'");
				$assassinations = current($assassinations->fetch_array(MYSQLI_NUM));

				$unconfirmed_assassinations = $db->query("SELECT count(*) FROM assassinations WHERE assassin = '".$db->real_escape_string($r['id'])."' AND ver != 'both'");
				$unconfirmed_assassinations = current($unconfirmed_assassinations->fetch_array(MYSQLI_NUM));

				$target = $db->query("SELECT * FROM players WHERE id = '" . $db->real_escape_string($player['target'])."' LIMIT 1");
				$target = $target->fetch_array(MYSQLI_ASSOC);

				if (empty($player['target'])) { // player is dead if they have no target
					$assassin = $db->query("SELECT * FROM assassinations WHERE target = '" . $db->real_escape_string($player['id'])."' LIMIT 1");
					$assassin = $assassin->fetch_array(MYSQLI_ASSOC);
					$alive = "dead";
					$killed_by = array($assassin['assassin'], uid2name($assassin['assassin']));
				} else {
					$alive = "alive";
					$killed_by = false;
				}
				$i++;
				?>
			  <tr id="<?php echo 'player-', $player['id']; ?>">
				<td><a href="#player-<?php echo $player['id']; ?>"><?php echo $player['id']; ?></a></td>
				<td><?php echo $player['validated']; ?></td>
				<td><a href="javascript:toggle('<?php echo $player['id']; ?>');"><?php echo $player['eligible']; ?></a></td>
				<td><a href="/u/<?php echo $player['uid']; ?>"><?php echo $player['name']; ?></a></td>
				<td><?php echo $assassinations; ?> (<?php echo $unconfirmed_assassinations; ?>)</td>
				<td><?php echo $player['pin']; ?></td>
				<td><?php echo $alive; if ($killed_by) { ?> <a href="#player-<?php echo $killed_by[0]; ?>">(<?php echo $killed_by[1]; ?>)</a><?php } ?></td>
				<td><?php if ($target['name']) { echo $target['name']; } else { echo '<em>none</em>'; } ?> (<?php if ($target['id']) { echo $target['id']; } else { echo "deceased"; } ?>)</td>
				<td><?php echo $player['email']; ?></td>
				<td><?php echo $player['phone']; ?></td>
			  </tr>
				<?php
			}
			?>
		</table>
	</body>
<div id="overlay">
    <img src="/img/loader.gif" alt="Loading" /><br/>
	<em>Loading</em>
</div>
	<script src="/js/jquery.min.js"></script>
	<script>
		$(document).ready(function() {
			var regex = /^#pos=(\d+)$/;
			if (matches = window.location.hash.match(regex)) {
				$('body').scrollTop(matches[1]);
				console.log(matches[1]);
				window.location.hash = "!";
				
			}
			$("#overlay").hide();
		})
		function show_assassin(assassin) {
			alert(assassin);
		}
		function toggle(id) {
			window.location = "?toggle=" + id + "&pos=" + $('body').scrollTop();
		}
	</script>
</html>