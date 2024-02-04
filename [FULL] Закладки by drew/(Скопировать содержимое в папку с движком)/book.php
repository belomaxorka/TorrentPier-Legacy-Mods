<?php

define('BB_SCRIPT', 'book');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

$page_cfg['use_tablesorter'] = true;

// Init userdata
$user->session_start(array('req_login' => true));

$sql = DB()->fetch_rowset("SELECT b.*, t.topic_views, t.topic_replies, t.topic_title, t.topic_id, f.forum_id, f.forum_name
								FROM " . BB_BOOK . " b
									LEFT JOIN " . BB_TOPICS . " t ON(t.topic_id = b.topic_id)
									LEFT JOIN " . BB_FORUMS . " f ON(f.forum_id = b.forum_id)
								WHERE user_id = {$userdata['user_id']}");

if (!$sql) {
	$template->assign_block_vars('no_book', array(
		'NO_BOOK' => $lang['NO_USER_ID_SPECIFIED'],
	));
} else {
	foreach ($sql as $i => $row) {
		$template->assign_block_vars('book', array(
			'REPLIES' => $row['topic_replies'],
			'VIEWS' => $row['topic_views'],
			'ID' => $row['topic_id'],
			'FORUM' => '<a href="' . FORUM_URL . $row['forum_id'] . '">' . $row['forum_name'] . '</a>',
			'TOPIC' => '<a href="' . TOPIC_URL . $row['topic_id'] . '">' . $row['topic_title'] . '</a>',
		));
	}
}

print_page('book.tpl');
