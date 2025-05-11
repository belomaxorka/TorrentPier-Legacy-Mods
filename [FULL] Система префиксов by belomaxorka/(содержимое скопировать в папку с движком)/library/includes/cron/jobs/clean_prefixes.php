<?php

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$prefix_ids = DB()->fetch_rowset("
    SELECT DISTINCT prefix_id FROM " . BB_TOPIC_PREFIXES
);

$need_update_datastore = false;

foreach ($prefix_ids as $row) {
    $prefix_id = (int)$row['prefix_id'];

    $exists = DB()->fetch_row("
        SELECT 1 FROM " . BB_PREFIXES . " WHERE prefix_id = $prefix_id LIMIT 1
    ");

    if (!$exists) {
        DB()->sql_query("
            DELETE FROM " . BB_TOPIC_PREFIXES . "
            WHERE prefix_id = $prefix_id
        ");
        $need_update_datastore = true;
    }
}

if ($need_update_datastore) {
    $datastore->update('prefixes');
}
