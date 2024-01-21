<?php

// Подключаем необходимые файлы
require(__DIR__ . '/config.php');
require(__DIR__ . '/Scraper.php');

// Инициализируем библиотеку
$scraper = new \Scraper();

// Открываем соединение с базой данных
$mysqli = new mysqli($cfg_db['host'], $cfg_db['user'], $cfg_db['pass'], $cfg_db['db']);
if ($mysqli->connect_errno) {
	die(sprintf("Не удалось подключиться: %s", $mysqli->connect_error));
}

// Устанавливаем кодировку
if (!$mysqli->set_charset($cfg_db['charset'])) {
	die(sprintf("Ошибка при загрузке набора символов {$cfg_db['charset']}: %s", $mysqli->error));
}
