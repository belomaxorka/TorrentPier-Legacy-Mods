<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $cfg_ann, $lang;

// Авто-обновление списка хостов
if (USE_AUTO_TRACKERS_UPDATE) {
	$sources_array = array(AUTO_UPDATE_SOURCE, AUTO_UPDATE_SOURCE_MIRROR_1, AUTO_UPDATE_SOURCE_MIRROR_2);
	foreach ($sources_array as $source) {
		// Пропускаем пустые зеркала
		if ($source === null) {
			continue;
		}
		// Проверяем список хостов на актуальность
		if (file_exists(HOSTS_FILE_PATH) && (time() - (int)filemtime(HOSTS_FILE_PATH) < 24 * 3600)) {
			break;
		}
		$get_hosts = @file_get_contents($source);
		// Перебираем источники
		if ($get_hosts !== false) {
			@unlink(HOSTS_FILE_PATH);
			if ((bool)file_put_contents(HOSTS_FILE_PATH, $get_hosts)) {
				$cfg_ann = array(); // Очищаем список хостов
				unset($get_hosts);
				break;
			}
		}
	}

	// Читаем файл с хостами
	if (file_exists(HOSTS_FILE_PATH) && empty($cfg_ann)) {
		$file_contents = file(HOSTS_FILE_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		// Формируем новый массив $cfg_ann
		$cfg_ann = array_map('trim', $file_contents);
		unset($file_contents);
	}
}

// Проверяем список хостов на пустоту
if (empty($cfg_ann)) {
	$this->ajax_die('Announcers list empty...');
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
	}

	// Отображаем статистику
	$html = '<div class="mrg_4 pad_4">';
	$html .= '<span class="seed">' . $lang['SEEDERS'] . ':&nbsp; <b>' . $seed . '</b> &nbsp;[&nbsp; 0 KB/s &nbsp;]</span> &nbsp;';
	$html .= '<span class="leech">' . $lang['LEECHERS'] . ':&nbsp; <b>' . $leech . '</b> &nbsp;[&nbsp; 0 KB/s &nbsp;]</span> &nbsp;';
	$html .= '</div>';
}

$this->response['html'] = $html;
$this->response['topic_id'] = $topic_id;
