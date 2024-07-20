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

$mode = request_var('mode', 'list');
$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;
$per_page = $bb_cfg['topics_per_page'];
$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

$sql = DB()->fetch_rowset("
	SELECT t.*, f.forum_id, f.forum_name FROM " . BB_BOOK . " b
		INNER JOIN " . BB_TOPICS . " t ON(t.topic_id = b.topic_id)
		INNER JOIN " . BB_FORUMS . " f ON(f.forum_id = b.forum_id)
	WHERE user_id = {$userdata['user_id']}
	ORDER BY b.time DESC
	LIMIT $start, $per_page
");

if (!$sql) {
	$template->assign_block_vars('no_book', array(
		'NO_BOOK' => $lang['BOOKMARKS_NONE'],
	));
} else {
	foreach ($sql as $i => $row) {
		$is_unread = is_unread($row['topic_last_post_time'], $row['topic_id'], $row['forum_id']);

		$template->assign_block_vars('book', array(
			'ROW_CLASS' => (!($i % 2)) ? 'row1' : 'row2',
			'REPLIES' => $row['topic_replies'],
			'VIEWS' => $row['topic_views'],
			'ID' => $row['topic_id'],
			'FORUM' => '<a href="' . FORUM_URL . $row['forum_id'] . '">' . $row['forum_name'] . '</a>',
			'POLL' => (bool)$row['topic_vote'],
			'TOPIC' => '<a title="' . preg_replace($orig_word, $replacement_word, $row['topic_title']) . '" href="' . TOPIC_URL . $row['topic_id'] . '">' . str_short(preg_replace($orig_word, $replacement_word, $row['topic_title']), 70) . '</a>',
			'TOPIC_ICON' => get_topic_icon($row, $is_unread)
		));
	}

	$sql = "SELECT COUNT(*) AS total FROM " . BB_BOOK . " WHERE user_id = {$userdata['user_id']}";
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Error getting total bookmarks');
	}
	if ($total = DB()->sql_fetchrow($result)) {
		$total_book = $total['total'];
		generate_pagination("book.php?mode=$mode", $total_book, $per_page, $start);
	}
	DB()->sql_freeresult($result);
}

print_page('book.tpl');
