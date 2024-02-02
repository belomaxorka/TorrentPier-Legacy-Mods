<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$sql = DB()->fetch_rowset("SELECT * FROM " . BB_BLACKJACK . " ORDER BY `bj_date` DESC");

foreach ($sql as $row) {
	switch ($row['bj_plstat']) {
		case 'playing':
			if (!$row['bj_gamer'] && !$row['bj_winner'] && ($row['bj_date'] < (TIMENOW - 60 * 15))) {
				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $row['bj_id']);
			}
			break;
		case 'waiting':
			if ($row['bj_gamer'] && !$row['bj_winner'] && ($row['bj_date'] < (TIMENOW - 60 * 15))) {

				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $row['bj_id']);
			}
			break;
		case 'finished':
			if ($row['bj_gamer'] && $row['bj_winner'] && ($row['bj_date'] < (TIMENOW - 172800 * 2))) {
				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $row['bj_id']);
			}
			break;
	}
}
