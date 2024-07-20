<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata;

$mode = (string)$this->request['mode'];

// Максимальное количество закладок (указать число) (false - выключено)
// Исключение: Администраторы и модераторы (IS_AM)
$max_book_marks = 100;

switch ($mode) {
	case 'add':
		$tid = (int)$this->request['tid'];
		$fid = (int)$this->request['fid'];

		if (DB()->fetch_row('SELECT book_id FROM ' . BB_BOOK . " WHERE topic_id = $tid AND user_id = " . $userdata['user_id'])) {
			$this->ajax_die($lang['BOOKMARKS_ALREADY']);
		}

		// Проверка на лимит закладок
		if (is_numeric($max_book_marks) && !IS_AM) {
			$book_count = DB()->fetch_row('SELECT COUNT(*) AS books FROM ' . BB_BOOK . " WHERE user_id = " . $userdata['user_id']);
			if ($book_count['books'] > $max_book_marks) {
				$this->ajax_die($lang['BOOKMARKS_LIMIT_REACHED']);
			}
		}

		// Добавляем закладку в базу
		$columns = 'user_id, topic_id, forum_id, time';
		$values = "{$userdata['user_id']}, $tid, $fid, " . TIMENOW;

		DB()->query("INSERT IGNORE INTO " . BB_BOOK . " ($columns) VALUES ($values)");
		$this->response['ok'] = $lang['BOOKMARKS_ADD_SUCCESS'];
		break;
	case 'delete':
		if (empty($this->request['confirmed'])) {
			$this->prompt_for_confirm();
		}

		$tid = (int)$this->request['tid'];

		// Удаляем закладку из базы
		DB()->query("DELETE FROM " . BB_BOOK . " WHERE topic_id = $tid AND user_id = " . $userdata['user_id']);
		$this->response['ok'] = $lang['BOOKMARKS_REMOVE_SUCCESS'];
		$this->response['tid'] = $tid;
		break;
	default:
		$this->ajax_die('Invalid mode:' . $mode);
		break;
}

$this->response['mode'] = $mode;
