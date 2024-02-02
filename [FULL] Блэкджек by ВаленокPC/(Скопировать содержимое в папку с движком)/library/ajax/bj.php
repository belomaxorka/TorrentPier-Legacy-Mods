<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang, $userdata;

$html_w = $html_f = '';

$color = array(
	5 => "74AE04",
	10 => "9E9E9E",
	15 => "0574C9",
	20 => "DB48A2",
	25 => "D8DB04",
	50 => "EFA900",
	100 => "DC0000",
	500 => "FFC0CB",
	1000 => "B0C4DE",
	5000 => "ff0000",
	10000 => "000000"
);

$sql = DB()->fetch_rowset("SELECT *  FROM " . BB_BLACKJACK . " ORDER BY `bj_date` DESC");
if ($sql) {
	foreach ($sql as $row) {
		if ($row['bj_plstat'] === 'waiting' && !($row['bj_TookGame'] && !$row['bj_winner'])) {
			$self = ($row['bj_StartGame'] === $userdata['username'] || $row['bj_TookGame'] ? "disabled" : "");
			$html_w .= "<tr><td class=\"row1\" width=\"15%\" align=\"center\">" . $row["bj_StartGame"] . "</td>\n
            <td class=\"row1\" width=\"20%\" align=\"center\">" . bb_date($row["bj_date"]) . "</td>\n
            <td class=\"row1\" width=\"15%\" align=\"center\">" . (($row['bj_TookGame']) ? $row['bj_TookGame'] : '--') . "</td>\n
            <td class=\"row1\" width=\"40%\" align=\"center\"><input type=button style=\"width: 70px; height: 18px; background: #" . $color[$row['bj_bet']] . "; color: #FFFFFF; font-weight: normal; border: 1px solid white\" $self  value='" . $row["bj_bet"] . "' onClick=\"window.open('blackjack.php?takegame=$row[bj_id]', '', 'height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no'); return false;\"></td>\n";
			$html_w .= "</tr>\n";
			unset($self);
		}
		if ($row['bj_plstat'] === 'finished' && !($row['bj_TookGame'] && !$row['bj_winner'])) {
			unset($self);
			unset($bgcolor);
			unset($pts);

			$bgcolor = ($userdata['username'] === $row['bj_TookGame'] || $userdata['username'] === $row['bj_StartGame'] ? "style=\"background: #E8DDDD;\"" : "");
			$self = ($row['bj_StartGame'] === $userdata['username'] || $row['bj_TookGame'] ? "disabled" : "");

			$html_f .= "<tr><td class=\"row1 gen\" $bgcolor width=15% align=center>" . $row['bj_StartGame'] . "</td>\n
            <td class=\"row1 gen\" $bgcolor width=20% align=center>" . bb_date($row["bj_date"]) . "</td>\n
            <td class=\"row1 gen\" $bgcolor width=15% align=center>" . (($row['bj_TookGame']) ? $row['bj_TookGame'] : '--') . "</td>\n
            <td class=\"row1 gen\" $bgcolor width=40% align=center><input type=button style=\"width: 70px; height: 18px; background: #" . $color[$row['bj_bet']] . "; color: #FFFFFF; font-weight: normal; border: 1px solid white\" $self value='$row[bj_bet]' onClick=\"window.open('blackjack.php?takegame=$row[bj_id]', '', 'height=280, width=620, toolbar=no, status=no, scrollbars=no, resize=no, menubar=no'); return false;\">" . sprintf($lang['GAME_WIN'], $row['bj_winner'], $row['bj_points'], $row['bj_gamewithid']) . "</td>\n";
			$html_f .= "</tr>\n";
		}
	}

	$this->response['html'] = $html_w . $html_f;
} else {
	$this->response['html'] = "<tr><td colspan=5 class=\"row1\" width=\"15%\" align=\"center\">" . $lang['NO_GAMES'] . "</td></tr>";
}
