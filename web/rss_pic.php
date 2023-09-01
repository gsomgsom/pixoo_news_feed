<?php
/*
Формирует анимированный GIF для Pixoo 64 (квадратный анимированный 64 x 64)
Выводит туда актуальный курс валют и последние новости из RSS (из открытого канала Telegram)
*/

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require "AnimGif.php";

const MAX_ENTRIES = 5; // кол-во новостей
const PAGE_LINES = 9; // кол-во строк на страницу
const JSON_FEED = 'https://wtf.roflcopter.fr/rss-bridge/?action=display&bridge=TelegramBridge&username=breakingmash&format=Json'; // источник RSS (JSON)
const FONT = 'pixelcyr_normal.ttf'; // основной шрфит
const SIZE = 5; // размер основного шрфита
const FONT2 = 'retro-land-mayhem.ttf'; // шрифт курсов валют
const SIZE2 = 7; // размер шрфита курсов валют
const WIDTH = 64; // ширина итоговой GIF
const HEIGHT = 64; // высота итоговой GIF
const PICS_DIR = './rss_pics/'; // временная папка с кадрами

// Раскидывает текст по строкам
function makeTextBlock($text, $fontfile, $fontsize, $width) {    
	$words = explode(' ', $text);
	$lines = array($words[0]);
	$currentLine = 0;
	for ($i = 1; $i < count($words); $i++) {
		$lineSize = imagettfbbox($fontsize, 0, $fontfile, $lines[$currentLine] . ' ' . $words[$i]);
		if ($lineSize[2] - $lineSize[0] < $width) {
			$lines[$currentLine] .= ' ' . $words[$i];
		}
		else {
			$currentLine++;
			$lines[$currentLine] = $words[$i];
		}
	}
	return implode("\n", $lines);
}

// Получает курс валют
function getRate($from = 'EUR', $to = 'RUB') {
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.coingate.com/v2/rates/merchant/{$from}/{$to}",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		//echo "cURL Error #:" . $err;
		return 0;
	} else {
		//echo "{$from} -> {$to} = {$response}\n";
		return $response;
	}
}

// Формирует кадр ленты новостей с текстом
function makeTextFrame($fn, $n, $lines, $dateTime, $showNextPage = false) {
	$bg = [
		[40, 0, 0],
		[0, 40, 0],
		[0, 0, 40],
		[40, 40, 0],
		[0, 40, 20],
		[40, 0, 40],
		[40, 0, 0],
		[0, 40, 0],
		[0, 0, 40],
		[40, 40, 0],
		[0, 40, 40],
		[40, 0, 40],
	];
	$im = imagecreatetruecolor(WIDTH, HEIGHT);
	$black = imagecolorallocate($im, $bg[$n][0], $bg[$n][1], $bg[$n][2]);
	$green = imagecolorallocate($im, 50, 255, 50);
	$blue = imagecolorallocate($im, 50, 50, 255);
	$red = imagecolorallocate($im, 255, 50, 50);
	$white = imagecolorallocate($im, 255, 255, 255);
	$yellow = imagecolorallocate($im, 255, 255, 50);
	imagefilledrectangle($im, 0, 0, WIDTH-1, HEIGHT-1, $black);
	imagettftext($im, SIZE, 0, 0, SIZE, $green, FONT, $n);
	imagettftext($im, SIZE, 0, SIZE, SIZE, $red, FONT, $dateTime);
	foreach ($lines as $ln => $line) {
		imagettftext($im, SIZE, 0, 0, SIZE + 1 + SIZE + $ln * (SIZE + 1), $white, FONT, $line);
	}
	if ($showNextPage) {
		imagettftext($im, SIZE, 0, 33, 63, $yellow, FONT, '.');
		imagettftext($im, SIZE, 0, 32, 62, $yellow, FONT, '_');
	}
	imagepng($im, PICS_DIR.(sprintf("%02d", $fn)).'.gif');
	imagedestroy($im);
}

// Возвращает GD image из файла картинки произвольного формата по URL
function imagecreatefromany($filepath) {
	$type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
	$allowedTypes = [
		1,  // [] gif
		2,  // [] jpg
		3,  // [] png
	];
	if (!in_array($type, $allowedTypes)) {
		return false;
	}
	switch ($type) {
		case 1 :
			$im = imagecreatefromgif($filepath);
		break;
		case 2 :
			$im = imagecreatefromjpeg($filepath);
		break;
		case 3 :
			$im = imagecreatefrompng($filepath);
		break;
	}	
	return $im;  
}

// Формирует кадр ленты новостей с фото
function makePhotoFrame($fn, $n, $img_url, $dateTime) {
	$bg = [
		[40, 0, 0],
		[0, 40, 0],
		[0, 0, 40],
		[40, 40, 0],
		[0, 40, 20],
		[40, 0, 40],
		[40, 0, 0],
		[0, 40, 0],
		[0, 0, 40],
		[40, 40, 0],
		[0, 40, 40],
		[40, 0, 40],
	];
	$im = imagecreatetruecolor(WIDTH, HEIGHT);
	$black = imagecolorallocate($im, $bg[$n][0], $bg[$n][1], $bg[$n][2]);
	$green = imagecolorallocate($im, 50, 255, 50);
	$blue = imagecolorallocate($im, 50, 50, 255);
	$red = imagecolorallocate($im, 255, 50, 50);
	$white = imagecolorallocate($im, 255, 255, 255);
	$yellow = imagecolorallocate($im, 255, 255, 50);
	imagefilledrectangle($im, 0, 0, WIDTH-1, HEIGHT-1, $black);
	imagettftext($im, SIZE, 0, 0, SIZE, $green, FONT, $n);
	imagettftext($im, SIZE, 0, SIZE, SIZE, $red, FONT, $dateTime);

	$inner_im = imagecreatefromany($img_url);;
	list($w, $h) = getimagesize($img_url);
	$imgRatio = $w / $h;
	if ($imgRatio > 1) {
		$scale = $w / WIDTH;
		imagecopyresampled ($im, $inner_im, 0, 6 + ((HEIGHT - 6) - ceil($h / $scale)) / 2, 0, 0, WIDTH, ceil($h / $scale), $w, $h);
	}
	else {
		$scale = $h / (HEIGHT - 6);
		imagecopyresampled ($im, $inner_im, 0 + (WIDTH - ceil($w / $scale)) / 2, 6, 0, 0, ceil($w / $scale), (HEIGHT - 6), $w, $h);
	}
	imagepng($im, PICS_DIR.(sprintf("%02d", $fn)).'.gif');
	imagedestroy($im);
}

$n = 1;
$fn = 1;

// Получаем новости в JSON
$feed = json_decode(file_get_contents(JSON_FEED), true, 512, JSON_OBJECT_AS_ARRAY);

// Чистим папку с кадрами
exec("rm -f ".PICS_DIR."*");

// Генерируем кадр с курсом валют
$im = imagecreatetruecolor(WIDTH, HEIGHT);
$black = imagecolorallocate($im, 0, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$green = imagecolorallocate($im, 50, 255, 50);
$blue = imagecolorallocate($im, 50, 50, 255);
$red = imagecolorallocate($im, 255, 50, 50);
$yellow = imagecolorallocate($im, 255, 255, 50);
imagefilledrectangle($im, 0, 0, WIDTH-1, HEIGHT-1, $black);
$dateTime = date('Y.m.d H:i');
imagettftext($im, SIZE, 0, 1, SIZE, $white, FONT, "КУРС ВАЛЮТ НА");
imagettftext($im, SIZE, 0, 1, SIZE + (SIZE + 1) * 1, $red, FONT, $dateTime);
imagettftext($im, SIZE2, 0, 1, 24 + (SIZE2 + 2) * 0, $green, FONT2, "USD: ".(sprintf("%01.2f", getRate('USD', 'RUB') / 1)));
imagettftext($im, SIZE2, 0, 1, 24 + (SIZE2 + 2) * 1, $yellow, FONT2, "EUR: ".(sprintf("%01.2f", getRate('EUR', 'RUB') / 1)));
imagettftext($im, SIZE2, 0, 1, 24 + (SIZE2 + 2) * 2, $red, FONT2, "CNY: ".(sprintf("%01.2f", getRate('CNY', 'RUB') / 1)));
imagettftext($im, SIZE2, 0, 1, 24 + (SIZE2 + 2) * 3, $white, FONT2, "BTC: ".(sprintf("%01.2f", getRate('BTC', 'RUB') / 1000000))."M");
imagettftext($im, SIZE2, 0, 1, 24 + (SIZE2 + 2) * 4, $white, FONT2, "ETH: ".(sprintf("%01.2f", getRate('ETH', 'RUB') / 1000000))."M");
imagepng($im, PICS_DIR.'00.gif');
imagedestroy($im);

// Генерируем новые кадры ленты новостей
foreach (array_slice($feed['items'], 0, MAX_ENTRIES) as $item) {
	// Выколупаем все картинки (кроме emoji)
	preg_match_all(
		'/<img.+src=[\'"](.+?)[\'"].*>|background-image ?: ?url\([\'" ]?(.*?\.(?:png|jpg|jpeg|gif|svg))/i',
		$item['content_html'],
		$matches,
		PREG_SET_ORDER
	);
	$image = [];
	foreach ($matches as $set) {
		unset($set[0]);
		foreach ($set as $url) {
			if ($url && (!strpos($url, 'emoji'))) {
				$image[] = $url;
			}
		}
	}

	// Выколупываем и чистим текст новости
	$text = html_entity_decode(trim(strip_tags($item['content_html'])), ENT_QUOTES, 'UTF-8');
	$text = str_replace("❤ Подписывайся на Kub Mash", '', $text);
	$text = str_replace('😋 Подписывайся на Mash', '', $text);
	$text = str_replace('Forwarded from', 'FW:', $text);
	$text = str_replace("\n\t\n", ': ', $text);
	$text = str_replace("Mash\n", '', $text);
	$text = str_replace("Media is too big\nVIEW IN TELEGRAM\n", '', $text);
	$caption = substr($text,0,strpos($text,'.')); // забираем первое предложение из текста
	if (substr($caption,-1) != '.') $caption .= '.';

	$dateTime = date('Y.m.d H:i', strtotime($item['date_modified']));
	$textBlock = makeTextBlock($caption, FONT, SIZE, WIDTH);
	$lines = explode("\n", $textBlock);
	$pages = ceil(count($lines) / PAGE_LINES);
	for ($p = 0; $p < $pages; $p++) {
		$slicedLines = array_slice($lines, $p * PAGE_LINES, PAGE_LINES);
		makeTextFrame($fn, $n, $slicedLines, $dateTime, $p + 1 < $pages);
		$fn++;
	}
	// если есть картинки, берём первую по списку
	if (count($image) > 0) {
		makePhotoFrame($fn, $n, $image[0], $dateTime);
		$fn++;
	}
	$n++;
}

// Компилируем GIF
$anim = new GifCreator\AnimGif();
$anim->create(PICS_DIR, [100]); // задержка не важна, Pixoo при отображении ставит свою
$gif = $anim->get();
header("Content-type: image/gif");
echo $gif;
exit;