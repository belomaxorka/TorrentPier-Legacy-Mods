<?php

include('config.php');
include('func.php');
include('class.getpeers.php');
include('class.remote.php');
include('class.fbenc.php');
include('class.bittorrent.php');

$gp = new \getpeers();
$mysqli = new mysqli($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
if ($mysqli->connect_errno) {
	printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
	exit();
}
if (!$mysqli->set_charset($cfg_db['charset'])) {
	printf("Ошибка при загрузке набора символов {$cfg_db['charset']}: %s\n", $mysqli->error);
	exit();
}
