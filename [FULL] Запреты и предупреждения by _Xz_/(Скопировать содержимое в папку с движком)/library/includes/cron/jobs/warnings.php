<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if ($bb_cfg['warning']['enabled']) {
	DB()->query("UPDATE " . BB_WARNINGS . " SET auth = 0 WHERE auth != 3 AND time_end < " . TIMENOW);
	DB()->query("UPDATE " . BB_USERS . " u SET u.user_warnings = (SELECT COUNT(w.id) FROM " . BB_WARNINGS . " w WHERE u.user_id = w.user_id AND w.auth IN(1,3))");
}
