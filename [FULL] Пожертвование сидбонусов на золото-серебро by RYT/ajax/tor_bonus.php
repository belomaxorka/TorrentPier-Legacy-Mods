<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang, $bb_cfg, $tr_cfg, $userdata;

if (!$bb_cfg['tor_bonus'] || !$tr_cfg['gold_silver_enabled']) {
	$this->ajax_die($lang['MODULE_OFF']);
}

$mode = (string)$this->request['mode'];

switch ($mode) {

	case 'tor_bonus_info':
		$attach_id = (int)$this->request['attach_id'];

		$sql = DB()->fetch_rowset("SELECT SUM(tb.tor_bonus_silver), SUM(tb.tor_bonus_gold), tb.attach_id, tor.attach_id, tor.tor_type
									FROM bb_bt_tor_bonus tb
									LEFT JOIN bb_bt_torrents tor ON(tor.attach_id = tb.attach_id)
									WHERE tb.attach_id = $attach_id
									LIMIT 1");

		foreach ($sql as $row) {
			if ($row['tor_type'] == 0) {
				$tor_bonus = $row['SUM(tb.tor_bonus_silver)'] ? $row['SUM(tb.tor_bonus_silver)'] : '0.00';
				$ostatok = $bb_cfg['tor_bonus_silver'] - $row['SUM(tb.tor_bonus_silver)'];
			} elseif ($row['tor_type'] == 2) {
				$tor_bonus = $row['SUM(tb.tor_bonus_gold)'] ? $row['SUM(tb.tor_bonus_gold)'] : '0.00';
				$ostatok = $bb_cfg['tor_bonus_gold'] - $row['SUM(tb.tor_bonus_gold)'];
			} elseif ($row['tor_type'] == 1) {
				$tor_bonus = $row['SUM(tb.tor_bonus_gold)'] ? $row['SUM(tb.tor_bonus_gold)'] : '0.00';
				$ostatok = $bb_cfg['tor_bonus_gold'] - $row['SUM(tb.tor_bonus_gold)'];
			}
		}

		$html = '<div class="spacer_10"></div>
				<table class="forumline w95" cellspacing="0" cellpadding="0">
					<tr class="row3 tCenter">
						<td width="25%">' . sprintf($lang['SEED_BONUS_PRESENT'], $tor_bonus) . '</td>
						<td width="25%">' . sprintf($lang['SEED_BONUS_REMAINING'], $ostatok) . '</td>
						<td width="25%">' . sprintf($lang['SEED_BONUS_ME'], $userdata['user_points']) . '</td>
						<td width="25%">' . $lang['TOR_BONUS_NUMBER'] . ': <input id="bonus" type="number" style="width: 70px;" /> <input onclick="return ajax.tor_bonus(\'add\'); return false;" type="button" value="OK" /></td>
					</tr>
				</table>';

		$this->response['html'] = $html;
		break;

	case 'add':
		$attach_id = (int)$this->request['attach_id'];
		$bonus = (string)$this->request['bonus'];

		if (!($bonus == '' || $bonus <= 0)) {
			$user_id = $userdata['user_id'];

			$sql = DB()->fetch_rowset("SELECT tor.tor_type, tor.topic_id, SUM(tb.tor_bonus_silver), SUM(tb.tor_bonus_gold)
										FROM bb_bt_torrents tor
										LEFT JOIN bb_bt_tor_bonus tb ON(tb.attach_id = tor.attach_id)
										WHERE tor.attach_id = $attach_id
										LIMIT 1");

			foreach ($sql as $row) {
				if ($bonus > $userdata['user_points']) $this->ajax_die($lang['ERRORS_NO_BONUS_USER']);
				if ($row['tor_type'] == 1) $this->ajax_die($lang['ERRORS_TOR_TYPE_GOLD']);
				if ($row['tor_type'] == 0 && $row['SUM(tb.tor_bonus_gold)'] >= $bb_cfg['tor_bonus_gold'] || $row['tor_type'] == 0 && $row['SUM(tb.tor_bonus_silver)'] >= $bb_cfg['tor_bonus_silver']) $this->ajax_die($lang['ERRORS_TOR_TUPE_BONUS']);
				if ($row['tor_type'] == 2 && $row['SUM(tb.tor_bonus_gold)'] >= $bb_cfg['tor_bonus_gold']) $this->ajax_die($lang['ERRORS_MAX_TOR_BONUS']);

				if ($row['tor_type'] == 0 && ($row['SUM(tb.tor_bonus_silver)'] + $bonus) < $bb_cfg['tor_bonus_silver']) {
					DB()->query("UPDATE bb_users SET user_points = user_points - $bonus WHERE user_id = $user_id LIMIT 1");
					DB()->query('INSERT INTO bb_bt_tor_bonus (attach_id, user_id, time, tor_bonus_silver) VALUES (' . $attach_id . ', ' . $user_id . ', ' . TIMENOW . ', ' . $bonus . ')');

					$title = $lang['TOR_BONUS_SILVER'];
					$url = (TOPIC_URL . $row['topic_id']);
					cache_rm_user_sessions($user_id);
				} elseif ($row['tor_type'] == 0 && ($row['SUM(tb.tor_bonus_silver)'] + $bonus) >= $bb_cfg['tor_bonus_silver']) {
					$limit = $bb_cfg['tor_bonus_silver'] - $row['SUM(tb.tor_bonus_silver)'];

					DB()->query("UPDATE bb_bt_torrents SET tor_type = 2 WHERE attach_id = $attach_id LIMIT 1");
					DB()->query("UPDATE bb_users SET user_points = user_points - $limit WHERE user_id = $user_id LIMIT 1");
					DB()->query('INSERT INTO bb_bt_tor_bonus (attach_id, user_id, time, tor_bonus_silver) VALUES (' . $attach_id . ', ' . $user_id . ', ' . TIMENOW . ', ' . $limit . ')');

					$title = $lang['TOR_BONUS_SILVER_TO'];
					$url = (TOPIC_URL . $row['topic_id']);
					cache_rm_user_sessions($user_id);
				} elseif ($row['tor_type'] == 2 && ($row['SUM(tb.tor_bonus_gold)'] + $bonus) < $bb_cfg['tor_bonus_gold']) {
					DB()->query("UPDATE bb_users SET user_points = user_points - $bonus WHERE user_id = $user_id LIMIT 1");
					DB()->query('INSERT INTO bb_bt_tor_bonus (attach_id, user_id, time, tor_bonus_gold) VALUES (' . $attach_id . ', ' . $user_id . ', ' . TIMENOW . ', ' . $bonus . ')');

					$title = $lang['TOR_BONUS_GOLD'];
					$url = (TOPIC_URL . $row['topic_id']);
					cache_rm_user_sessions($user_id);
				} elseif ($row['tor_type'] == 2 && ($row['SUM(tb.tor_bonus_gold)'] + $bonus) >= $bb_cfg['tor_bonus_gold']) {
					$limit = $bb_cfg['tor_bonus_gold'] - $row['SUM(tb.tor_bonus_gold)'];

					DB()->query("UPDATE bb_bt_torrents SET tor_type = 1 WHERE attach_id = $attach_id LIMIT 1");
					DB()->query("UPDATE bb_users SET user_points = user_points - $limit WHERE user_id = $user_id LIMIT 1");
					DB()->query('INSERT INTO bb_bt_tor_bonus (attach_id, user_id, time, tor_bonus_gold) VALUES (' . $attach_id . ', ' . $user_id . ', ' . TIMENOW . ', ' . $limit . ')');

					$title = $lang['TOR_BONUS_GOLD_TO'];
					$url = (TOPIC_URL . $row['topic_id']);
					cache_rm_user_sessions($user_id);
				}
			}
		} else $this->ajax_die($lang['ERRORS_TOR_TYPE_TEXT']);

		$this->response['url'] = $url;
		$this->response['title'] = $title;
		break;

	case 'list':
		$attach_id = (int)$this->request['attach_id'];

		$sql = DB()->fetch_rowset("SELECT tb.attach_id, tb.user_id, tb.time, tb.tor_bonus_silver, tb.tor_bonus_gold, u.username, u.user_rank, u.user_id
									FROM bb_bt_tor_bonus tb
									LEFT JOIN bb_users	u ON(u.user_id = tb.user_id)
									WHERE tb.attach_id = $attach_id
									ORDER BY tb.time DESC");

		$user_list = '';

		foreach ($sql as $row) {
			if ($row['tor_bonus_silver'] != 0) {
				$bonus = $row['tor_bonus_silver'];
				$type = TOR_TYPE_SILVER;
			} elseif ($row['tor_bonus_gold'] != 0) {
				$bonus = $row['tor_bonus_gold'];
				$type = TOR_TYPE_GOLD;
			}

			$user_list .= '<tr class="row1 pad_4 med">
					<td><b>' . profile_url(array('username' => $row['username'], 'user_rank' => $row['user_rank'], 'user_id' => $row['user_id'])) . '</b></td>
					<td class="tCenter"><b>' . $bonus . '</b></td>
					<td class="tCenter">' . is_gold($type) . '</td>
					<td class="tCenter">' . bb_date($row['time']) . '</td>
				</tr>';
		}

		$html = '<div class="spacer_10"></div>
			<table class="forumline w95" cellspacing="0" cellpadding="0">
				<tr class="row3 small tCenter">
					<td width="25%">' . $lang['TOR_BONUS_USERS_WHO'] . '</td>
					<td width="25%">' . $lang['TOR_BONUS_USERS_HOW'] . '</td>
					<td width="25%">' . $lang['TOR_BONUS_USERS_WHAT'] . '</td>
					<td width="25%">' . $lang['TOR_BONUS_USERS_TIME'] . '</td>
				</tr>
				' . $user_list . '
			</table>';

		$no_users = '<div class="spacer_10"></div>
			<table class="forumline w95" cellspacing="0" cellpadding="0">
				<tr class="row3 tCenter">
					<td>' . $lang['ERRORS_NOUSERS_TOR_BONUS'] . '</th>
				</tr>
			</table>';

		$this->response['html'] = ($sql) ? $html : $no_users;
		break;

	case 'release_add_list':
		$attach_id = (int)$this->request['attach_id'];

		$html = '<div class="spacer_10"></div>
			<table class="forumline w95" cellspacing="0" cellpadding="0">
				<tbody>
					<tr class="row3 tCenter">
						<td width="25%">' . sprintf($lang['SEED_BONUS_ME'], $userdata['user_points']) . '</td>
						<td width="25%">' . $lang['TOR_BONUS_NUMBER'] . ': <input id="bonus_transfer" type="number" style="width: 70px;" /> <input onclick="return ajax.tor_bonus(\'releaser_bonus_add\'); return false;" type="button" value="OK" /></td>
					</tr>
				</tbody>
			</table>';

		$this->response['html'] = $html;
		break;

	case 'releaser_bonus_add':
		$attach_id = (int)$this->request['attach_id'];
		$bonus = (string)$this->request['bonus_transfer'];

		if (!($bonus == '' || $bonus <= 0)) {
			$user_id = $userdata['user_id'];

			$sql = DB()->fetch_rowset("SELECT tor.poster_id, tor.topic_id, u.user_id, t.topic_title
										FROM " . BB_BT_TORRENTS . " tor
										INNER JOIN " . BB_USERS . " u ON(u.user_id = tor.poster_id)
										INNER JOIN " . BB_TOPICS . " t ON(t.topic_id = tor.topic_id)
										WHERE tor.attach_id = $attach_id
										AND tor.poster_id = u.user_id
										LIMIT 1");

			foreach ($sql as $row) {
				if ($bonus > $userdata['user_points']) $this->ajax_die($lang['ERRORS_NO_BONUS_USER']);
				$poster_id = $row['poster_id'];

				$subject = sprintf($lang['POINTS_SUBJECT'], $row['topic_title']);
				$message = sprintf($lang['POINTS_MESSAGE'], profile_url($userdata), $bonus);
				send_pm($poster_id, $subject, $message, $user_id);

				DB()->query("UPDATE bb_users SET user_points = user_points - $bonus WHERE user_id = $user_id LIMIT 1");
				DB()->query("UPDATE bb_users SET user_points = user_points + $bonus WHERE user_id = $poster_id LIMIT 1");

				$title = $lang['RELEASER_BONUS'];
				$url = (TOPIC_URL . $row['topic_id']);
				cache_rm_user_sessions($user_id);
			}
		} else $this->ajax_die($lang['ERRORS_TOR_TYPE_TEXT']);

		$this->response['url'] = $url;
		$this->response['title'] = $title;
		break;

	default:
		$this->ajax_die('Invalid mode');
}

$this->response['mode'] = $mode;
