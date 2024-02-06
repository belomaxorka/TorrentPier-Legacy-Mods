<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata;

if (!$bb_cfg['warning']['enabled']) $this->ajax_die('Запреты отключены.');

$mode = (string)$this->request['mode'];
$warning = (int)$this->request['warning'];
$term = (int)$this->request['term'];
$time = (int)$this->request['time'];
$reason = (string)$this->request['reason'];

if (!$warning) $this->ajax_die('Вы не выбрали тип запрета.');
if (!$reason) $this->ajax_die('Вы не указали причину запрета.');

if (!empty($this->request['user_id'])) {
	// Для выдачи
	$user_id = (int)$this->request['user_id'];
	if ($user_id < 0) $this->ajax_die('Данному пользователю запрещено выдавать запрет.');
} else {
	// Для редактирования
	$id = (int)$this->request['id'];
	$war = DB()->fetch_row("SELECT * FROM " . BB_WARNINGS . " WHERE id = $id");
	if (!$war) $this->ajax_die('Такого запрета не существует.');

	// Модератор может редактировать запрет, выданный только собой.
	if ($war['poster_id'] != $userdata['user_id'] && IS_MOD) $this->ajax_die('Вы не можете редактировать данный запрет.');
	$user_id = $war['user_id'];
}

// Перерасчёт всех активных запретов юзверя
DB()->query("UPDATE " . BB_WARNINGS . " SET auth = 0 WHERE auth != 3 AND time_end < " . TIMENOW);
DB()->query("UPDATE " . BB_USERS . " u SET u.user_warnings = (SELECT COUNT(w.id) FROM " . BB_WARNINGS . " w WHERE u.user_id = w.user_id AND w.auth IN(1,3))");

switch ($mode) {
	case 'add':
		@$type = (string)$this->request['type'];
		@$type_id = (int)$this->request['type_id'];

		if (!$term && ($time != DELETED)) $this->ajax_die('Вы не указали срок запрета.');
		if (!$user_id || !$time) $this->ajax_die('Убейся, кривые руки.');

		// Получаем данные по пользователю
		$row = DB()->fetch_row("SELECT username, user_level FROM " . BB_USERS . " WHERE user_id = $user_id LIMIT 1");

		if (!IS_ADMIN && ($user_id != $userdata['user_id'])) {
			// Проверка на админа и модера
			if ($row['user_level'] == ADMIN) {
				$ololo = array('Бан админу - самоубийство', 'Модер, ты камикадзе?', 'Забанишь админа - и кладбище радостно встретит тебя', 'Не кусай руку с едой!', 'Модер тебе хана!', 'И после бана админу ад встретит тебя с радостным воплем "Welcome!@', 'А теперь сделай ручкой модерству и трекеру', 'Сцуко! На святое покусился?!', 'Вот сейчас тебе админ такую баню устроит!', 'А по печени?', 'Админ временно недоступен!', 'Забаненный админ страшнее торнадо', 'Это ж админ! Кто ж его забанит?!', 'Покушение на бан админу - от 5 до 15.', 'Руки прочь от чудотворца!', 'Не видать тебе модерства, как админу бана');
				$bI = rand(0, count($ololo));
				$this->ajax_die($ololo[$bI]);
			} else if ($row['user_level'] == MOD) {
				$blablabla = array('Бан один на всех модеров сразу!', 'Поляна общая - не суетись!', 'Брат на брата?!', 'Бань модератора! Так его, сцуку!', 'Глаза разуй - он же модер!', 'Модер модеру друг, товарищ и брат!', 'А прав то хватит?)', 'Низззя!', 'За наказанием обратитесь к администратору.');
				$bI = rand(0, count($blablabla));
				$this->ajax_die($blablabla[$bI]);
			}
		}

		// Только супер админ может банить юзверя
		if ($warning == DELETED) {
			if (!IS_SUPER_ADMIN) $this->ajax_die($lang['ONLY_FOR_SUPER_ADMIN']);
			if ($user_id == $userdata['user_id']) $this->ajax_die('Администратор, ты ебанулся, банить самого себя?)');
			DB()->query("DELETE FROM " . BB_SESSIONS . " WHERE session_user_id = $user_id LIMIT 1");
		}

		// Запрет из топика
		if ($type == 'topic') {
			// проверка на модератора
			$mod = DB()->fetch_row("SELECT topic_id, forum_id FROM " . BB_POSTS . " WHERE post_id = $type_id LIMIT 1");
			$is_auth = auth(AUTH_ALL, $mod['forum_id'], $userdata);
			if (!$is_auth['auth_mod']) $this->ajax_die($lang['Not_Moderator']);

			// проверка на повторный запрет
			$again = DB()->fetch_row("SELECT id FROM " . BB_WARNINGS . " WHERE type = 'topic' AND type_id = $type_id AND auth IN(1,3)");
			// if($again) $this->response['url'] = html_entity_decode(make_url('/warnings.php?id='. $again['id']));
			if ($again) $this->ajax_die('За данное сообщение уже было выдано предупреждение');

			$this->response['url'] = html_entity_decode(make_url(POST_URL . $type_id . '#' . $type_id));
		} else {
			// Запрет выдачи запретов модерам через профиль
			if (!IS_ADMIN) $this->ajax_die('Функция доступна только администратору.');
			$this->response['url'] = html_entity_decode(make_url(PROFILE_URL . $user_id));
		}

		// Проверка на бессрочный
		if ($time == DELETED) {
			$auth = 3;
			$term = $time_end = 0;
		} else {
			$auth = 1;
			$time_end = (TIMENOW + ($time * $term));
		}

		$message = "Уважаемый [b]" . $row['username'] . "[/b], администрацией было выявлено нарушение одного из пунктов правил трекера с Вашей стороны![br]
			Рекомендуем Вам ещё раз ознакомиться с [url=" . make_url('/viewtopic.php?t=ТУТ_ID_ТОПИКА') . "]правилами[/url] данного ресурса и не повторять подобных нарушений.[br]
			Более подробную информацию Вы можете получить [url=" . make_url('/warnings.php?u=' . $user_id) . "]на вашей странице предупреждений и наказаний[/url].[br][br]
			[align=right]Это автоматическое сообщение! Вам не нужно на него отвечать![/align]";
		$subject = "Вам выдано предупреждение!";

		send_pm($user_id, $subject, $message, BOT_UID);

		DB()->query("INSERT INTO " . BB_WARNINGS . " (type, type_id, user_id, poster_id, reason, warning, time_start, time_end, term, auth)
			VALUES ('$type', $type_id, $user_id, '" . $userdata['user_id'] . "', '" . DB()->escape($reason) . "', '$warning', '" . TIMENOW . "', '$time_end', '$term', '$auth')");
		DB()->query("UPDATE " . BB_USERS . " SET user_warnings = user_warnings + 1 WHERE user_id = $user_id");
		cache_rm_user_sessions($user_id);

		$this->response['info'] = 'Запрет успешно добавлен.';
		break;
	case 'edit':
		$auth = (int)$this->request['auth'];

		// Только супер админ может банить юзверя
		if ($warning == DELETED) {
			if (!IS_SUPER_ADMIN) $this->ajax_die($lang['ONLY_FOR_SUPER_ADMIN']);
			if ($user_id == $userdata['user_id']) $this->ajax_die('Администратор, ты ебанулся, банить самого себя?)');
			DB()->query("DELETE FROM " . BB_SESSIONS . " WHERE session_user_id = $user_id LIMIT 1");
		}

		// Удаление запрета
		if ($auth == DELETED) {
			DB()->query("DELETE FROM " . BB_WARNINGS . " WHERE id = $id LIMIT 1");
			DB()->query("UPDATE " . BB_USERS . " SET user_warnings = user_warnings - 1 WHERE user_id = $user_id");

			$this->response['info'] = 'Запрет успешно удалён.';
			$this->response['url'] = html_entity_decode(make_url('/warnings.php?warnings'));
		} else {
			$time_end = ($war['time_start'] + ($time * $term));
			if ($auth == 1 && $time_end < TIMENOW) $this->ajax_die('Срок запрета уже истёк...');
			if ($time != DELETED && $term) {
				if (!$auth && $time_end > TIMENOW) $auth = 1;
				$set_time = "time_end = '$time_end', term = '$term', auth = '$auth'";
			} else {
				$set_time = "auth = '$auth'";
			}

			DB()->query("UPDATE " . BB_WARNINGS . " SET warning = '$warning', reason = '" . DB()->escape($reason) . "', $set_time WHERE id = $id");
			DB()->query("UPDATE " . BB_USERS . " u SET u.user_warnings = (SELECT COUNT(w.id) FROM " . BB_WARNINGS . " w WHERE w.user_id = $user_id AND w.auth IN(1,3) OR w.time_end > " . TIMENOW . ") WHERE u.user_id = $user_id");
			cache_rm_user_sessions($user_id);

			$this->response['info'] = 'Запрет успешно отредактирован.';
			$this->response['url'] = html_entity_decode(make_url('/warnings.php?warnings&id=' . $id));
		}
		break;
	default:
		$this->ajax_die('Invalid mode:' . $mode);
		break;
}
