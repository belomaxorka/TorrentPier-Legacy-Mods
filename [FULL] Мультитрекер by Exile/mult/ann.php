<?php

define('BB_BT_TORRENTS', 'bb_bt_torrents');
require(__DIR__ . '/init.php');

// Авто-обновление списка хостов
if (USE_AUTO_TRACKERS_UPDATE) {
	$cfg_ann = array(); // Очищаем список хостов
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
				unset($get_hosts);
				break;
			}
		}
	}

	// Читаем файл с хостами
	if (file_exists(HOSTS_FILE_PATH)) {
		$file_contents = file(HOSTS_FILE_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		// Формируем новый массив $cfg_ann
		$cfg_ann = array_map('trim', $file_contents);
		unset($file_contents);
	}
}

// Проверяем список хостов на пустоту
if (empty($cfg_ann)) {
	die('Announcers list empty...');
}

$seed = $leech = $completed = 0;

// Получаем массив с инфо-хэшами раздач
$last_update = isset($_GET['force_upd']) ? '' : " WHERE last_update < " . TIME_UPD . " ";
$sql = "SELECT info_hash FROM " . BB_BT_TORRENTS . " $last_update ORDER BY reg_time DESC LIMIT " . TORRENT_PER_CYCLE;

// Обрабатываем каждую раздачу
if ($result = $mysqli->query($sql)) {
	while ($row = $result->fetch_assoc()) {
		$data = $scraper->scrape(bin2hex($row['info_hash']), $cfg_ann, LIMIT_MAX_TRACKERS, ANNOUNCER_TIMEOUT_CONNECT);

		// Проверка на наличие ошибок
		if ($scraper->has_errors() && SHOW_DEAD_ANNOUNCERS) {
			die(print_r($scraper->get_errors(), true));
		}

		// Получаем статистику
		if (isset($data[bin2hex($row['info_hash'])])) {
			$announcer = $data[bin2hex($row['info_hash'])];
			if (isset($announcer['seeders'], $announcer['leechers'], $announcer['completed'])) {
				$seed = (int)$announcer['seeders'];
				$leech = (int)$announcer['leechers'];
				$completed = (int)$announcer['completed'];

				// Вывод статистики
				print_r($announcer);
				echo bin2hex($row['info_hash']);
				echo '<br/>';

				// Обновляем данные торрента
				$sql_update = "UPDATE " . BB_BT_TORRENTS . " SET last_update = " . time() . ", ext_seeder = " . $seed . ", ext_leecher = " . $leech . " WHERE info_hash = '" . rtrim($mysqli->real_escape_string($row['info_hash']), ' ') . "'";
				if ($mysqli->query($sql_update)) {
					$seed = $leech = $completed = 0;
				} else {
					die(sprintf("Error while updating peers: %s", $mysqli->error));
				}
			}
		}
	}

	$result->close();
}

$mysqli->close();
