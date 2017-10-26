<?php
require("../template/top.php");
require("../api/Services/Twilio.php");

header('Content-Type: text/html; charset=utf-8');

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<style>
img { max-width: 400px; }
</style>
<?php

$q = $db->query("SELECT phone, name FROM players");
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	$players[$r['phone']] = $r['name'];
}

echo "<div id='time'></div><pre id='start'>";

$i = 1;
foreach ($client->account->sms_messages as $message) {
	if (!@$last) { $last = $message->sid; }
	if ($message->to != "+18173693691" && $message->from != "+18173693691") { continue; }
	$message->body = htmlspecialchars($message->body);
	if (preg_match("/^MM/i", $message->sid)) {
		$process = curl_init("https://api.twilio.com/2010-04-01/Accounts/AC220f04b1ab251758b0cb7279b1184cc9/Messages/" . $message->sid . "/Media.json");
		curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($process, CURLOPT_USERPWD, "AC220f04b1ab251758b0cb7279b1184cc9:32a60dc935f29520ef43c77f3b6e2a36");
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_HEADER, 0);  
		curl_setopt($process, CURLOPT_RETURNTRANSFER, true);  
		$return = json_decode(curl_exec($process));
		curl_close($process);
		foreach ($return->media_list as $key => $val) {
			$message->body .= "<img src='https://api.twilio.com".substr($val->uri, 0, -5)."'/>\n";
		}
	}
	echo "<b>$i-{$message->sid} => From: ".ids($message->from).", To: ".ids($message->to)."</b> @ ".date("H:i:s D j, M, Y", strtotime($message->date_sent))."\n<div class='well well-sm'>" . $message->body . "</div>";
	if (strtotime($message->date_sent) < strtotime("-7 days")) { break; } else { $i++; }
}

function ids($id) {
	global $players;
	if ($id == "+18173693691") {
		return "<span style='color:blue;'>ASSASSINS</span>";
	} else {
		$id = preg_replace("/^\+1/", "", $id);
		if (isset($players[$id])) {
			return "<span style='color:green;'>".trim($players[$id])."</span>";
		} else {
			return "<span style='color:red;'>".$id."</span>";
		}
	}
}

?>
<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script>
var last = "<?php echo $last; ?>";
var hidden, visibilityChange; 
if (typeof document.hidden !== "undefined") {
	hidden = "hidden";
	visibilityChange = "visibilitychange";
} else if (typeof document.mozHidden !== "undefined") {
	hidden = "mozHidden";
	visibilityChange = "mozvisibilitychange";
} else if (typeof document.msHidden !== "undefined") {
	hidden = "msHidden";
	visibilityChange = "msvisibilitychange";
} else if (typeof document.webkitHidden !== "undefined") {
	hidden = "webkitHidden";
	visibilityChange = "webkitvisibilitychange";
}
var unread = 0;
function makeColour(id) {
	if (id == "ASSASSINS") {
		return "blue";
	} else if (id.match(/^\d{10}$/)) {
		return "red";
	} else {
		return "green";
	} // not the safest function in the world
}
function updateSMSFunction() {
	$.get("/tmp/ajax/sms.php?last="+last, function(data) {
		data = JSON.parse(data);
		$("#time").html(data.time);
		if (data.messages.length > 0) {
			last = data.messages[0/*data.messages.length-1*/]["sid"];
			if (window_focus == false) {
				unread += data.messages.length;
				document.title = "("+unread+") Bye <?php echo ucfirst($_SERVER['PHP_AUTH_USER']); ?>";
			}
		}
		for (var i = data.messages.length-1; i >= 0; i--) {
			message = data.messages[i];
			html = '<b>0-'+message["sid"]+' =&gt; From: <span style="color:'+makeColour(message["from"])+';">'+message["from"]+'</span>, To: <span style="color:'+makeColour(message["to"])+';">'+message["to"]+'</span></b> @ '+message["date"]+"\n<div class='well well-sm'>"+message["body"]+"</div>";
			$("pre").prepend(html);
		}
	}).always(function() {
		setTimeout(updateSMSFunction, 1000);
	});
}
handleVisibilityChange();
document.title = "Hello <?php echo ucfirst($_SERVER['PHP_AUTH_USER']); ?>";
updateSMSFunction();
function handleVisibilityChange() {
	if (document[hidden]) {
		window_focus = false;
		document.title = "Bye <?php echo ucfirst($_SERVER['PHP_AUTH_USER']); ?>";
	} else {
		window_focus = true;
		unread = 0;
		document.title = "Hai <?php echo ucfirst($_SERVER['PHP_AUTH_USER']); ?>";
	}
}
if (typeof document.addEventListener === "undefined" || 
	typeof hidden === "undefined") {
	alert("This page requires a browser that supports the Page Visibility API.");
} else {
    document.addEventListener(visibilityChange, handleVisibilityChange, false);
}
</script>