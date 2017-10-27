<?php
require("../../template/top.php");

$q = $db->query("SELECT * FROM assassinations WHERE ver != 'both'");
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
			<th>Assassin</th>
			<th>Target</th>
			<th>Confirmation Status</th>
			<th>Timestamp</th> 
			<th>Commands</th>
		  </tr>
		  <?php
			while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
			?>
		  <tr id="<?php echo 'entry-', $r['id']; ?>">
			<td><?php echo uid2name($r['assassin']); ?></td>
			<td><?php echo uid2name($r['target']); ?></td>
			<td><?php echo $r['ver']; ?></a></td>
			<td><?php echo date('l jS \of F Y h:i:s A'); ?></td>
			<td><a href="confirm?target_id=<?php echo $r['target']; ?>">Confirm</a> / <a href="deny?target_id=<?php echo $r['target']; ?>">Deny</a></td>
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