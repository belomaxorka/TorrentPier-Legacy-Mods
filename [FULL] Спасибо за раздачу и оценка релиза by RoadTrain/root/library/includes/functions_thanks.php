<?php
function get_user_thanks($uid)
{
	$uid = (int)$uid;
	$row = DB()->fetch_row("
			SELECT COUNT(user_id) AS thanks
				FROM " . BB_ATTACHMENTS_RATING . "
			WHERE user_id = $uid");

	return $row['thanks'] ?? 0;
}

function get_user_thanked($uid)
{
	$uid = (int)$uid;
	$row = DB()->fetch_row("
			SELECT COUNT(r.user_id) AS thanked
				FROM " . BB_ATTACHMENTS_RATING . " r
				LEFT JOIN bb_attachments a ON (a.attach_id = r.attach_id)
			WHERE a.user_id_1 = $uid AND r.thanked = 1");

	return $row['thanked'] ?? 0;
}
