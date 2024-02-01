<?php

define('BB_SCRIPT', 'parser');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

// Start session management
$user->session_start(array('req_login' => true));

// Проверка наличия прав доступа
if (!IS_AM && !$bb_cfg['parser_for_all']) {
	bb_die($lang['NOT_AUTHORISED']);
}

// Подключаем вспомогательные файлы
require_once(INC_DIR . '/parser/random_int.php'); // Polyfill для random_int()
require_once(INC_DIR . '/parser/userAgent.php'); // Генератор User-agent
