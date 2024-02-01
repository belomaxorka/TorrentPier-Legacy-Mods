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
