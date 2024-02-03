<?php
define('IN_FORUM', true);
define('BB_SCRIPT', 'blackjack');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

$user->session_start(array('req_login' => true));

function bj_die($bet, $text)
{
	global $template, $lang;

	$title = ($bet == '') ? $lang['BJ']['BLACKJACK'] : sprintf($lang['BJ']['THE_TITLE'], $bet);

	$template->assign_vars(array(
		'MASSAGES_INFO' => true,
		'PAGE_TITLE' => $title,
		'TITLE' => $title,
		'MASSAGES' => $text,
	));

	print_page('blackjack.tpl', 'simple');
	exit;
}

$stake = request_var('bet', 0);
$games = request_var('game', '');
$takegame = request_var('takegame', '');

if (!$cards = CACHE('bb_cache')->get('bj_cards')) {

	$sql = "SELECT card_id, card_points, card_img FROM " . BB_CARDS;

	$cards = array();
	foreach (DB()->fetch_rowset($sql) as $row) {
		$cards[$row['card_id']] = $row;
	}

	$cards['count'] = DB()->num_rows(DB()->sql_query($sql));
	CACHE('bb_cache')->set('bj_cards', $cards, 1200);
}

if ($stake || $games || is_numeric($takegame)) {
	$winorlose = '';
	cache_rm_user_sessions($userdata['user_id']);

	if ($games == 'start' || $takegame) {
		$cardid = rand(1, $cards['count']);
		if ($games == 'start') {
			$numbets = DB()->num_rows(DB()->sql_query("SELECT bj_id FROM " . BB_BLACKJACK . " WHERE bj_placeholder = '" . $userdata['username'] . "' AND bj_plstat = 'waiting'"));

			if ($userdata["user_tokens"] <= $stake) {
				bj_die($stake, $lang['BJ']['NOT_TOKENS']);
			}
			if ($numbets >= $bb_cfg['max_open_games']) bj_die($stake, sprintf($lang['BJ']['MAX_OPEN_GAMES'], $bb_cfg['max_open_games']));

			DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens - $stake WHERE user_id =" . $userdata['user_id']);
			DB()->sql_query("INSERT INTO " . BB_BLACKJACK . " (bj_placeholder, bj_points, bj_plstat, bj_bet, bj_cards, bj_date) values('" . $userdata['username'] . "', " . $cards[$cardid]['card_points'] . ", 'playing', " . $stake . ", " . $cardid . ", " . TIMENOW . ")");
			$id = DB()->sql_nextid();
		}

		if (is_numeric($takegame)) {
			$gid = $takegame;
			$sql = "SELECT bj_bet, bj_gamer, bj_placeholder FROM " . BB_BLACKJACK . " WHERE bj_id =" . $gid;
			if ($row = DB()->fetch_row($sql)) {
				if ($userdata["user_tokens"] <= $row['bj_bet']) {
					bj_die($row['bj_bet'], $lang['BJ']['NOT_TOKENS']);
				}

				if ($row['bj_gamer']) {
					$template->assign_vars(array('JS_ON' => true));
				}

				if ($row['bj_placeholder'] == $userdata['username']) {
					bj_die($row['bj_bet'], $lang['BJ']['THE_A_GAMES']);
				}

				DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens - " . $row['bj_bet'] . " WHERE user_id = " . $userdata['user_id']);
				DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_gamer = '" . $userdata['username'] . "' WHERE bj_id =" . $gid);
				DB()->query("INSERT INTO " . BB_BLACKJACK . " (bj_placeholder, bj_points, bj_plstat, bj_bet, bj_cards, bj_date, bj_gamewithid) VALUES('" . $userdata['username'] . "', " . $cards[$cardid]['card_points'] . ", 'playing', " . $row['bj_bet'] . ", " . $cardid . ",  " . TIMENOW . ", " . $gid . ")");

				$id = DB()->sql_nextid();

			} else {
				bb_die($lang['BJ']['GAME_NOT_FOUND']);
			}
		}

		$template->assign_vars(array(
			'MASSAGES_START' => true,
			'PAGE_TITLE' => sprintf($lang['BJ']['THE_TITLE'], $stake),
			'IMG_CARDS' => "<img src=styles/images/cards/" . $cards[$cardid]['card_img'] . " border=0>",
			'CARD_POINTS' => $cards[$cardid]['card_points'],
			'ID_GAMES' => $id,
		));
	} elseif ($games == 'cont') {
		$id = request_var('id', 0);
		$playerarr = DB()->sql_fetchrow(DB()->sql_query("SELECT * from " . BB_BLACKJACK . " where bj_id= " . $id));

		if ($playerarr["bj_plstat"] == 'waiting') {
			bj_die($playerarr['bj_bet'], $lang['BJ']['GAME_IS_PLAYED']);
		}

		$showcards = '';
		$usedcards = explode(':', $playerarr["bj_cards"]);
		$arr = array();

		foreach ($usedcards as $array_list) {
			$arr[] = $array_list;
		}

		foreach ($arr as $card_id) {
			$showcards .= "<img src=styles/images/cards/" . $cards[$card_id]["card_img"] . " border=0> ";
		}
		$cardid = rand(1, $cards['count']);

		while (in_array($cardid, $arr)) {
			$cardid = rand(1, $cards['count']);
		}

		$showcards .= "<img src=styles/images/cards/" . $cards[$cardid]['card_img'] . " border=0>";
		$points = $playerarr['bj_points'] + $cards[$cardid]['card_points'];

		DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_points = bj_points + " . $cards[$cardid]['card_points'] . ", bj_cards='" . $playerarr['bj_cards'] . ":" . $cardid . "' WHERE bj_id = " . $id);
		if ($points == 21) {
			DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_plstat = 'waiting', bj_date = " . TIMENOW . " WHERE  bj_id = " . $id);

			$check = DB()->sql_fetchrow(DB()->sql_query("SELECT bj_gamewithid FROM " . BB_BLACKJACK . " WHERE bj_id=" . $id));
			if ($check['bj_gamewithid']) {
				$a = DB()->sql_fetchrow(DB()->sql_query("SELECT * FROM " . BB_BLACKJACK . " WHERE bj_id = " . $check['bj_gamewithid']));

				if ($a['bj_points'] != 21) {
					$winorlose = sprintf($lang['BJ']['YOU_WON'], $playerarr['bj_points'], $a['bj_points']);

					DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . ($a['bj_bet'] * 2) . " WHERE user_id = " . $userdata['user_id']);
					DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $userdata['username'] . "', bj_gamewithid = " . $points . ", bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $check['bj_gamewithid']);
				} else {
					$winorlose = sprintf($lang['BJ']['NOBODY_WON'], $points, $a['bj_points']);

					DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $lang['BJ']['DRAW'] . "', bj_gamewithid = " . $points . ", bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $check['bj_gamewithid']);
					DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . $a['bj_bet'] . " WHERE user_id = " . $userdata['user_id']);
					DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . $a['bj_bet'] . " WHERE username = '" . $a['bj_placeholder'] . "'");
				}

				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $id);
				bj_die($a['bj_bet'], $winorlose);
			} else {
				bj_die($playerarr['bj_bet'], $lang['BJ'][21]);
			}
		} elseif ($points > 21) {
			DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_plstat = 'waiting', bj_date = " . TIMENOW . " WHERE  bj_id = " . $id);

			$check = DB()->sql_fetchrow(DB()->sql_query("SELECT bj_gamewithid FROM " . BB_BLACKJACK . " WHERE bj_id =" . $id));
			if ($check['bj_gamewithid']) {
				$a = DB()->sql_fetchrow(DB()->sql_query("SELECT * FROM " . BB_BLACKJACK . " WHERE bj_id =" . $check['bj_gamewithid']));

				if (($a['bj_points'] >= $points || $points >= $a['bj_points']) && $a['bj_points'] > 21) {
					$winorlose = sprintf($lang['BJ']['NOBODY_WON'], $points, $a['bj_points']);
					DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $lang['BJ']['ROBIN'] . "', bj_gamewithid = $points, bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $check['bj_gamewithid']);
				} elseif ($a['bj_points'] < $points && $a['bj_points'] <= 21) {
					$winorlose = sprintf($lang['BJ']['YOU_LOST'], $points, $a['bj_points']);

					DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . ($a['bj_bet'] * 2) . " WHERE username = '" . $a['bj_placeholder'] . "'");
					if ($a['bj_placeholder']) {
						DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $a['bj_placeholder'] . "', bj_gamewithid = $points, bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $check['bj_gamewithid']);
					}
				}

				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $id);
				bj_die($a['bj_bet'], $winorlose);
			} else {
				bj_die($playerarr['bj_bet'], sprintf($lang['BJ']['BUST'], $points));
			}
		} else {
			$template->assign_vars(array(
				'MASSAGES_START' => true,
				'STOP' => true,
				'PAGE_TITLE' => sprintf($lang['BJ']['THE_TITLE'], $playerarr['bj_bet']),
				'IMG_CARDS' => $showcards,
				'CARD_POINTS' => $points,
				'ID_GAMES' => $id,
			));
		}
	} elseif ($games == 'stop') {
		$id = request_var('id', 0);
		$playerarr = DB()->sql_fetchrow(DB()->sql_query("SELECT * FROM " . BB_BLACKJACK . " WHERE bj_id =" . $id));

		if ($playerarr['bj_plstat'] == 'waiting' || !$playerarr) {
			bj_die($playerarr['bj_bet'], $lang['BJ']['GAME_IS_PLAYED']);
		}

		DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_plstat = 'waiting', bj_date = " . TIMENOW . "  WHERE  bj_id = " . $id);
		if ($playerarr['bj_gamewithid']) {
			$a = DB()->sql_fetchrow(DB()->sql_query("SELECT * FROM " . BB_BLACKJACK . " WHERE bj_id =" . $playerarr['bj_gamewithid']));
			if ($a['bj_points'] == $playerarr['bj_points'] && $a['bj_points'] < 22 && $playerarr['bj_points'] < 22) {
				$winorlose = $lang['BJ']['NOT_WIN'];

				DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . $a['bj_bet'] . " WHERE user_id = " . $userdata['user_id']);
				DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . $a['bj_bet'] . " WHERE username = '" . $a['placeholder'] . "'");
				DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $lang['BJ']['DRAW'] . "', bj_gamewithid = " . $playerarr['bj_points'] . ", bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $playerarr['bj_gamewithid']);
				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $id);
			} elseif ($playerarr['bj_points'] < $a['bj_points'] && $a['bj_points'] > 21 || $playerarr['bj_points'] > $a['bj_points'] && $playerarr['bj_points'] < 22) {
				$winorlose = sprintf($lang['BJ']['YOU_WON'], $playerarr['bj_points'], $a['bj_points']);

				DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . ($a['bj_bet'] * 2) . " WHERE user_id = " . $userdata['user_id']);

				if ($a['bj_placeholder']) {
					DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $userdata['username'] . "', bj_gamewithid = " . $playerarr['bj_points'] . ", bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $playerarr['bj_gamewithid']);
				}

				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $id);
			} elseif ($playerarr['bj_points'] < $a['bj_points'] && $a['bj_points'] <= 21) {
				$winorlose = sprintf($lang['BJ']['YOU_LOST'], $playerarr['bj_points'], $a['bj_points']);

				DB()->query("UPDATE " . BB_USERS . " SET user_tokens = user_tokens + " . ($a['bj_bet'] * 2) . " WHERE username = '" . $a['bj_placeholder'] . "'");
				DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $a['bj_placeholder'] . "', bj_gamewithid = " . $playerarr['bj_points'] . ", bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $playerarr['bj_gamewithid']);
				DB()->query("DELETE FROM " . BB_BLACKJACK . " WHERE bj_id = " . $id);
			} elseif ($a["bj_points"] > 21 && $playerarr["bj_points"] > 21) {
				$winorlose = sprintf($lang['BJ']['NOBODY_WON'], $a["bj_points"], $playerarr['bj_points']);

				DB()->query("UPDATE " . BB_BLACKJACK . " SET bj_winner = '" . $lang['BJ']['ROBIN'] . "', bj_gamewithid = $points, bj_date = " . TIMENOW . ", bj_plstat = 'finished' WHERE  bj_id = " . $check['bj_gamewithid']);
			}

			bj_die($playerarr['bj_bet'], $winorlose);
		} else {
			$template->assign_vars(array(
				'JS_ON' => true,
			));
		}
	}

	print_page('blackjack.tpl', 'simple');
} else {
	$sql = DB()->fetch_rowset("SELECT * FROM " . BB_BLACKJACK . " ORDER BY `bj_date` DESC");
	$finish_count = 0;

	foreach ($sql as $arr) {
		if ($arr['bj_plstat'] == 'waiting') {
			$self = ($arr['bj_placeholder'] == $userdata['username'] || $arr['bj_gamer'] ? "disabled" : "");

			$template->assign_block_vars('waiting', array(
				'W_PLAY' => ($arr['bj_gamer'] && !$arr['bj_winner']) ? $lang['BJ']['PLAY'] : '',
				'PLACEHOLDER' => profile_url(get_userdata($arr['bj_placeholder'], true)),
				'GAMER' => !empty($arr['bj_gamer']) ? profile_url(get_userdata($arr['bj_gamer'], true)) : '--',
				'DATA_GAME' => bb_date($arr['bj_date']),
				'COLOR_BET' => $bb_cfg['bj_colors'][$arr['bj_bet']],
				'BETS' => $arr['bj_bet'],
				'GAME_ID' => $arr['bj_id'],
				'SELF' => $self,
			));
		}

		if ($arr['bj_plstat'] == 'finished') {
			if ($bb_cfg['max_finish_show'] && ($finish_count > $bb_cfg['max_finish_show'])) {
				break;
			}

			$bgcolor = ($userdata['username'] == $arr['bj_gamer'] || $userdata['username'] == $arr['bj_placeholder'] ? 'style="background-color: #E8DDDD;"' : '');
			$self = ($arr['bj_placeholder'] == $userdata['username'] || $arr['bj_gamer'] ? "disabled" : "");

			$winner = $pts = '';
			if ($arr['bj_gamer'] && !isset($arr['bj_winner']))
				$winner = "&nbsp;->&nbsp;<b>???????</b>";
			if ($arr['bj_winner']) {
				if (get_user_id($arr['bj_winner'])) {
					$winner = "&nbsp;->&nbsp;<b>" . profile_url(get_userdata($arr['bj_winner'], true)) . "</b>";
				} else {
					$winner = "&nbsp;->&nbsp;<b>" . $arr['bj_winner'] . "</b>";
				}
				$pts = $arr['bj_points'] . " | " . $arr['bj_gamewithid'];
			}

			$template->assign_block_vars('finished', array(
				'WINNER' => $winner,
				'PLACEHOLDER' => profile_url(get_userdata($arr['bj_placeholder'], true)),
				'GAMER' => !empty($arr['bj_gamer']) ? profile_url(get_userdata($arr['bj_gamer'], true)) : '--',
				'DATA_GAME' => bb_date($arr['bj_date']),
				'COLOR_BET' => $bb_cfg['bj_colors'][$arr['bj_bet']],
				'BETS' => $arr['bj_bet'],
				'GAME_ID' => $arr['bj_id'],
				'SELF' => $self,
				'GAME_WIN' => $pts,
				'BGCOLOR' => $bgcolor,
			));

			$finish_count++;
		}
	}

	foreach ($bb_cfg['bj_colors'] as $val => $color) {
		$template->assign_block_vars('bet', array(
			'BET_COLOR' => $color,
			'BET_GAMES' => $val,
		));
	}

	$template->assign_vars(array(
		'GAMES_VIEW' => true,
		'PAGE_TITLE' => $lang['BJ']['BLACKJACK'],
		'BJ_GAME' => $lang['BJ']['BLACKJACK'],
		'NO_GAMES' => !$sql,
		'TOKENS' => $userdata['user_tokens']
	));

	print_page('blackjack.tpl');
}
