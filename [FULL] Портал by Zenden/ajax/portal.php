<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

$topic_id = (int) $this->request['topic_id'];

$row = DB()->fetch_row("SELECT seeders, leechers, speed_up, speed_down
			FROM " . BB_BT_TRACKER_SNAP . "
			WHERE topic_id = $topic_id
			LIMIT 1"
		);

$this->response['html'] = '<span class="seedmed">&uArr;'.$row['seeders'].' ('.($row['speed_up'] ? humn_size(($row['speed_up']), 0, 'KB').'/s' : '0 KB/s').')</span><span class="leechmed">&dArr;'.$row['leechers'].' ('.($row['speed_down'] ? humn_size(($row['speed_down']), 0, 'KB').'/s' : '0 KB/s').')</span>';
$this->response['topic_id']	= $topic_id;
