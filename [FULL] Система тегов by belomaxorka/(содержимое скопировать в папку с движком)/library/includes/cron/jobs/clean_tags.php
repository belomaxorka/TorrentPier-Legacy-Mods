<?php

if (!defined('BB_ROOT')) {
	die(basename(__FILE__));
}

$tags = get_topic_tags()['all_tags']; // Все теги

$need_update_datastore = false;
foreach ($tags as $tag) {
	$row = DB()->fetch_row("
        SELECT COUNT(*) AS total FROM " . BB_TOPIC_TAGS . " WHERE tag_id = {$tag['tag_id']}
    ");

	if ($row['total'] == 0) {
		// Если tag_id отсутствует в bb_topic_tags, удаляем запись из bb_tags
		DB()->sql_query("DELETE FROM " . BB_TAGS . " WHERE tag_id = {$tag['tag_id']}");
		$need_update_datastore = true;
	}
}

if ($need_update_datastore) {
	$datastore->update('tags');
}
