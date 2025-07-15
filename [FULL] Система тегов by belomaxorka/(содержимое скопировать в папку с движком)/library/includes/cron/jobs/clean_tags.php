<?php

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$max_iterations_per_cycle = 50;

$tags = get_tags_list()['all_tags']; // Все теги

$need_update_datastore = false;
$processed_count = 0;
foreach ($tags as $tag) {
    if ($processed_count >= $max_iterations_per_cycle) {
        break;
    }

    $row = DB()->fetch_row("
        SELECT COUNT(*) AS total FROM " . BB_TOPIC_TAGS . " WHERE tag_id = {$tag['tag_id']}
    ");

    if ($row['total'] == 0) {
        if ($tag['tag_creation_time'] == 0 || ((TIMENOW - (int)$tag['tag_creation_time']) > 3600)) {
            DB()->sql_query("DELETE FROM " . BB_TAGS . " WHERE tag_id = {$tag['tag_id']}");
            $need_update_datastore = true;
            $processed_count++;
        }
    }
}

if ($need_update_datastore) {
    $datastore->update('tags');
}
