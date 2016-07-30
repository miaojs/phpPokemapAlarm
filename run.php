<?php
include("./config.php");

$pdb = new SQLite3($path);
$pdb->busyTimeout(5000);
$db = new SQLite3("alarm.db");
$watch = array();

$db->exec("create table if not exists `pokemon` (encounter varchar(255));");
$db->exec("create table if not exists `watch` (id integer);");
$db->exec("delete from `watch`;");

$p_file = fopen("pokemon.json", "r");

$pokemon = json_decode(fread($p_file, filesize("pokemon.json")),true);
fclose($p_file);

watchPokemon(131);
watchPokemon(143);
watchPokemon(21);
print_r($watch);
foreach($watch as $pid) {

do {
	
try {	
$res = $pdb->query('SELECT * FROM `pokemon` WHERE `pokemon_id` = '. $pid . ' ORDER BY `disappear_time` DESC LIMIT 1;');
if ($res_array = $res->fetchArray(SQLITE3_ASSOC)) {
	$lastSeen = strtotime($res_array["disappear_time"] . "UTC");
echo "Last " . $pokemon[$pid] . " seen: " . date("F j, Y, g:i a", strtotime($res_array["disappear_time"] . "UTC")) . ".";
if ($lastSeen > time()) {
	echo " Not expired";
	foreach($notify as $sendTo) {
	exec("curl -k -X POST 'https://api.twilio.com/2010-04-01/Accounts/" . $twilioSid . "/Messages.json' --data-urlencode 'To=+" . $sendTo . "' --data-urlencode 'From=+" . $twilioFrom . "' --data-urlencode 'Body=" . $pokemon[$pid] . " is around. Expires in " . round(($lastSeen - time())/60,2) . " minutes.' -u '" . $twilioSid . ":" . $twilioAuth . "'");
	}
} else {
	echo " Expired";
	foreach($notify as $sendTo) {
	
	}
}
echo "\n";
}
} catch (Error $e) {
	echo "error" . $e->getMessage();
	sleep(1);
	continue;
}
	break;
} while (1==1);


}


function watchPokemon($pid) {
	global $watch;
	$watch[$pid] = $pid;
	
}
/* function watchPokemon($pid) {
	global $db;
	global $pokemon;
	unset($wres);
	$wres = $db->query("SELECT * FROM `watch` where `id` = " . $pid . ";");
	if ($wres->fetchArray()) {
		//already watching
	} else {
		$db->exec("INSERT INTO `watch` (`id`) values (" . $pid . ");");
	}

}
*/
$pdb->close();
unset $pdb;
$db->close();
unset $db;

