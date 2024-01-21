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
		if ($scraper->has_errors() && SHOW_DEAD_ANNOUNCERS) {
			die(print_r($scraper->get_errors(), true));
		}

		// Получаем статистику
		if (is_array($data) && $announcer = $data[bin2hex($row[0])]) {
			$seed = (int)$announcer['seeders'];
			$leech = (int)$announcer['leechers'];
			$completed = (int)$announcer['completed'];

			// Обновляем данные торрента
			if (isset($seed, $leech, $completed)) {
				$sql_update = "UPDATE " . BB_BT_TORRENTS . " SET last_update = " . time() . ", ext_seeder = " . $seed . ", ext_leecher = " . $leech . " WHERE info_hash = '" . rtrim($mysqli->real_escape_string($row[0]), ' ') . "'";
				if ($mysqli->query($sql_update)) {
					$seed = $leech = $completed = 0;
				} else {
					die(sprintf("Ошибка при обновлении пиров: %s", $mysqli->error));
				}
			}
		}
	}

	$result->close();
} else {
	die("Вероятно у вас нету ещё раздач, либо ещё не прошло время указанное в константе TIME_UPD");
}

$mysqli->close();
