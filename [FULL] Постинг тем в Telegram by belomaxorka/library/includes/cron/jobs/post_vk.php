<?php

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

global $cron_runtime_log;

define('VK_VERSION', '5.199');

$vk_group_id = $bb_cfg['vk_group_id'];
$vk_token = $bb_cfg['vk_group_token'];
$vk_user_token = $bb_cfg['vk_user_token'];

if (empty($vk_token) || empty($vk_group_id) || empty($vk_user_token)) {
    $cron_runtime_log[] = 'Ошибка: Не указаны токен (группы и пользователя) или ID группы VK';
    return;
}

$sql = "
    SELECT
        a.attach_id,
        t.topic_title, t.topic_id, t.topic_image, t.island_cloud_android_url, t.island_cloud_comics_url, t.topic_edit_reason,
        p.post_time,
        pt.post_text,
        u.username, u.user_id,
        f.forum_name
    FROM bb_topics AS t
    LEFT JOIN bb_posts AS p ON (p.post_id = t.topic_first_post_id)
    LEFT JOIN bb_posts_text AS pt ON (pt.post_id = p.post_id)
    LEFT JOIN bb_users AS u ON (u.user_id = p.poster_id)
    LEFT JOIN bb_forums AS f ON (f.forum_id = t.forum_id)
    LEFT JOIN bb_attachments AS a ON (a.post_id = p.post_id)
    WHERE t.posted_vk_status = 0
    LIMIT 10
";

foreach (DB()->fetch_rowset($sql) as $row) {
    $isRelease = !empty($row['attach_id']) || !empty($row['island_cloud_android_url']) || !empty($row['island_cloud_comics_url']);
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

    // Заголовок и ссылка
    $message = sprintf('%s :: %s%s', htmlCHR($row['forum_name']), htmlCHR($row['topic_title']), $prefixes) . "\n";
    $message .= FULL_URL . TOPIC_URL . $row['topic_id'];

    // Текст сообщения
    if ($isRelease) {
        //
        // Раздача
        //

        // Автор темы
        // $message .= "\n\n" . sprintf('Автор темы: %s (%s)', htmlCHR($row['username']), FULL_URL . PROFILE_URL . $row['user_id']);

        // Ссылка на Android-приложение
        $islandAndroid = !empty($row['island_cloud_android_url']) ? $row['island_cloud_android_url'] : false;
        if ($islandAndroid) {
            $message .= "\n\nАндроид порт: $islandAndroid";
        }

        // Причина редактирования темы
        if (!empty($row['topic_edit_reason'])) {
            $message .= "\n\nПричина редактирования: " . htmlCHR($row['topic_edit_reason']);
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

    // Вывод списка тегов
    if ($tags) {
        $message .= "\n\n$tags";
    }

    // Убедимся, что сообщение в правильной кодировке
    $message = mb_convert_encoding($message, 'UTF-8', 'auto');
    $message = str_short($message, 2000);

    // Отправка через VK API
    $params = [
        'owner_id' => '-' . $vk_group_id,
        'from_group' => 1,
        'message' => $message,
        'signed' => 0,
        'access_token' => $vk_token,
        'v' => VK_VERSION
    ];

    // Добавляем постер в качестве вложения
    if ($hasTopicImage) {
        // Получаем адрес сервера для загрузки
        $uploadServerResponse = file_get_contents("https://api.vk.com/method/photos.getWallUploadServer?group_id={$vk_group_id}&access_token={$vk_user_token}&v=" . VK_VERSION);
        $uploadServerData = json_decode($uploadServerResponse, true);
        if (!isset($uploadServerData['response']['upload_url'])) {
            $cron_runtime_log[] = 'Не удалось получить URL для загрузки: ' . ($uploadServerData['error']['error_msg'] ?? 'Неизвестная ошибка');
        }
        $uploadUrl = $uploadServerData['response']['upload_url'];

        // Загружаем изображение на сервер ВК
        $tempFile = tempnam(sys_get_temp_dir(), 'vk_image');
        file_put_contents($tempFile, file_get_contents($hasTopicImage));

        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'photo' => new CURLFile($tempFile, mime_content_type($tempFile), basename($tempFile))
        ]);
        $uploadResponse = curl_exec($ch);
        curl_close($ch);

        // Удаление локальной копии файла
        if (is_file($tempFile)) {
            unlink($tempFile);
        }

        $uploadData = json_decode($uploadResponse, true);
        if (!isset($uploadData['server']) || !isset($uploadData['photo']) || !isset($uploadData['hash'])) {
            $cron_runtime_log[] = 'Ошибка загрузки фото: ' . ($uploadData['error']['error_msg'] ?? 'Неизвестная ошибка');
        }

        // Сохраняем фото на сервере ВК (используем заранее полученный токен приложения)
        $saveParams = [
            'group_id' => $vk_group_id,
            'server' => $uploadData['server'],
            'photo' => $uploadData['photo'],
            'hash' => $uploadData['hash'],
            'access_token' => $vk_user_token,
            'v' => VK_VERSION
        ];

        $saveResponse = file_get_contents('https://api.vk.com/method/photos.saveWallPhoto?' . http_build_query($saveParams));
        $saveData = json_decode($saveResponse, true);
        if (!isset($saveData['response'][0]['id'])) {
            $cron_runtime_log[] = 'Ошибка сохранения фото: ' . ($saveData['error']['error_msg'] ?? 'Неизвестная ошибка');
        }

        $photo = $saveData['response'][0];
        $params['attachments'] = "photo{$photo['owner_id']}_{$photo['id']}";
    }

    // Инициализация cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/wall.post');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    // Отправка запроса
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $cron_runtime_log[] = 'Ошибка при отправке запроса: ' . curl_error($ch);
    } else {
        $decodedResponse = json_decode($response, true);
        if (isset($decodedResponse['response']['post_id'])) {
            DB()->sql_query("UPDATE `bb_topics` SET `posted_vk_status` = 1 WHERE `topic_id` = " . (int)$row['topic_id'] . " LIMIT 1");
        } else {
            $cron_runtime_log[] = 'Ошибка при публикации поста: ' . ($decodedResponse['error']['error_msg'] ?? 'Неизвестная ошибка');
        }
    }

    // Закрываем соединение
    curl_close($ch);
}
