<?php

if (!empty($setmodules)) {
	$module['USERS']['PRIVATE_MESSAGING'] = basename(__FILE__);
	return;
}

require('./pagestart.php');
require(INC_DIR . 'bbcode.php');

$start = (int)request_var('start', 0);

$pagination = '';
$pm_count = DB()->sql_fetchrow(DB()->sql_query("SELECT COUNT(privmsgs_id) as total FROM " . BB_PRIVMSGS));
if ($pm_count['total'] > 50) {
	$pagination = generate_pagination('admin_ls.php?', $pm_count['total'], $bb_cfg['posts_per_page'], $start);
}

$template->assign_vars(array(
	'PAGINATION' => $pagination,
));

$sql = "SELECT u.username AS username_1, u.user_id AS user_id_1, u.user_rank AS user_rank_1, u2.username AS username_2, u2.user_id AS user_id_2, u2.user_rank AS user_rank_2, pm.privmsgs_date, pm.privmsgs_ip, pmt.privmsgs_text
	FROM " . BB_PRIVMSGS . " pm, " . BB_PRIVMSGS_TEXT . " pmt, " . BB_USERS . " u, " . BB_USERS . " u2
	WHERE pmt.privmsgs_text_id = pm.privmsgs_id
		AND u.user_id = pm.privmsgs_from_userid
		AND u2.user_id = pm.privmsgs_to_userid
	ORDER BY pm.privmsgs_date DESC
	LIMIT " . $start . ", 50";
$result = DB()->sql_query($sql);

$row_counter = 0;
while ($pm_text = DB()->sql_fetchrow($result)) {
	$row_class = !($row_counter % 2) ? 'row1' : 'row2';
	$template->assign_block_vars('pmrow', [
		'ROW_CLASS' => $row_class,
		'FROM' => profile_url(array('username' => $pm_text['username_1'], 'user_id' => $pm_text['user_id_1'], 'user_rank' => $pm_text['user_rank_1'])),
		'TO' => profile_url(array('username' => $pm_text['username_2'], 'user_id' => $pm_text['user_id_2'], 'user_rank' => $pm_text['user_rank_2'])),
		'DATE' => bb_date($pm_text['privmsgs_date']),
		'IP' => ($pm_text['privmsgs_ip'] != '7f000001') ? \TorrentPier\Helpers\IPHelper::long2ip_extended($pm_text['privmsgs_ip']) : '0.0.0.0',
		'MESSAGE' => '<div class="post_wrap"><div class="post_body">' . bbcode2html($pm_text['privmsgs_text']) . '</div></div>',
	]);

	$row_counter++;
}

print_page('admin_ls.tpl', 'admin');
