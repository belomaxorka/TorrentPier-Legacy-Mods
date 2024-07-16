<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
	die(basename(__FILE__));
}

global $bb_cfg, $lang, $userdata;

// ----------------- Настройки ----------------- //
// Максимальное количество поблагодаривших, при преодолении лимита, будет удаляться по одному пользователю с конца
$max_users = $bb_cfg['tor_thank_limit_per_topic'];
// --------------------------------------------- //

if (!$bb_cfg['tor_thank']) {
	$this->ajax_die($lang['MODULE_OFF']);
}

if (!$mode = (string)$this->request['mode']) {
	$this->ajax_die("invalid mode (empty)");
}

if (!$topic_id = (int)$this->request['topic_id']) {
	$this->ajax_die($lang['INVALID_TOPIC_ID']);
}

if (!$to_user_id = (int)$this->request['to_user_id']) { // poster_id
	$this->ajax_die("invalid to_user_id (empty)");
}

switch ($mode) {
	case 'add':
		// Проверка на гостя
		if (IS_GUEST) {
			$this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
		}

		// Проверка на спасибо самому себе
		if ($to_user_id == $userdata['user_id']) {
			$this->ajax_die($lang['LIKE_OWN_POST']);
		}

		// Проверка на повторное спасибо
		if (DB()->fetch_row("SELECT topic_id FROM " . BB_THX . " WHERE topic_id = $topic_id  AND user_id = " . $userdata['user_id'])) {
			$this->ajax_die($lang['LIKE_ALREADY']);
		}

		// Добавление спасибо
		$columns = "topic_id, user_id, to_user_id, time";
		$values = "$topic_id, {$userdata['user_id']}, $to_user_id, " . TIMENOW;
		DB()->query("INSERT IGNORE INTO " . BB_THX . " ($columns) VALUES ($values)");

		// Проверка на лимит
		$thanks_count = DB()->fetch_row("SELECT COUNT(*) as thx FROM " . BB_THX . " WHERE topic_id = $topic_id")['thx'];
		if ($thanks_count > $max_users) {
			DB()->query("DELETE FROM " . BB_THX . " WHERE topic_id = $topic_id ORDER BY time ASC LIMIT 1");
		}
		break;
	case 'get':
		// Проверка на возможность просмотра списка
		if (IS_GUEST && !$bb_cfg['tor_thanks_list_guests']) {
			$this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
		}

		$user_list = array();
		$sql = DB()->fetch_rowset("
			SELECT u.username, u.user_rank, u.user_id, thx.*
			FROM " . BB_THX . " thx, " . BB_USERS . " u
			WHERE thx.topic_id = $topic_id
				AND thx.user_id = u.user_id
		");
		foreach ($sql as $row) {
			$user_list[] = "<b>" . profile_url($row) . " <i>(" . bb_date($row['time']) . ")</i></b>";
		}

		if (!empty($user_list)) {
			$this->response['count_likes'] = "&nbsp;(" . declension(count($user_list), 'times') . ")";
			$this->response['html'] = implode(", ", $user_list);
		} else {
			$this->response['html'] = $lang['NO_LIKES'];
		}
		break;
	default:
		$this->ajax_die("Invalid mode: " . $mode);
}

$this->response['mode'] = $mode;
