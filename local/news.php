<?php
/*
	Скрипт, меняющий GIF с новостями на Pixoo 64
*/

require_once('pixoo64.php');

while (true) {
	// Забираем GIF файл, срендеренный скриптом rss_pic.php каждые 3 минуты, задержка кадра 6 сек
	pixoo_download_gif('https://gw-zone.ru/rss_pic.php', 30, false, 6000);
	sleep(180);
}