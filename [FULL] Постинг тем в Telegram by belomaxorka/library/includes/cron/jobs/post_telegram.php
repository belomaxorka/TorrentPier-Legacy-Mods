<?php

if (!defined('BB_ROOT')) {
	die(basename(__FILE__));
}

$chatId = $bb_cfg['tg_group'];
$botToken = $bb_cfg['tg_botkey'];

if (empty($botToken) || empty($chatId)) {
	return;
}

function rudate($format, $timestamp = 0, $nominative_month = false): string
{
	if (!$timestamp) {
		$timestamp = time();
	} elseif (!preg_match("/^[0-9]+$/", $timestamp)) {
		$timestamp = strtotime($timestamp);
	}

	$F = $nominative_month ? array(1 => "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь") : array(1 => "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
	$M = array(1 => "Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек");
	$l = array("Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");
	$D = array("Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб");

	$format = str_replace("F", $F[date("n", $timestamp)], $format);
	$format = str_replace("M", $M[date("n", $timestamp)], $format);
	$format = str_replace("l", $l[date("w", $timestamp)], $format);
	$format = str_replace("D", $D[date("w", $timestamp)], $format);

	return date($format, $timestamp);
}

$sql = "
    SELECT
        ad.attach_id, ad.filetime,
        t.topic_title, t.topic_id, t.topic_image,
        p.post_time,
        pt.post_text,
        u.username, u.user_id,
        f.forum_name, f.forum_tg_thread_id
    FROM bb_topics AS t
    LEFT JOIN bb_posts AS p ON (p.topic_id = t.topic_id)
    LEFT JOIN bb_posts_text AS pt ON (pt.post_id = p.post_id)
    LEFT JOIN bb_users AS u ON (u.user_id = p.poster_id)
    LEFT JOIN bb_forums AS f ON (f.forum_id = t.forum_id)
    LEFT JOIN bb_attachments AS a ON (a.post_id = p.post_id)
    LEFT JOIN bb_attachments_desc AS ad ON (ad.attach_id = a.attach_id)
    WHERE t.posted_tg_status = 0
    LIMIT 10
";

foreach (DB()->fetch_rowset($sql) as $row) {
	$params = [
		'chat_id' => $chatId,
		'parse_mode' => 'HTML'
	];

	$forumThreadId = (!empty($row['forum_tg_thread_id']) && $row['forum_tg_thread_id'] > 0) ? $row['forum_tg_thread_id'] : false;
	if (!$forumThreadId && $bb_cfg['tg_skip_forums_without_thread_id']) {
		continue;
	}
	$isRelease = !empty($row['attach_id']);
	$hasTopicImage = !empty($row['topic_image']) ? $row['topic_image'] : false;

	// Префиксы
	$prefixes = '';
	if (defined('BB_PREFIXES')) {
		$topic_prefixes = get_prefixes_list($row['topic_id'])['topic_prefixes'];
		$topic_prefixes = array_map(fn($prefix) => $prefix['prefix_name'], $topic_prefixes);
		$prefixes = ' ' . implode(' ', $topic_prefixes);
	}

	// Теги
	$tags = '';
	if (defined('BB_TAGS')) {
		$topic_tags = get_tags_list($row['topic_id'])['topic_tags'];
		$topic_tags = array_map(fn($tag) => '#' . str_replace([' ', '-', ',', '.', "'"], '_', $tag['tag_name']), $topic_tags);
		$tags = implode(' ', $topic_tags);
	}

	// Название темы
	if ($forumThreadId) {
		$message = "<a href=\"" . FULL_URL . TOPIC_URL . $row['topic_id'] . "\">" . htmlCHR($row['topic_title']) . "</a>" . $prefixes . "\n\n";
	} else {
		$message = "<a href=\"" . FULL_URL . TOPIC_URL . $row['topic_id'] . "\">" . htmlCHR($row['forum_name']) . " :: " . htmlCHR($row['topic_title']) . "</a>" . $prefixes . "\n\n";
	}

	// Текст сообщения
	if ($isRelease) {
		//
		// Раздача
		//

		$message .= "<b>Автор темы:</b> <a href=\"" . FULL_URL . PROFILE_URL . $row['user_id'] . "\">" . htmlCHR($row['username']) . "</a>";
		// if ($row['post_time'] == $row['filetime']) {
		//     $message .= "\nРелиз добавлен: " . rudate("j M Y G:i:s", $row['post_time']) . " (GMT +3)";
		// } elseif ($row['post_time'] < $row['filetime']) {
		//     $message .= "\nРелиз обновлен: " . rudate("j M Y G:i:s", $row['filetime']) . " (GMT +3)";
		// }

		// Ссылка на Android-приложение
		$islandAndroid = !empty($row['island_cloud_android_url']) ? $row['island_cloud_android_url'] : false;
		if ($islandAndroid) {
			$message .= "\n\n<b>Андроид порт:</b> <a href=\"" . $islandAndroid . "\">" . $islandAndroid . "</a>";
		}

		// Причина редактирования темы
		if (!empty($row['comment'])) {
			$message .= "\n\n<b>Причина редактирования:</b> " . htmlCHR($row['comment']);
		}
	} else {
		//
		// Обычное сообщение
		//

		if (!function_exists('strip_bbcode')) {
			require INC_DIR . '/bbcode.php';
		}

		$message .= strip_bbcode($row['post_text']);
		$message = preg_replace('/https?:\/\/[^\s]+\/' . preg_quote(hide_bb_path($bb_cfg['ajax_upload_posting_images_path']), '/') . '[^\s]+/', '', $message);
	}

	// Вывод списка тегов
	if ($tags) {
		$message .= "\n\n$tags";
	}

	// Выбираем тип сообщения
	if ($hasTopicImage) {
		$imageLink = $hasTopicImage;
		$params['photo'] = $imageLink;
		$params['caption'] = $message;
		$apiUrl = "https://api.telegram.org/bot$botToken/sendPhoto";
	} else {
		$params['text'] = $message;
		$apiUrl = "https://api.telegram.org/bot$botToken/sendMessage";
	}

	// Убедимся, что сообщение в правильной кодировке
	$message = mb_convert_encoding($message, 'UTF-8', 'auto');

	// Определяем message_thread_id для конкретного форума
	if ($forumThreadId) {
		$params['message_thread_id'] = $forumThreadId;
	}

	// Инициализация cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

	// Отправка запроса
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		$cron_runtime_log[] = 'Ошибка при отправке запроса: ' . curl_error($ch);
	} else {
		$decodedResponse = json_decode($response, true);
		if ($decodedResponse && $decodedResponse['ok']) {
			DB()->sql_query("UPDATE `bb_topics` SET `posted_tg_status` = 1 WHERE `topic_id` = " . (int)$row['topic_id'] . " LIMIT 1");
		} else {
			$cron_runtime_log[] = 'Ошибка при публикации поста: ' . $decodedResponse['description'] ?? 'Неизвестная ошибка';
		}
	}

	// Закрываем соединение
	curl_close($ch);
}
