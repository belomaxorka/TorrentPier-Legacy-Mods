<?php
/**
 * Torrenbar for TorrentPier
 * Author: _Xz_
 */

define('BB_ROOT', '.././');
require(BB_ROOT . 'common.php');
require(INC_DIR . 'class.torrentbar.php');

$user_id = (int)request_var('u', '');
torrentbar($user_id);
