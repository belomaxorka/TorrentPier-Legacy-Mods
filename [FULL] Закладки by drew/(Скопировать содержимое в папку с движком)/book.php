<?php

define('BB_SCRIPT', 'book');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

//
// Define censored word matches
//
$orig_word = $replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

$page_cfg['use_tablesorter'] = true;

// Init userdata
$user->session_start(array('req_login' => true));

$sql = DB()->fetch_rowset("SELECT b.*, t.*, f.* FROM " . BB_BOOK . " b
								INNER JOIN " . BB_TOPICS . " t ON(t.topic_id = b.topic_id)
								INNER JOIN " . BB_FORUMS . " f ON(f.forum_id = b.forum_id)
							WHERE user_id = {$userdata['user_id']}");

if (!$sql) {
	$template->assign_block_vars('no_book', array(
		'NO_BOOK' => 'У вас нету закладок',
	));
} else {
	foreach ($sql as $i => $row) {
		$is_unread = is_unread($row['topic_last_post_time'], $row['topic_id'], $row['forum_id']);

		$template->assign_block_vars('book', array(
			'REPLIES' => $row['topic_replies'],
			'VIEWS' => $row['topic_views'],
			'ID' => $row['topic_id'],
			'FORUM' => '<a href="' . FORUM_URL . $row['forum_id'] . '">' . $row['forum_name'] . '</a>',
			'POLL' => (bool)$row['topic_vote'],
			'TOPIC' => '<a title="' . preg_replace($orig_word, $replacement_word, $row['topic_title']) . '" href="' . TOPIC_URL . $row['topic_id'] . '">' . str_short(preg_replace($orig_word, $replacement_word, $row['topic_title']), 70) . '</a>',
			'TOPIC_ICON' => get_topic_icon($row, $is_unread)
		));
	}
}

print_page('book.tpl');
