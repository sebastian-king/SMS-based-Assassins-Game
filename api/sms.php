<?php
require("../template/top.php");
require("Services/Twilio.php");

$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

function generate_random_string($name_length = 6) {
	$alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	return substr(str_shuffle($alpha_numeric), 0, $name_length);
}

function handle_blocked($exception) {
	global $client, $from, $db;
	if (get_class($exception) == "Services_Twilio_RestException") {
		error_log("{$_REQUEST['From']} has blocked 8173693691 at service level.");
		email("spprt.sbx.net@gmail.com", "Service level block", "{$_REQUEST['From']} has blocked 8173693691 at service level.\n{$exception->getMessage()}");
		$sms = $client->account->messages->sendMessage(
			"8722218220", 
			$from,
			"You have blocked 8173693691, you will not be able to play in Assassins without unblocking it. To unblock it, text START to 8173693691."
		);
		$q = $db->query("SELECT name FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
		if ($q->num_rows == 1) {
			$r = $q->fetch_array(MYSQL_NUM);
			$name = $r[0];
		} else {
			$name = false;
		}
		preg_match('/^(\d{3})(\d{3})(\d{4})$/', $from,  $matches);
		$from = '('.$matches[1] . ')-' .$matches[2] . '-' . $matches[3];
		groupme_sys_bot("BLOCKED by $from ".(($name) ? "($name) " : "")." -> 8173693691 (unblock by texting START)");
		die();
	}
}

set_exception_handler("handle_blocked");

$msg = trim($_REQUEST['Body']);
if ($_REQUEST["FromCountry"] == "US") {
	$from = str_replace("+1", "", $_REQUEST['From']);
} else {
	die();
}

if ($_REQUEST['NumMedia'] > 0) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		for ($i = 0; $i < $_REQUEST['NumMedia']; $i++) {
			$picture_url = $_REQUEST['MediaUrl'.$i];
			if (!is_dir($base . "/i/" . $r['id'])) {
				mkdir($base . "/i/" . $r['id']);
			}
			$tmp = tmpfile();
			fwrite($tmp, file_get_contents($picture_url));
			fseek($tmp, 0);
			$tmpnam = stream_get_meta_data($tmp);
			$tmpnam = $tmpnam['uri'];
			$ext = image_type_to_extension(exif_imagetype($tmpnam));
			$mime = image_type_to_mime_type(exif_imagetype($tmpnam));
			$uri = "/i/" . $r['id'] . "/" . uniqid() . "$ext";
			file_put_contents($base . $uri, file_get_contents($tmpnam)); // doesn't need to be cryptographically secure, just unique
			$db->query("INSERT INTO pictures (`twilio_url`,`assassins_url`,`uid`,`timestamp`,`mime`) VALUES ('".$db->real_escape_string($_REQUEST['MediaUrl'.$i])."','$uri','{$r['id']}','".time()."','$mime')");
			fclose($tmp);
		}
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"Thank you for the picture, this will be used on your next assassination twitter announce."
		);
	} else {
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"You are not registered for assassins, but thank you for your picture anyway. :)"
		);
	}
} else if (preg_match("/^#(.+)/i", $msg, $m)) { // if just '#' then wtf do you want, to substr or to $m is the question
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		groupme_user_bot("(".$r['name']."): ".$m[1]);
	} else {
		groupme_user_bot("($from): ".$m[1]);
	}
	// msg received and understood
} else if (preg_match("/^start$/i", $msg)) {
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		"Thank you for unblocking Assassins, you may now continue to be in the game."
	);
	$q = $db->query("SELECT name FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_NUM);
		$name = $r[0];
	} else {
		$name = false;
	}
	
	preg_match('/^(\d{3})(\d{3})(\d{4})$/', $from,  $matches);
	$from = '('.$matches[1] . ')-' .$matches[2] . '-' . $matches[3];
	groupme_bot("UNBLOCKED by $from ".(($name) ? "($name) " : "")."-> 8173693691");
} else if (preg_match("/^target$/i", $msg)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if (time() < $start) {
			$a = "The game has not yet started";
		} else if ($r['assassinated'] == 1) {
			$q = $db->query("SELECT * FROM assassinations WHERE target = '{$r['id']}' LIMIT 1");
			$r2 = $q->fetch_array(MYSQL_ASSOC);
			$a = "You were assassinated by ".uid2name($r2['assassin'])." @ ".ago($r2['timestamp']);
		} else if ($r['suicided'] == 1) {
			$a = "You commited sudoku";
		} else {
			$a = uid2name($r['target']);
		}
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^eliminated$/i", $msg)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if (time() < $start) {
			$a = "The game has not yet started";
		} else if ($r['assassinated'] == 1) {
			$q = $db->query("SELECT * FROM assassinations WHERE target = '{$r['id']}' LIMIT 1");
			$r2 = $q->fetch_array(MYSQL_ASSOC);
			$a = "You were assassinated by ".uid2name($r2['assassin'])." @ ".ago($r2['timestamp']);
		} else if ($r['suicided'] == 1) {
			$a = "You commited sudoku";
		} else {
			$q3 = $db->query("SELECT * FROM assassinations WHERE target = '".$db->real_escape_string($r['target'])."' AND assassin = '".$db->real_escape_string($r['id'])."' LIMIT 1");
			if ($q3->num_rows == 1) {
				$a = "The target has not yet confirmed or denied the assassination";
			} else {
				$a = "Nice work! Once your target confirms they were assassinated you will be assigned a new target";
				$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($r['target'])."' LIMIT 1");
				$db->query("INSERT INTO assassinations (`assassin`,`target`,`timestamp`,`ver`) VALUES ('".$db->real_escape_string($r['id'])."','".$db->real_escape_string($r['target'])."','".time()."','assassin');") or error_log("ERROR!".$db->error);
				if ($q->num_rows == 1) {
				$r = $q->fetch_array(MYSQL_ASSOC);
					$sms = $client->account->messages->sendMessage(
						"8173693691", 
						$r['phone'],
						"Your assassin says they have eliminated you. Reply with rip <pin> to confirm this or norip if they haven't assassinated you. E.G: rip 1234 or norip."
					);
				} // else o.O!!
			}
		}
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^status$/i", $msg, $m)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if (time() < $start) {
			$a = "The game has not yet started";
		} else if ($r['assassinated'] == 1) {
			$q = $db->query("SELECT * FROM assassinations WHERE target = '{$r['id']}' LIMIT 1");
			$r2 = $q->fetch_array(MYSQL_ASSOC);
			$a = "You were assassinated by ".uid2name($r2['assassin'])." @ ".ago($r2['timestamp']);
		} else if ($r['suicided'] == 1) {
			$a = "You commited sudoku";
		} else {
			$a = "You are still alive and in the game";
		}
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^([a-z0-9]{6})$/i", $msg)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' AND verp = '".$db->real_escape_string($msg)."' LIMIT 1") or die($db->error);
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if ($r['validated'] == 'email' || $r['validated'] == 'both') {
			$validated = 'both';
		} else {
			$validated = 'phone';
		}
		$db->query("UPDATE players SET validated = '$validated' WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1") or die($db->error);
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"Thank you! Your phone number has been confirmed." . (($r['validated'] == "both" || $r['validated'] == "email") ? "" : " Please don't forget to verify your e-mail address.")
		);
	} else {
		$code = strtolower(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 6));
		$db->query("UPDATE players SET verp = '$code' WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1") or die($db->error);
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"That code was not correct. Please try the code: $code"
		);
	}
} else if (preg_match("/^(?:no)?rip[ ]*(\d+)?/i", $msg, $m)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if (time() < $start) {
			$a = "The game has not yet started";
		} else if ($r['assassinated'] == 1) {
			$q = $db->query("SELECT * FROM assassinations WHERE target = '{$r['id']}' LIMIT 1");
			$r2 = $q->fetch_array(MYSQL_ASSOC);
			$a = "You were assassinated by ".uid2name($r2['assassin'])." @ ".ago($r2['timestamp']);
		} else if ($r['suicided'] == 1) {
			$a = "You commited sudoku";
		} else {
			if (!isset($m[1]) && preg_match("/^rip/i", $msg) && !empty($r['pin'])) { // lazy lazy
				$a = "You must send your PIN with the command, for example: rip 1234";
			} else if (@$m[1] == $r['pin'] || preg_match("/^no/i", $msg)) { // lazy lazy
				$q2 = $db->query("SELECT * FROM assassinations WHERE target = '".$db->real_escape_string($r['id'])."' AND ver = 'assassin' LIMIT 1");
				if ($q2->num_rows == 1) {
					$r2 = $q2->fetch_array();
					if (preg_match("/^no/i", $msg)) {// lazy af
						$db->query("DELETE FROM assassinations WHERE target = '".$db->real_escape_string($r['id'])."' AND ver = 'assassin'") or error_log($db->error);
						$a = "You have denied the assassination";
						$q5 = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($r2['assassin'])."' LIMIT 1");
						if ($q5->num_rows == 1) { // if not, huh!?
							$r5 = $q5->fetch_array();
							$sms = $client->account->messages->sendMessage(
								"8173693691", 
								$r5['phone'],
								"Your target has denied the assassination. If you wish to dispute this, please contact us on Twitter @assassins2k15."
							);
						}
					} else {
						$db->query("UPDATE assassinations SET ver = 'both' WHERE target = '".$db->real_escape_string($r['id'])."' AND ver = 'assassin'") or error_log($db->error);
						$db->query("UPDATE players SET assassinated = '1', target = '' WHERE id = '".$db->real_escape_string($r['id'])."' LIMIT 1") or error_log($db->error);
						assassination($r2['id']);
						$a = "Thank you for confirming your assassination";
						$q5 = $db->query("SELECT * FROM players WHERE target = '".$db->real_escape_string($r['id'])."' LIMIT 1");
						if ($q5->num_rows == 1) { // if not, huh!?
							$r5 = $q5->fetch_array();
							$db->query("UPDATE players SET target = '".$db->real_escape_string($r['target'])."' WHERE id = '".$db->real_escape_string($r5['id'])."' LIMIT 1") or error_log($db->error);
							$sms = $client->account->messages->sendMessage(
								"8173693691", 
								$r5['phone'],
								(($r2['assassin'] != $r5['id']) ? "Your target has fallen victim to a previous assassin" : "Assassination confirmed") . ", your new target is: ".uid2name($r['target'])
							);
						}
					}
				} else {
					$a = "You haven't been assassinated";
				}
			} else {
				$a = "The PIN you entered was not correct, please try again.";
			}
		}
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^pin(email)?$/i", $msg, $m)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if (@$m[1] == "email") {
			if (empty($r['pin'])) {
				email($r['email'], "Set your PIN for MHS Assassins", "Hello, ".current(explode(" ", $r['name']))."!<br><br>You need to set your PIN at http://assassins.in/p/" . base64_encode($r['vere'] . $r['salt'] . $r['id']) . "<br>You will need to remember the PIN that you set in order to play the game.<br><br>Best regards,<br>Seb, Ben & Sam"); 
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					$from,
					"The link has been e-mailed to you"
				);
			} else {
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					$from,
					"Your PIN has already been set. If you think you're seeing this message in error, please contact us on Twitter @assassins2k15"
				);
			}
		} else {
			if (empty($r['pin'])) {
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					$from,
					"You can set your pin here: http://assassins.in/p/" . base64_encode($r['vere'] . $r['salt'] . $r['id']) . "\nIf you want us to email you this link, please text pinemail"
				);
			} else {
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					$from,
					"Your PIN has already been set. If you think you're seeing this message in error, please contact us on Twittter @assassins2k15"
				);
			}
		}
	} else {
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"You are not registered for MHS Assassins 2015 on this phone number"
		);
	}
} else if (preg_match("/^(?:suicide|sudoku)[ ]*(\d+)?/i", $msg, $m)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if (!isset($m[1])) {
			$a = "To leave you must text suicide followed by your 4 digit PIN number. For example: suicide 1234";
		} elseif ($r['pin'] == $m[1]) {
			$q2 = $db->query("SELECT * FROM players WHERE target = '{$r['id']}' LIMIT 1");
			if ($q2->num_rows == 1) {
				$r2 = $q2->fetch_array(MYSQL_ASSOC);
				$db->query("UPDATE players SET target = '{$r['target']}' WHERE id = '{$r2['id']}' LIMIT 1");
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					$r2['phone'],
					"Your target has left the game, your new target is " . uid2name($r['target'])
				);
			} else {
				error_log("Player #{$r['id']} suicided with no assassin");
			}
			$q = $db->query("SELECT * FROM assassinations WHERE ver = 'assassin' AND target = '".$db->real_escape_string($r['id'])."' LIMIT 1") or error_log($db->error);
			if ($q->num_rows == 1) {
				$assassination = $q->fetch_array(MYSQL_ASSOC);
				$db->query("UPDATE assassinations SET ver = 'both' WHERE target = '".$db->real_escape_string($r['id'])."' AND ver = 'assassin'") or error_log($db->error);
				$db->query("UPDATE players SET assassinated = '1', target = '' WHERE id = '".$db->real_escape_string($r['id'])."' LIMIT 1") or error_log($db->error);
				assassination($assassination['id']);
				$a = "You took the cowards way out";
				$sms = $client->account->messages->sendMessage(
					"8173693691", 
					uid2phone($assassination['assassin']),
					"Your target took the cowards way out, we have granted you the assassination"
				);
			} else {
				$db->query("INSERT INTO `assassins`.`assassinations` (`assassin`,`target`,`timestamp`,`ver`) VALUES ('".$db->real_escape_string($r['id'])."','".$db->real_escape_string($r['id'])."','".time()."','both');") or error_log($db->error);
				assassination($db->insert_id);
				$db->query("UPDATE players SET suicided = 1, target = '' WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
				$a = "You have just committed sudoku.";
			}
		} else {
			$a = "The PIN you entered was not correct, please try again";
		}
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^whois$/i", $msg, $m)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
	} else {
		$r['name'] = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$r['name']
	);
} else if (preg_match("/^rank$/i", $msg)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		$rank = rank($r['id']);
		if ($rank['in_game'] == false) {
			$a = "You are not in the game";
		} else {
				$q = $db->query("SELECT count(*) FROM players");
				$r2 = $q->fetch_array(MYSQL_NUM);
				$total = $r2[0];
			$a = "You are ranked ".ordinal($rank['rank'])." of ".$total;
		}
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^me$/i", $msg, $m)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		$a = "http://assassins.in/u/{$r['uid']}";
	} else {
		$a = "You are not registered";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^(\d+)-([a-z0-9]{6})$/i", $msg, $m)) {
	$id = $m[1];
	$code = $m[2];
	$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($id)."' AND vere = '".$db->real_escape_string($code)."' LIMIT 1") or die($db->error);
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		if ($r['validated'] == 'email' || $r['validated'] == 'both') {
			$validated = 'both';
		} else {
			$validated = 'phone';
		}
		$db->query("UPDATE players SET validated = '$validated', phone = '".$db->real_escape_string($from)."' WHERE id = '".$db->real_escape_string($id)."' LIMIT 1") or die($db->error);
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"Thank you! Your phone number has been confirmed." . (($r['validated'] == "both" || $r['validated'] == "email") ? "" : " Please don't forget to verify your e-mail address.")
		);
	} else {
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"That code was not correct, please try again."
		);
	}
} else if (preg_match("/^subscribe$/i", $msg)) {
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		"To subscribe to instant updates please text subscribe followed by your carrier, for example:\nsubscribe sprint\nWe support: sprint/tmobile/verizon/att/metropcs"
	);
} else if (preg_match("/^unsubscribe$/i", $msg)) {
	$q = $db->query("SELECT * FROM subscriptions WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
		if ($q->num_rows == 1) {
			$db->query("DELETE FROM subscriptions WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
			 $sms = $client->account->messages->sendMessage(
				"8173693691", 
				$from,
				"You have been unsubscribed."
			);
		} else {
			$sms = $client->account->messages->sendMessage(
				"8173693691", 
				$from,
				"You are not subscribed."
			);
		}
} else if (preg_match("/^subscribe[ ]*(.+)?$/i", $msg, $m)) {
	$carrier_names = array("sprint" => "SprintPCS", "tmobile" => "T-Mobile", "verizon" => "Verizon", "att" => "AT&T", "metropcs" => "MetroPCS");
	if (array_key_exists($m[1], $carrier_names)) {
		$q = $db->query("SELECT * FROM subscriptions WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
		if ($q->num_rows == 1) {
			$r = $q->fetch_array(MYSQL_ASSOC);
			if ($r['carrier'] == $m[1]) {
				$a = "You are already subscribed";
			} else {
				$db->query("UPDATE subscriptions SET carrier = '$m[1]' WHERE phone = '".$db->real_escape_string($from)."'");
				$a = "Your carrier has been updated";
			}
		} else {
			$db->query("INSERT INTO subscriptions (`phone`,`carrier`) VALUES('".$db->real_escape_string($from)."', '{$m[1]}')");
			$a = "You have subscribed to instant updates. To unsubscribe reply to the number that you receive the updates from with the message unsubscribe.";
			smsUpdate("Your MHS Assassins instant updates will be coming from this address, to unsubscribe you must reply here with unsubscribe.", $from, $m[1]);
		}
	} else {
		$a = "Sorry we do not support that carrier, we only support: sprint/tmobile/verizon/att/metropcs";
	}
	$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			$a
		);
} else if (preg_match("/^test[ ]*$/i", $msg)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
		$registered = true;
		$pin = preg_match("/^\d+$/i", $r['pin']);
		$verified_phone = $r['validated'] == 'both' || $r['validated'] == 'phone' ? true : false;
		$verified_email = $r['validated'] == 'both' || $r['validated'] == 'email' ? true : false;
		if ($r['senior'] == "no") {
			$senior = "x";
		} else if ($r['senior'] == "yes") {
			$senior = "✓";
		} else {
			$senior = "?";
		}
	} else {
		$registered = false;
		$verified_phone = false;
		$verified_email = false;
		$pin = false;
		$senior = "?";
	}
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		"Registered - " . (($registered) ? "✓" : "x") . "\n" .
		"Verified phone - " . (($verified_phone) ? "✓" : "x") . "\n" .
		"Verified email - " . (($verified_email) ? "✓" : "x") . "\n" .
		"PIN Set - " . (($pin) ? "✓" : "x") . "\n" .
		"Senior - " . $senior
	);
} else if (preg_match("/^top$/i", $msg)) {
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQL_ASSOC);
	}
	
	$a = "";
	$top10 = rank(NULL, 10);
	foreach ($top10 as $key => $val) {
		if (isset($r) && $val['id']  == $r['id']) {
			$a .= "#{$val['rank']} You\n";
			unset($r); // fewest lines
		} else {
			$a .= "#{$val['rank']} ".(($val['killed'] == 1) ? "RIP " : "").uid2name($val['id'])."\n";
		}
	}
	
	if (isset($r)) {
		$a .= "#".current(rank($r['id']))." ".(($val['killed'] == 1) ? "RIP " : "")."You\n";
	}
	
	$a .= "http://assassins.in/#statistics";
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		$a
	);
} else if (preg_match("/^about[ ]*(.*)?/i", $msg, $m)) {
	if ($m[1]) {
		switch ($m[1]) {
			case "#":
				$a = "Use # followed by a message you want to be sent the administrator of the game, we will reply through this number";
			break;
			case "about":
				$a = "Use about followed by the name of a function you want to know how to use, for example: about subscribe";
			break;
			case "subscribe":
				$a = "Text subscribe to find out how to get instant updates for all kills made during assassins";
			break;
			case "pin":
				$a = "Text pin to recieve a link to the page where you can securely set your pin for assassins";
			break;
			case "target":
				$a = "Texting target will return nothing but the name of your target";
			break;
			case "eliminated":
				$a = "When you assassinate someone, you must text eliminated so the system knows the assassination has taken place, you will not be assigned a new target until your target has also confirmed via text that they were assassinated";
			break;
			case "rip":
				$a = "If/when you are assassinated you must text rip followed by your pin, for example: rip 1234, this confirms in the system that the assassination was genuine and allows the game to continue";
			break;
			case "suicide":
				$a = "If you wish to leave assassins at any point, you need to text suicide followed by your pin, for example: suicide 1234, this will take you out of the game completely and assign your assassin a new target";
			break;
			case "top":
				$a = "Texting top will display the top 10 people (ranked by kills) and your own rank";
			break;
			case "status":
				$a = "Texting status will show you your status, which has the following possible responses: alive/assassinated/suicided";
			break;
			case "test":
				$a = "Texting test will show you which of the 5 verification steps you have completed";
			break;
			case "whois":
				$a = "This simply returns your own name, to verify whose account the phone is attached to";
			break;
			case "me":
				$a = "This gives you the link to your personal statistics page";
			break;
			case "rank":
				$a = "This tells you what your rank in the game is";
			break;
			default:
				$a = "Function $m[1] does not exist. Available functions are: #, about, subscribe, pin, target, eliminated, rip, suicide, top, status, test, whois, me, rank";
		}
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			$a
		);
	} else {
		$sms = $client->account->messages->sendMessage(
			"8173693691", 
			$from,
			"To find out more about a function use about followed by the name of the function for example: about subscribe"
		);
	}
} else if (preg_match("/^donotreply$/i", $msg)) {
	// literally do nothing
} else {
	$sms = $client->account->messages->sendMessage(
		"8173693691", 
		$from,
		"Sorry, I do not understand.\nFor help with commands and instructions please go to http://assassins.in/#commands or contact us on Twitter @assassins2k15"
	);
}
?>