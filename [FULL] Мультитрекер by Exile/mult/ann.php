<?php

define('BB_BT_TORRENTS', 'bb_bt_torrents');
require(__DIR__ . '/init.php');

// Получаем массив с инфо-хэшами раздач
$seed = $leech = $completed = 0;
$sql = "SELECT info_hash FROM " . BB_BT_TORRENTS . " WHERE last_update < " . TIME_UPD . " ORDER BY reg_time DESC LIMIT " . TORRENT_PER_CYCLE;

// Обрабатываем каждую раздачу
if ($result = $mysqli->query($sql)) {
	while ($row = $result->fetch_row()) {
		$data = $scraper->scrape(bin2hex($row[0]), $cfg_ann);
		// Проверка на наличие ошибок
		if ($scraper->has_errors()) {
			die(print_r($scraper->get_errors(), true));
		}
		if (is_array($data) && ($peer_data = $data[bin2hex($row[0])])) {
			// Получаем данные о раздаче от трекеров
			foreach ($peer_data as $announce) {
				$seed = (int)$announce['seeders'];
				$leech = (int)$announce['leechers'];
				$completed = (int)$announce['completed'];
			}
			// Обновляем данные торрента
			$sql_update = "UPDATE " . BB_BT_TORRENTS . " SET last_update = " . $data['last_update'] . ", ext_seeder = " . $seed . ", ext_leecher = " . $leech . " WHERE info_hash = '" . $mysqli->real_escape_string($row[0]) . "'";
			if ($res_upd = $mysqli->query($sql_update)) {
				$seed = $leech = 0;
			} else {
				die(sprintf("Ошибка при обновлении пиров: %s", $mysqli->error));
			}
		}
	}
	$result->close();
}
$mysqli->close();
