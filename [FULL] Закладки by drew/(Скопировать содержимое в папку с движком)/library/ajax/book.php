<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata;

$mode = (string)$this->request['mode'];
$html = '';

switch ($mode) {
	case 'add':
		$tid = (int)$this->request['tid'];
		$fid = (int)$this->request['fid'];

		if (DB()->fetch_row('SELECT book_id FROM ' . BB_BOOK . " WHERE topic_id = $tid AND user_id = " . $userdata['user_id'])) {
			$this->ajax_die('Вы уже добавили данную тему в закладки');
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
