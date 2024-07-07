<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Подключаем необходимые файлы
require(__DIR__ . '/config.php');
require(__DIR__ . '/Scraper.php');

// Инициализируем библиотеку
$scraper = new \Scraper();

// Открываем соединение с базой данных
$mysqli = new mysqli($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
if ($mysqli->connect_errno) {
	die(sprintf("Failed to connect: %s", $mysqli->connect_error));
}

// Устанавливаем кодировку
if (!$mysqli->set_charset($cfg_db['charset'])) {
	die(sprintf("Error while applying charset {$cfg_db['charset']}: %s", $mysqli->error));
}
