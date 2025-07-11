<?php

if (!defined('BB_ROOT')) {
	die(basename(__FILE__));
}

global $cron_runtime_log;

$sql = "
    SELECT
        a.attach_id,
        t.topic_title, t.topic_id, t.topic_image, t.topic_edit_reason,
        p.post_time,
        pt.post_text,
        u.username, u.user_id,
        f.forum_name, f.forum_ds_webhook
    FROM bb_topics AS t
    LEFT JOIN bb_posts AS p ON (p.post_id = t.topic_first_post_id)
    LEFT JOIN bb_posts_text AS pt ON (pt.post_id = p.post_id)
    LEFT JOIN bb_users AS u ON (u.user_id = t.topic_poster)
    LEFT JOIN bb_forums AS f ON (f.forum_id = t.forum_id)
    LEFT JOIN bb_attachments AS a ON (a.post_id = p.post_id)
    WHERE t.posted_ds_status = 0
    LIMIT 10
";

foreach (DB()->fetch_rowset($sql) as $row) {
	$currentWebhookUrl = !empty($row['forum_ds_webhook']) ? $row['forum_ds_webhook'] : $bb_cfg['ds_webhook_url'];
	if ($bb_cfg['ds_skip_forums_without_webhook']) {
		if (empty($row['forum_ds_webhook'])) {
			continue;
		}
	} else {
		if (empty($currentWebhookUrl)) {
			continue;
		}
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

	// Разделитель сообщения
	$message = "★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★  ★\n";

	// Заголовок
	$forumName = htmlCHR($row['forum_name']);
	$topicTitle = htmlCHR($row['topic_title']);
	$topicLink = FULL_URL . TOPIC_URL . $row['topic_id'];
	if ($currentWebhookUrl !== $bb_cfg['ds_webhook_url']) {
		$message .= $topicTitle . $prefixes . "\n";
	} else {
		$message .= "**[$forumName]** $topicTitle" . $prefixes . "\n";
	}

	// Ссылка на тему
	$message .= "**Ссылка:** $topicLink";

	// Вывод списка тегов
	if ($tags) {
		$message .= "\n\n$tags";
	}

	// Текст сообщения
	if ($isRelease) {
		//
		// Раздача
		//

		// Автор темы
		$authorLink = FULL_URL . PROFILE_URL . $row['user_id'];
		$authorName = htmlCHR($row['username']);
		$message .= "\n\n**Автор темы:** [{$authorName}]({$authorLink})";

		// Причина редактирования темы
		if (!empty($row['topic_edit_reason'])) {
			$message .= "\n\n**Причина редактирования:** " . htmlCHR($row['topic_edit_reason']);
		}
	} else {
		//
		// Обычное сообщение
		//

		if (!function_exists('strip_bbcode')) {
			require INC_DIR . '/bbcode.php';
		}

		$message .= "\n\n" . strip_bbcode($row['post_text']);
		$message = preg_replace('/https?:\/\/[^\s]+\/' . preg_quote(hide_bb_path($bb_cfg['ajax_upload_posting_images_path']), '/') . '[^\s]+/', '', $message);
	}

	// Убедимся, что сообщение в правильной кодировке
	$message = mb_convert_encoding($message, 'UTF-8', 'auto');

	// Формируем JSON для Discord
	$message = str_short($message, 2000);
	$payload = ['content' => $message];

	// Картинка как embed
	if ($hasTopicImage) {
		$payload['embeds'] = [[
			'image' => ['url' => $hasTopicImage]
		]];
	}

	// Отправляем в Discord
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $currentWebhookUrl);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if (curl_errno($ch)) {
		$cron_runtime_log[] = 'Ошибка cURL: ' . curl_error($ch);
	} else {
		if ($httpCode >= 200 && $httpCode < 300) {
			if (empty($response)) {
				DB()->sql_query("UPDATE `bb_topics` SET `posted_ds_status` = 1 WHERE topic_id = " . (int)$row['topic_id'] . " LIMIT 1");
			} else {
				$decodedResponse = json_decode($response, true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					$cron_runtime_log[] = 'Ошибка JSON: ' . json_last_error_msg();
					$cron_runtime_log[] = 'Получен некорректный JSON: ' . $response;
				} elseif (!empty($decodedResponse['code'])) {
					$cron_runtime_log[] = "Ошибка Discord API: {$decodedResponse['code']} — {$decodedResponse['message']}";
				} else {
					DB()->sql_query("UPDATE `bb_topics` SET `posted_ds_status` = 1 WHERE topic_id = " . (int)$row['topic_id'] . " LIMIT 1");
				}
			}
		} else {
			$cron_runtime_log[] = "Ошибка Discord. HTTP код: $httpCode";
			$cron_runtime_log[] = 'Ответ: ' . $response;
		}
	}

	curl_close($ch);
}
