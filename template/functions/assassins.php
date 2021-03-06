<?php
// all of these functions are purpose built for assassins
function begun() {
	return time() > GAME_START;
}

function registration_ended() {
	return time() > REGISTRATION_DEADLINE;
}

function format_phone_number($phone_number) {
	$numeric_phone_number = intval($phone_number);
	return '('.substr($numeric_phone_number, 0, 3).') '.substr($numeric_phone_number, 3, 3).'-'.substr($numeric_phone_number,6);
}

function email($to, $subject, $message, $replyto = false, $headers = NULL) {
        $replyto = ($replyto ? "$replyto" : EMAIL_NAME . ' <' . EMAIL_USER . '@' . EMAIL_DOMAIN . '>');
        if (!$headers || $headers == NULL) {
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= 'From: ' . EMAIL_NAME . ' <no-reply@' . EMAIL_DOMAIN . '.net>' . "\r\n" .
                'Reply-To: ' . $replyto . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        }
        return mail($to, $subject, $message, $headers);
}

function smsUpdate($msg, $to, $carrier = NULL) {
	global $db, $carriers, $smsmail, $carriersWithSubjects;
	if ($carrier == NULL) {
		$q = $db->query("SELECT carrier FROM subscriptions WHERE phone = '".$db->real_escape_string($to)."' LIMIT 1");
		if ($q->num_rows == 1) {
			$r = $q->fetch_array(MYSQL_ASSOC);
			$carrier = $carriers[$r['carrier']];
		} else {
			return false;
		}
	}
	return exec('echo "'.$msg.'" | mail '.$to.'@'.$carriers[$carrier].' '.((in_array($carrier, $carriersWithSubjects)) ? "-s Message" : "").' -aFrom:'.$smsmail);
}

function groupme_sys_bot($text) {
	return exec('curl -–silent -X POST '.escapeshellarg('https://api.groupme.com/v3/bots/post?bot_id=a57c670a29964c66eb90a4e5a4&text='.urlencode($text)));
}

function groupme_user_bot($text) {
	return exec('curl -–silent -X POST '.escapeshellarg('https://api.groupme.com/v3/bots/post?bot_id=c8d263a9fad4b29ca6e4b2a4b0&text='.urlencode($text)));
}

function smtp2sms($msg, $to, $subject = "") {
	error_log("Carrier '$_from' not supported");
	return exec('echo "'.$msg.'" | mail '.$to.' -s "'.$subject.'" -aFrom:'.$smsmail);
}

function smsUpdateAll($msg) {
	global $db, $carriers, $smsmail, $carriersWithSubjects;
	$q = $db->query("SELECT * FROM subscriptions");
	while ($r = $q->fetch_array(MYSQL_ASSOC)) {
		exec('echo "'.$msg.'" | mail '.$r['phone'].'@'.$carriers[$r['carrier']].' '.((in_array($r['carrier'], $carriersWithSubjects)) ? "-s Assassination" : "").' -aFrom:'.$smsmail);
	}
	return true;
}

function assassination($id) {
	global $db, $base;
	$q = $db->query("SELECT * FROM assassinations WHERE id = '".$db->real_escape_string($id)."' LIMIT 1");
	if ($q->num_rows == 1) {
		$r = $q->fetch_array(MYSQLI_ASSOC);
		$q = $db->query("SELECT * FROM pictures WHERE user_id = '{$r['assassin']}' AND used = 0 ORDER BY id DESC LIMIT 1");
		if ($q->num_rows == 1) {
			$p = $q->fetch_array(MYSQLI_ASSOC);
			$media = $base . $p['assassins_url']; // uri
		} else {
			$media = false;
		}
		if (isset($p)) {
			$db->query("UPDATE pictures SET used = 1 WHERE id = '{$p['id']}' LIMIT 1");
		}
		if ($r['assassin'] == $r['target']) { // suicided
			$text = trim(uid2name($r['assassin']))." committed suicide";
		} else {
			$text = trim(uid2name($r['assassin']))." has assassinated ".trim(uid2name($r['target']));
		}
		tweet($text . "", $media); // hashtag?
		return true;
	} else {
		return false;
	}
}

function send_sms($from, $to, $body) {
	return $twilio_client->messages->create(
			$to,
			array(
					'from' => $from,
					'body' => $body
			)
	);
}

function tweet($msg, $media = false) {
	return;
	global $cb;
	if ($media) {
		$media_id = $cb->media_upload(array(
			'media' => $media
		));
		$params = array(
		  'status' => $msg,
		  'media_ids' => $media_id->media_id_string
		);
	} else {
		$params = array(
		  'status' => $msg
		);
	}
	return $cb->statuses_update($params);
}

function uid2name($uid) {
	global $db;
	$q = $db->query("SELECT name FROM players WHERE id = '".$db->real_escape_string($uid)."' LIMIT 1");
	$r = $q->fetch_array(MYSQLI_NUM);
	return trim($r[0]);
}

function uid2uid($uid) {
	global $db;
	$q = $db->query("SELECT uid FROM players WHERE id = '".$db->real_escape_string($uid)."' LIMIT 1");
	$r = $q->fetch_array(MYSQLI_NUM);
	return trim($r[0]);
}

function uid2phone($uid) {
	global $db;
	$q = $db->query("SELECT phone FROM players WHERE id = '".$db->real_escape_string($uid)."' LIMIT 1");
	$r = $q->fetch_array(MYSQLI_NUM);
	return $r[0];
}

function rank($id, $top = false) {
	global $db;
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
	".(($top != false) ? "LIMIT $top" : "")) or die($db->error);
	if ($top) {
		$a = array();
		$i = 1;
		while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
			$a[] = array_merge(array("rank"=>$i), $r); // if $key != $i; ?
			$i++;
		}
		return $a;
	} else {
		$i = 1;
		while ($r = $q->fetch_array(MYSQLI_ASSOC)) {
			if ($id == $r['id']) {
				return array_merge(array("rank"=>$i, "in_game"=>true), $r);
			} else {
				$i++;
			}
		}
		return array("rank"=>$i, "assassin"=>"", "target"=>"", "id"=>$id, "kills"=>0, "killed"=>0, "in_game"=>false);
	}
}