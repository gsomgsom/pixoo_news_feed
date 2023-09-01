<?php
/*
	Pixoo 64

	Упрощённый интерфейс к REST API
	https://github.com/4ch1m/pixoo-rest
*/


function encode_array($args) {
	if (!is_array($args))
		return false;

	$c = 0;
	$out = '';
	foreach($args as $name => $value) {
		if($c++ != 0) $out .= '&';
		$out .= urlencode("$name").'=';
		if(is_array($value)) {
			$out .= urlencode(serialize($value));
		}
		else {
			$out .= urlencode("$value");
		}
	}
	return $out . "\n";
}

function pixoo_cmd($cmd, $params) {
	$ch = curl_init("http://localhost:5000/".$cmd);
	curl_setopt($ch, CURLOPT_HEADER, "accept: application/json");
	curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/x-www-form-urlencoded");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, encode_array($params));
	$r = curl_exec($ch);
	if(curl_error($ch)) {
		echo curl_error($ch);
	}
	else {
		//echo $r;
	}
	curl_close($ch);
}

function pixoo_cmd_json($cmd, $params) {
	$ch = curl_init("http://localhost:5000/".$cmd);
	$payload = json_encode($params);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$r = curl_exec($ch);
	if(curl_error($ch)) {
		echo curl_error($ch);
	}
	else {
		//echo $r;
	}
	curl_close($ch);
}

function pixoo_fill($r = 255, $g = 255, $b = 255, $push_immediately = true) {
	pixoo_cmd('fill', [
		'r' => $r,
		'g' => $g,
		'b' => $b,
		'push_immediately' => $push_immediately,
	]);
}

function pixoo_text($text = '', $x = 0, $y = 0, $r = 255, $g = 255, $b = 255, $push_immediately = true) {
	pixoo_cmd('text', [
		'text' => $text,
		'x' => $x,
		'y' => $y,
		'r' => $r,
		'g' => $g,
		'b' => $b,
		'push_immediately' => $push_immediately,
	]);
}

function pixoo_download_image($url = 'https://hsto.org/getpro/habr/avatars/027/ef9/fd5/027ef9fd50a6ad8a8ccf27260892a156.png', $timeout = 30, $ssl_verify = false, $x = 0, $y = 0, $push_immediately = true ) {
	pixoo_cmd('download/image', [
		'url' => $url,
		'timeout' => $timeout,
		'ssl_verify' => $ssl_verify,
		'x' => $x,
		'y' => $y,
		'push_immediately' => $push_immediately,
	]);
}

function pixoo_download_gif($url = 'https://img.itch.zone/aW1hZ2UvMTU0NDA1MC8xMDM3NDMwNC5naWY=/original/ZFV5eG.gif', $timeout = 30, $ssl_verify = false, $speed = 100, $skip_first_frame = false ) {
	pixoo_cmd('download/gif', [
		'url' => $url,
		'timeout' => $timeout,
		'ssl_verify' => $ssl_verify,
		'speed' => $speed,
		'skip_first_frame' => $skip_first_frame,
	]);
}

function pixoo_score_board($blue = 0, $red = 0) {
	pixoo_cmd_json('/passthrough/tools/setScoreBoard', [
		"Command" => "Tools/SetScoreBoard",
		"BlueScore" => intval($blue),
		"RedScore" => intval($red),
	]);
}