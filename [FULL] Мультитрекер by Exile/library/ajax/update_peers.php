<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $cfg_ann, $lang;

// Проверяем список хостов на пустоту
if (empty($cfg_ann)) {
	$this->ajax_die('Список хостов пустой...');
}

// Получаем ID топика
$topic_id = (int)$this->request['topic_id'];
if (!isset($topic_id)) {
	$this->ajax_die($lang['INVALID_TOPIC_ID']);
}

$html = '';
$seed = $leech = $completed = 0;

// Получаем массив с инфо-хэшами раздач
$row = DB()->fetch_row("SELECT info_hash FROM " . BB_BT_TORRENTS . " WHERE topic_id = " . $topic_id . " LIMIT 1");

if (!empty($row)) {
	$info_hash = bin2hex($row['info_hash']);
	$scraper = new \Scraper();
	$data = $scraper->scrape($info_hash, $cfg_ann, LIMIT_MAX_TRACKERS, ANNOUNCER_TIMEOUT_CONNECT);

	// Проверка на наличие ошибок
	if ($scraper->has_errors() && SHOW_DEAD_ANNOUNCERS) {
		$this->ajax_die($scraper->get_errors());
	}

	// Получаем статистику
	if (isset($data[$info_hash])) {
		$announcer = $data[$info_hash];
		if (isset($announcer['seeders'], $announcer['leechers'], $announcer['completed'])) {
			$seed = (int)$announcer['seeders'];
			$leech = (int)$announcer['leechers'];
			$completed = (int)$announcer['completed'];

			// Обновляем данные торрента
			DB()->query("UPDATE " . BB_BT_TORRENTS . " SET last_update = " . time() . ", ext_seeder = " . $seed . ", ext_leecher = " . $leech . " WHERE topic_id = $topic_id");
		}
	} else {
		// Отображаем статистику
		$html = '<div class="mrg_4 pad_4">';
		$html .= '<span class="seed">' . $lang['SEEDERS'] . ':&nbsp; <b>' . $seed . '</b> &nbsp;[&nbsp; 0 KB/s &nbsp;]</span> &nbsp;';
		$html .= '<span class="leech">' . $lang['LEECHERS'] . ':&nbsp; <b>' . $leech . '</b> &nbsp;[&nbsp; 0 KB/s &nbsp;]</span> &nbsp;';
		$html .= '</div>';
	}
}

$this->response['html'] = $html;
$this->response['topic_id'] = $topic_id;
