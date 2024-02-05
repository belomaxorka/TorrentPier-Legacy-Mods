<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata;

$mode = (string)$this->request['mode'];
$html = '';

// Максимальное количество закладок (указать число) (false - выключено)
// Исключение: Администраторы и модераторы (IS_AM)
$max_book_marks = false;

switch ($mode) {
	case 'add':
		$tid = (int)$this->request['tid'];
		$fid = (int)$this->request['fid'];

		if (DB()->fetch_row('SELECT book_id FROM ' . BB_BOOK . " WHERE topic_id = $tid AND user_id = " . $userdata['user_id'])) {
			$this->ajax_die('Вы уже добавили данную тему в закладки');
		}

		// Проверка на лимит закладок
		if (is_numeric($max_book_marks) && !IS_AM) {
			$book_count = DB()->fetch_row('SELECT COUNT(*) AS books FROM ' . BB_BOOK . " WHERE user_id = " . $userdata['user_id']);
			if ($book_count['books'] >= $max_book_marks) {
				$this->ajax_die('У вас слишком много закладок...');
			}
		}

		// Добавляем закладку в базу
		$columns = 'user_id, topic_id, forum_id';
		$values = "{$userdata['user_id']}, $tid, $fid";

		DB()->query("INSERT IGNORE INTO bb_book ($columns) VALUES ($values)");
		$this->response['ok'] = 'Закладка успешно добавлена';
		break;
	case 'delete':
		$tid = (int)$this->request['tid'];

		// Удаляем закладку из базы
		DB()->query("DELETE FROM bb_book WHERE topic_id = $tid AND user_id = " . $userdata['user_id']);
		$this->response['ok'] = 'Закладка успешно удалена';
		break;
	default:
		$this->ajax_die('Invalid mode:' . $mode);
		break;
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
