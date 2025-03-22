<?php

define('BB_SCRIPT', 'portal');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

$start = abs(intval(request_var('start', 0)));
$mode = (string)request_var('mode', '');
$search = (string)request_var('search', 'search');
$page_cfg['include_bbcode_js'] = true;

// Start session management
$user->session_start();

// Set tpl vars for bt_userdata
if ($bb_cfg['bt_show_dl_stat_on_index'] && !IS_GUEST) {
	show_bt_userdata($userdata['user_id']);
}

// Statistics
if (!$stats = $datastore->get('stats')) {
	$datastore->update('stats');
	$stats = $datastore->get('stats');
}

$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

// Latest news
if ($bb_cfg['show_latest_news']) {
	if (!$latest_news = $datastore->get('latest_news')) {
		$datastore->update('latest_news');
		$latest_news = $datastore->get('latest_news');
	}

	$template->assign_vars(array(
		'SHOW_LATEST_NEWS' => true,
	));

	foreach ($latest_news as $news) {
		$template->assign_block_vars('news', array(
			'NEWS_TOPIC_ID' => $news['topic_id'],
			'NEWS_TITLE' => $news['topic_title'],
			'NEWS_TIME' => bb_date($news['topic_time'], 'd-M', 'false'),
			'NEWS_IS_NEW' => is_unread($news['topic_time'], $news['topic_id'], $news['forum_id']),
		));
	}
}

// Network news
if ($bb_cfg['show_network_news']) {
	if (!$network_news = $datastore->get('network_news')) {
		$datastore->update('network_news');
		$network_news = $datastore->get('network_news');
	}

	$template->assign_vars(array(
		'SHOW_NETWORK_NEWS' => true,
	));

	foreach ($network_news as $net) {
		$template->assign_block_vars('net', array(
			'NEWS_TOPIC_ID' => $net['topic_id'],
			'NEWS_TITLE' => str_short($net['topic_title'], $bb_cfg['max_net_title']),
			'NEWS_TIME' => bb_date($net['topic_time'], 'd-M', 'false'),
			'NEWS_IS_NEW' => is_unread($net['topic_time'], $net['topic_id'], $net['forum_id']),
		));
	}
}

// Режима поиска
switch ($mode) {
	case'user': // поиск по пользователям
		$and = "AND u.username LIKE '%" . $search . "%'";
		break;

	case'title': // поиск по названию
		$and = "AND t.topic_title LIKE '%" . $search . "%'";
		break;

	case'desc': // поиск по описанию
		$and = "AND pt.post_html LIKE '%" . $search . "%'";
		break;

	case'c': // поиск по категориям
		$and = "AND f.cat_id = $search";
		break;

	case'free': // вывод золотых/серебрянных раздач
		$and = "AND tor.tor_type IN(" . TOR_TYPE_GOLD . "," . TOR_TYPE_SILVER . ")";
		break;

	default:
		$and = '';
		break;
}
// Завершили выбор режима посика

// Вывод релизов в зависимости от прав доступа
$excluded_forums_csv = $user->get_excluded_forums(AUTH_VIEW);
$not_auth_forums_sql = ($excluded_forums_csv) ? "AND f.forum_id NOT IN($excluded_forums_csv)" : '';
// Завершили вывод релизов в зависимости от прав доступа

// CACHE('bb_cache')->rm('portal_count_'.'*');
// Начинаем подсчет для паганицы

if (!bf($userdata['user_opt'], 'user_opt', 'user_portal')) {
	// ------ Ленточный интерфейс портала ------
	if (!$row = CACHE('portal')->get('portal_count_' . $mode . $search)) {
		$row = DB()->fetch_row("SELECT COUNT(t.topic_id) AS topic_count
			FROM " . BB_TOPICS . " AS t
			JOIN " . BB_USERS . " AS u ON u.user_id = t.topic_poster
			JOIN " . BB_POSTS_HTML . " AS pt ON pt.post_id = t.topic_first_post_id
			LEFT JOIN " . BB_BT_TORRENTS . " AS tor ON tor.topic_id = t.topic_id
			JOIN " . BB_FORUMS . " AS f ON f.forum_id = t.forum_id
			WHERE t.topic_dl_type = 1
			$not_auth_forums_sql
			$and"
		);

		CACHE('portal')->set('portal_count_' . $mode . $search, $row, 3600);
	}

	// Завершили подсчёт паганица
	$topic_count = $row['topic_count'] ?: 0; // Проверка на пустое значение.
	$per_page = '5'; // колличество релизов. Ленточный - 5, плиточный - 21.
	if ($topic_count) {
		if (!$row = CACHE('portal')->get('portal_desc_' . $start . $mode . $search)) {
			$row = DB()->fetch_rowset("SELECT t.topic_id, t.topic_title, t.topic_time, t.topic_replies, t.topic_dl_type, pt.post_id, pt.post_html, u.user_id, u.username, u.user_opt, u.user_rank, tor.attach_id, tor.size, tor.tor_status, tor.tor_type, f.cat_id
					FROM " . BB_TOPICS . " AS t
					JOIN " . BB_USERS . " AS u ON u.user_id = t.topic_poster
					JOIN " . BB_POSTS_HTML . " AS pt ON pt.post_id = t.topic_first_post_id
					LEFT JOIN " . BB_BT_TORRENTS . " AS tor ON tor.topic_id = t.topic_id
					JOIN " . BB_FORUMS . " AS f ON f.forum_id = t.forum_id
					WHERE t.topic_dl_type = 1
					$not_auth_forums_sql
					$and
					ORDER BY tor.reg_time DESC
					LIMIT $start, $per_page"
			);
			CACHE('portal')->set('portal_desc_' . $start . $mode . $search, $row, 3600);
		}

		foreach ($row as $rows) {
			// регулярка для парса картинок
			preg_match_all('/<var class="postImg postImgAligned img-(.*?)" title="(.*?)">&#10;<\/var>/', $rows['post_html'], $poster, PREG_SET_ORDER);
			preg_match_all('/<var class="postImg" title="(.*?)">&#10;<\/var>/', $rows['post_html'], $poster2, PREG_SET_ORDER);
			preg_match_all('/<a href="(.*?)" .*? class="highslide">/', $rows['post_html'], $poster3, PREG_SET_ORDER);

			if (isset($poster[0][2])) {
				$url = '<img border="0" src="' . $poster[0][2] . '" width="256" align="left" style="margin-bottom:8px;margin-right:8px;" />';
			} elseif (isset($poster2[0][1])) {
				$url = '<img border="0" src="' . $poster2[0][1] . '" width="256" align="left" style="margin-bottom:8px;margin-right:8px;" />';
			} elseif (isset($poster3[0][1])) {
				$url = '<img border="0" src="' . $poster3[0][1] . '" width="256" align="left" style="margin-bottom:8px;margin-right:8px;" />';
			} else {
				$url = '<img border="0" src="/images/no-poster.png" width="256" align="left" style="margin-bottom:8px;margin-right:8px;" />';
			}

			// отпарсили
			$title_post = $rows['post_html'];
			$title_post = preg_replace('/<var class="postImg postImgAligned img-(.*?)" title="(.*?)">&#10;<\/var>/', '', $title_post);
			$title_post = preg_replace('/<var class="postImg" title="(.*?)">&#10;<\/var>/', '', $title_post);
			$title_post = preg_replace('/<a href="(.*?)" .*? class="highslide">.*?<\/a>/', '', $title_post);
			$title_post = preg_replace('/<div class="sp-wrap">([\s\S]*?)<([^<]*?)\/div>/', "", $title_post);
			$title_post = str_replace('<span class="post-hr">-</span><span class="post-hr">-</span><span class="post-hr">-</span><span class="post-hr">-</span><span class="post-hr">-</span>', '<span class="post-hr">-</span>', $title_post);
			$title_post = str_replace('<span class="post-hr">-</span><span class="post-hr">-</span><span class="post-hr">-</span>', '<span class="post-hr">-</span>', $title_post);
			$title_post = str_replace('<span class="post-hr">-</span><span class="post-hr">-</span>', '<span class="post-hr">-</span>', $title_post);

			$template->assign_block_vars('topics', array(
				'TOPIC_TITLE' => $rows['topic_title'],
				'TOPIC_ID' => $rows['topic_id'],
				'U_VIEW_TOPIC' => TOPIC_URL . $rows['topic_id'],
				'POSTER' => profile_url(array('username' => $rows['username'], 'user_id' => $rows['user_id'], 'user_rank' => $rows['user_rank'])),
				'SIZE' => humn_size($rows['size']),
				'TOPIC_POSTER_ID' => $rows['user_id'],
				'TIME' => bb_date($rows['topic_time'], $bb_cfg['last_post_date_format'], 'false'),
				'REPLIES' => $rows['topic_replies'],
				'DESCRIPTION' => des_short($title_post, 1500),
				'ATTACH_ID' => $rows['topic_dl_type'] ? $rows['attach_id'] : false,
				'STATUS' => $rows['topic_dl_type'] ? $rows['tor_status'] : false,
				'TOR_FROZEN' => (!empty($rows['attach_id']) && $rows['topic_dl_type']) ? isset($bb_cfg['tor_frozen'][$rows['tor_status']]) : false,
				'TOR_STATUS_ICON' => (!empty($rows['attach_id']) && $rows['topic_dl_type']) ? $bb_cfg['tor_icons'][$rows['tor_status']] : false,
				'TOR_STATUS_TEXT' => (!empty($rows['attach_id']) && $rows['topic_dl_type']) ? $lang['TOR_STATUS_NAME'][$rows['tor_status']] : false,
				// 'TOR_TYPE'			=> is_gold($row['tor_type']),
				'POST_ID' => $rows['post_id'],
				'POSTER_IMG' => $url,
				'PORTAL_POST' => (bool)$rows['topic_dl_type'],
			));
		}
	}
} elseif (bf($userdata['user_opt'], 'user_opt', 'user_portal')) {
	// ------ Плиточный интерфейс портала ------
	// Начинаем подсчет для паганицы
	if (!$row = CACHE('portal')->get('portal_tile_count_' . $mode . $search)) {
		$row = DB()->fetch_row("SELECT COUNT(t.topic_id) AS topic_count
			FROM " . BB_TOPICS . " AS t
			JOIN " . BB_USERS . " AS u ON u.user_id = t.topic_poster
			JOIN " . BB_POSTS_HTML . " AS pt ON pt.post_id = t.topic_first_post_id
			LEFT JOIN " . BB_BT_TORRENTS . " AS tor ON tor.topic_id = t.topic_id
			JOIN " . BB_FORUMS . " AS f ON f.forum_id = t.forum_id
			WHERE t.topic_dl_type = 1
			$not_auth_forums_sql
			$and"
		);

		CACHE('portal')->set('portal_tile_count_' . $mode . $search, $row, 3600);
	}
	// Завершили подсчёт паганица

	$topic_count = $row['topic_count'] ?: 0; // Проверка на пустое значение.
	$per_page = '21'; // колличество релизов. Ленточный - 5, плиточный - 21.
	if ($topic_count) {
		if (!$row = CACHE('portal')->get('portal_tile_count' . $start . $mode . $search)) {
			$row = DB()->fetch_rowset("SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_replies, pt.post_id, pt.post_html, u.username, tor.attach_id, tor.size, tor.tor_status, tor.tor_type, ts.seeders, ts.leechers, ts.speed_up, ts.speed_down, f.cat_id
				FROM " . BB_TOPICS . " AS t
				LEFT JOIN " . BB_USERS . " AS u ON u.user_id = t.topic_poster
				LEFT JOIN " . BB_POSTS_HTML . " AS pt ON pt.post_id = t.topic_first_post_id
				JOIN " . BB_BT_TORRENTS . " AS tor ON tor.topic_id = t.topic_id
				JOIN " . BB_FORUMS . " AS f ON f.forum_id = t.forum_id
				LEFT JOIN " . BB_BT_TRACKER_SNAP . " AS ts ON ts.topic_id = t.topic_id
				WHERE t.topic_dl_type = 1
				$not_auth_forums_sql
				$and
				ORDER BY tor.reg_time DESC
				LIMIT $start, $per_page"
			);

			CACHE('portal')->set('portal_tile_count' . $start . $mode . $search, $row, 3600);
		}

		$data = array();
		foreach ($row as $rowdata) {
			$data[] = $rowdata;
		}

		if (!$data) {
			$template->assign_block_vars('no_topics', array());
		}

		for ($j = 0; $j < count($data); $j += 3) {
			$template->assign_block_vars('data', array());

			for ($i = $j; $i < ($j + 3); $i++) {
				if ($i >= count($data)) {
					$template->assign_block_vars('data.not', array());
					continue;
				}

				// Начинаем парсить постеры
				preg_match_all('/<var class="postImg postImgAligned img-(.*?)" title="(.*?)">&#10;<\/var>/', $data[$i]['post_html'], $poster, PREG_SET_ORDER);
				preg_match_all('/<var class="postImg" title="(.*?)">&#10;<\/var>/', $data[$i]['post_html'], $poster2, PREG_SET_ORDER);
				preg_match_all('/<a href="(.*?)" .*? class="highslide">/', $data[$i]['post_html'], $poster3, PREG_SET_ORDER);

				if (isset($poster[0][2])) {
					$url = $poster[0][2];
				} elseif (isset($poster2[0][1])) {
					$url = $poster2[0][1];
				} elseif (isset($poster3[0][1])) {
					$url = $poster3[0][1];
				} else {
					$url = './images/no-poster.png';
				}

				// Завершили парсить постеры
				$template->assign_block_vars('data.topics', array(
					'TOR_FROZEN' => isset($bb_cfg['tor_frozen'][$data[$i]['tor_status']]),
					'TOR_ICONS' => $bb_cfg['tor_icons'][$data[$i]['tor_status']] ?: '',
					'TOR_STATUS' => $lang['TOR_STATUS_NAME'][$data[$i]['tor_status']] ?: '',
					'SPEED_UP' => $data[$i]['speed_up'] ? humn_size(($data[$i]['speed_up']), 0, 'KB') . '/s' : '0 KB/s',
					'SPEED_DOWN' => $data[$i]['speed_down'] ? humn_size(($data[$i]['speed_down']), 0, 'KB') . '/s' : '0 KB/s',
					'TOPIC_ID' => TOPIC_URL . $data[$i]['topic_id'],
					'TOPIC_REPLIES' => $data[$i]['topic_replies'],
					'ATTACH_ID' => $data[$i]['attach_id'],
					'SIZE' => humn_size($data[$i]['size']),
					'LEECHERS' => $data[$i]['leechers'],
					'SEEDERS' => $data[$i]['seeders'],
					'TOPIC_TITLE' => str_short($data[$i]['topic_title'], 40),
					'IS_GOLD' => is_gold($data[$i]['tor_type']),
					'POSTER' => $url,
					'POST_ID' => $data[$i]['post_id'],
				));
			}
		}
	}
} else {
	$template->assign_block_vars('no_topics', array(
		'DESCRIPTION' => $lang['NO_MATCH'],
	));
}

// Начинаем генерировать пагинцу)
$url = $search ? "portal.php?mode=$mode&search=$search" : "portal.php";
generate_pagination($url, $topic_count, $per_page, $start);
// Завершаем генерировать пагинцу))

// Вывод прочей информации в блоках
$template->assign_vars(array(
	'PORTAL' => true,
	'L_STATISTICS' => $lang['STATISTICS'],
	'L_DESCRIPTION' => $lang['DESCRIPTION'],
	'TOTAL_TOPICS' => $stats['topiccount'],
	'TOTAL_POSTS' => $stats['postcount'],
	'TOTAL_USERS' => $stats['usercount'],
	'UNSELECT' => $stats['unselect'],
	'MALE' => $stats['male'],
	'FEMALE' => $stats['female'],
	'NEWEST_USER' => profile_url($stats['newestuser']),
	'RECORD_USERS' => $bb_cfg['record_online_users'],
	'RELES' => $stats['torrentcount'],
	'ALL_SIZE' => humn_size($stats['size']),
	'ALL_PEERS' => $stats['peers'],
	'ALL_SEEDERS' => $stats['seeders'],
	'ALL_LEECHERS' => $stats['leechers'],
	'SPEED' => humn_size($stats['speed']) . '/s',
));

print_page('portal.tpl');
