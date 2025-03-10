<?php

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!$topic_id = (int)$this->request['topic_id']) {
    $this->ajax_die('invalid topic_id (empty)');
}

$row = DB()->fetch_row("
    SELECT seeders, leechers, speed_up, speed_down
		FROM " . BB_BT_TRACKER_SNAP . "
		WHERE topic_id = $topic_id
		LIMIT 1"
);

$row['seeders'] = isset($row['seeders']) ? $row['seeders'] : 0;
$row['leechers'] = isset($row['seeders']) ? $row['seeders'] : 0;
$row['speed_up'] = isset($row['speed_up']) ? $row['speed_up'] : 0;
$row['speed_down'] = isset($row['speed_down']) ? $row['speed_down'] : 0;

$this->response['html'] = '<span class="seedmed">&uArr;' . $row['seeders'] . ' (' . ($row['speed_up'] ? humn_size(($row['speed_up']), 0, 'KB') . '/s' : '0 KB/s') . ')</span>&nbsp;<span class="leechmed">&dArr;' . $row['leechers'] . ' (' . ($row['speed_down'] ? humn_size(($row['speed_down']), 0, 'KB') . '/s' : '0 KB/s') . ')</span>';
$this->response['topic_id'] = $topic_id;
