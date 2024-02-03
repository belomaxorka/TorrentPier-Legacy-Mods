<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang, $userdata, $bb_cfg;

$html_w = $html_f = '';

$sql = DB()->fetch_rowset("SELECT *  FROM " . BB_BLACKJACK . " ORDER BY `bj_date` DESC");
if ($sql) {
	$finish_count = 0;

	foreach ($sql as $row) {
		if ($row['bj_plstat'] == 'waiting' && !($row['bj_TookGame'] && !$row['bj_winner'])) {
			$self = ($row['bj_placeholder'] == $userdata['username'] || $row['bj_gamer'] ? "disabled" : "");
			$html_w .= "<tr><td class=\"row1\" width=\"15%\" align=\"center\">" . $row["bj_StartGame"] . "</td>\n
							<td class=\"row1\" width=\"20%\" align=\"center\">" . bb_date($row["bj_date"]) . "</td>\n
							<td class=\"row1\" width=\"15%\" align=\"center\">" . (($row['bj_TookGame']) ?: '--') . "</td>\n
							<td class=\"row1\" width=\"40%\" align=\"center\">Ставка:&nbsp;<input type=\"button\" style=\"cursor: pointer !important; width: 70px; height: 18px; background-color: #" . $bb_cfg['bj_colors'][$row['bj_bet']] . "; color: #FFFFFF; font-weight: normal; border: 1px solid white\" $self value='" . $row["bj_bet"] . "' onclick=\"window.open('blackjack.php?takegame=$row[bj_id]', '', 'height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no'); return false;\"></td>\n";
			$html_w .= "</tr>\n";
			unset($self);
		}
		if ($row['bj_plstat'] == 'finished' && !($row['bj_TookGame'] && !$row['bj_winner'])) {
			if ($bb_cfg['max_finish_show'] && ($finish_count > $bb_cfg['max_finish_show'])) {
				break;
			}

			$bgcolor = ($userdata['username'] == $row['bj_gamer'] || $userdata['username'] == $row['bj_placeholder'] ? 'style="background-color: #E8DDDD;"' : '');
			$self = ($row['bj_placeholder'] == $userdata['username'] || $row['bj_gamer'] ? "disabled" : "");

			$html_f .= "<tr><td class=\"row1 gen\" $bgcolor width=\"15%\" align=\"center\">" . $row['bj_StartGame'] . "</td>\n
							<td class=\"row1 gen\" $bgcolor width=\"20%\" align=\"center\">" . bb_date($row["bj_date"]) . "</td>\n
							<td class=\"row1 gen\" $bgcolor width=\"15%\" align=\"center\">" . (($row['bj_TookGame']) ?: '--') . "</td>\n
							<td class=\"row1 gen\" $bgcolor width=\"40%\" align=\"center\">Ставка:&nbsp;<input type=\"button\" style=\"cursor: pointer !important; width: 70px; height: 18px; background-color: #" . $bb_cfg['bj_colors'][$row['bj_bet']] . "; color: #FFFFFF; font-weight: normal; border: 1px solid white\" $self value='$row[bj_bet]' onclick=\"window.open('blackjack.php?takegame=$row[bj_id]', '', 'height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no'); return false;\">" . sprintf($lang['BJ']['GAME_WIN'], $row['bj_winner'], $row['bj_points'], $row['bj_gamewithid']) . "</td>\n";
			$html_f .= "</tr>\n";
			$finish_count++;
			unset($self);
			unset($bgcolor);
		}
	}

	$this->response['html'] = $html_w . $html_f;
} else {
	$this->response['html'] = "<tr><td colspan=5 class=\"row1\" width=\"15%\" align=\"center\">" . $lang['BJ']['NO_GAMES'] . "</td></tr>";
}
