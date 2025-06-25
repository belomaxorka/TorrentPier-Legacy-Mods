<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Dredd
 * Date: 02.02.13
 * Time: 16:38
 * To change this template use File | Settings | File Templates.
 */

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata;

if (!$bb_cfg['subs']) {
	$this->ajax_die($lang['MODULE_OFF']);
}

$mode = (string)$this->request['mode'];
$user_id = $this->request['user_id'];

$html = '';
switch ($mode) {
	case 'add':
		if ($userdata['user_id'] == $user_id) {
			$this->ajax_die('Нельзя добавлять себя в подписчики!');
		}

		$sql = DB()->fetch_row("SELECT * FROM bb_subs WHERE subs_id = " . $userdata['user_id'] . " AND user_id = " . $user_id . " LIMIT 1");
		if ($sql) {
			$this->ajax_die('Вы уже подписаны на этого человека');
		}

		DB()->query("INSERT INTO bb_subs(user_id,subs_id) VALUES ('" . $user_id . "', '" . $userdata['user_id'] . "')");

		$count = DB()->fetch_row("SELECT COUNT(subs_id) AS subs FROM bb_subs WHERE user_id = " . $user_id);
		// Отправка сообщения о добавлении в подписчики
		$subject = 'У вас новый подписчик';
		$message = '[font="Georgia"][color=gray][size=16][align=center]Здравствуйте, ' . get_username($user_id) . '![/align][/size][/color][/font]
        [font="Georgia"][color=#006699][size=14][align=right]Пользователь ' . $userdata['username'] . ', добавил вас в подписчики. [hr][/align][/size][/color][/font]
        [align=right][size=14][color=gray][font="Georgia"]На это сообщение не нужно отвечать![/font][/color][/size][/align]';
		send_pm($user_id, $subject, $message, BOT_UID);
		// [END]

		// Чистим кеш
		CACHE('bb_cache')->rm('list_subs_[' . $user_id . ']');

		// Отправляем данные в шаблон
		$this->response['count'] = $count['subs'];
		$this->response['user_id'] = $user_id;
		break;

	case 'remove':
		if ($userdata['user_id'] == $user_id) {
			$this->ajax_die('Нельзя удалять себя из подписчиков!');
		}

		$sql = DB()->fetch_row("SELECT * FROM bb_subs WHERE subs_id = " . $userdata['user_id'] . " AND user_id = " . $user_id . " LIMIT 1");
		if (!$sql) {
			$this->ajax_die('Вы уже отписались от этого человека');
		}

		DB()->query("DELETE FROM bb_subs WHERE subs_id = " . $userdata['user_id'] . " AND user_id = $user_id");

		$count = DB()->fetch_row("SELECT COUNT(subs_id) AS subs FROM bb_subs WHERE user_id = " . $user_id);
		// Отправка сообщения о добавлении в подписчики
		$subject = 'Вы "потеряли" подписчика';
		$message = '[font="Georgia"][color=gray][size=16][align=center]Здравствуйте, ' . get_username($user_id) . '![/align][/size][/color][/font]
        [font="Georgia"][color=#006699][size=14][align=right]Пользователь ' . $userdata['username'] . ', отписался от вас. [hr][/align][/size][/color][/font]
        [align=right][size=14][color=gray][font="Georgia"]На это сообщение не нужно отвечать![/font][/color][/size][/align]';
		send_pm($user_id, $subject, $message, BOT_UID);
		// [END]

		// Чистим кеш
		CACHE('bb_cache')->rm('list_subs_[' . $user_id . ']');

		// Отправляем данные в шаблон
		$this->response['count'] = $count['subs'];
		$this->response['user_id'] = $user_id;
		break;

	case 'list_subs':
		if (!$sql = CACHE('bb_cache')->get('list_subs_[' . $user_id . ']', 300)) {
			$sql = DB()->fetch_rowset("SELECT s.*, u.username, u.user_id, u.avatar_ext_id, u.user_opt, u.user_points, u.user_rank, u.user_gender, u.user_posts
            FROM bb_subs s,
            " . BB_USERS . " u
            WHERE s.user_id = " . $user_id . "
            AND s.subs_id = u.user_id");
			CACHE('bb_cache')->set('list_subs_[' . $user_id . ']', $sql);
		}

		$html = '<div class="xenOverlay" style="position: fixed; z-index: 9999; top: 92.30000000000001px; left: 286.5px; display: block;"><div class="section"><h2 class="heading h1">Подписчики пользователя ' . get_username($user_id) . '</h2>';
		$html .= '<ol class="overlayScroll">';
		$html .= (!$sql) ? '<li class="primaryContent memberListItem"><div class="member"><div class="userInfo"><div class="userBlurb dimmed"><span class="userTitle">У ' . get_username($user_id) . ' нет подписчиков</span></div></div></div></li>' : '';

		foreach ($sql as $row) {
			// Gender
			$gender = ($bb_cfg['gender']) ? $lang['GENDER_SELECT'][$row['user_gender']] : '';
			$html .= '<li class="primaryContent memberListItem">';
			$html .= '<div style="width:50px; float: left; padding-top:4px;"><a title="' . $row['username'] . '" href="' . PROFILE_URL . $row['user_id'] . '">' . str_replace('<img', '<img class="avatarCropper"', get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar'), '', 50, 50)) . '</a></div>';
			$html .= '<div class="member"><h3 class="username">' . profile_url($row) . '</h3>';
			$html .= '<div class="userInfo">';
			$html .= '<div class="userBlurb dimmed"><span class="userTitle" itemprop="title">' . $gender . '</span></div>';
			$html .= '<dl class="userStats pairsInline">';
			$html .= '<dt title="Всего сообщений, опубликованных ' . $row['username'] . '">Сообщения:</dt> <dd>' . $row['user_posts'] . '</dd>&nbsp;';
			$html .= '<dt>Сид-бонусы:</dt> <dd title="Сид-бонусы">' . $row['user_points'] . '</dd>';
			$html .= '</dl></div></div></li>';
		}

		$html .= '</ol><div class="sectionFooter overlayOnly"><a href="#" onclick="$(\'.xenOverlay\').hide(\'slow\'); return false;" class="button primary OverlayCloser">Закрыть</a></div></div></div>';
		$this->response['html'] = $html;
		break;

	case 'list_user_subs':
		if (!$sql = CACHE('bb_cache')->get('list_user_subs_[' . $user_id . ']', 300)) {
			$sql = DB()->fetch_rowset("SELECT s.*, u.username, u.user_id, u.avatar_ext_id, u.user_opt, u.user_points, u.user_rank, u.user_gender, u.user_posts
            FROM bb_subs s,
            " . BB_USERS . " u
            WHERE s.subs_id = " . $user_id . "
            AND s.user_id = u.user_id");
			CACHE('bb_cache')->set('list_user_subs_[' . $user_id . ']', $sql);
		}

		$html = '<div class="xenOverlay" style="position: fixed; z-index: 9999; top: 92.30000000000001px; left: 286.5px; display: block;"><div class="section"><h2 class="heading h1">' . get_username($user_id) . ' является подписчиком пользователей</h2>';
		$html .= '<ol class="overlayScroll">';
		$html .= (!$sql) ? '<li class="primaryContent memberListItem"><div class="member"><div class="userInfo"><div class="userBlurb dimmed"><span class="userTitle">Пользователь ' . get_username($user_id) . ' ни на кого не подписан</span></div></div></div></li>' : '';

		foreach ($sql as $row) {
			// Gender
			$gender = ($bb_cfg['gender']) ? $lang['GENDER_SELECT'][$row['user_gender']] : '';
			$html .= '<li class="primaryContent memberListItem">';
			$html .= '<div style="width:50px; float: left; padding-top:4px;"><a title="' . $row['username'] . '" href="' . PROFILE_URL . $row['user_id'] . '">' . str_replace('<img', '<img class="avatarCropper"', get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar'), '', 50, 50)) . '</a></div>';
			$html .= '<div class="member"><h3 class="username">' . profile_url($row) . '</h3>';
			$html .= '<div class="userInfo">';
			$html .= '<div class="userBlurb dimmed"><span class="userTitle" itemprop="title">' . $gender . '</span></div>';
			$html .= '<dl class="userStats pairsInline">';
			$html .= '<dt title="Всего сообщений, опубликованных ' . $row['username'] . '">Сообщения:</dt> <dd>' . $row['user_posts'] . '</dd>&nbsp;';
			$html .= '<dt>Сид-бонусы:</dt> <dd title="Сид-бонусы">' . $row['user_points'] . '</dd>';
			$html .= '</dl></div></div></li>';
		}

		$html .= '</ol><div class="sectionFooter overlayOnly"><a href="#" onclick="$(\'.xenOverlay\').hide(\'slow\'); return false;" class="button primary OverlayCloser">Закрыть</a></div></div></div>';
		$this->response['html'] = $html;
		break;
}
