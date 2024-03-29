<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'person');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');
require(INC_DIR . 'bbcode.php');

$allow_for_guests = true; // Показывать персоны гостям

$user->session_start(array('req_login' => $allow_for_guests));

$mode = request_var('mode', '');
$page = isset($_REQUEST['start']) ? abs(intval($_REQUEST['start'])) : 0;
$act_id = request_var('id', 0);
$submit = !empty($_POST['submit']);
$per_page = $bb_cfg['pers_per_page'];

$errors = array();

if (!empty($mode)) {
	if ($mode == 'edit') {
		$sql = "SELECT * FROM " . BB_PERSON . " WHERE pers_id = " . (int)$act_id;

		if (!$PersRow = DB()->fetch_row($sql)) {
			bb_die('PERS_NO');
		}

		$pers_gender = isset($PersRow['pers_gender']) ? $PersRow['pers_gender'] : 0;

		$pers_id = (isset($_POST['id'])) ? intval($_POST['id']) : 0;

		$pers_nameru = isset($_POST['runame']) ? trim($_POST['runame']) : '';
		$pers_nameen = isset($_POST['enname']) ? trim($_POST['enname']) : '';
		$pers_gender = isset($_POST['gender']) ? intval($_POST['gender']) : 0;
		$pers_biography = isset($_POST['biography']) ? trim($_POST['biography']) : '';
		$pers_career = isset($_POST['career']) ? trim($_POST['career']) : '';
		$pers_images = isset($_POST['foto']) ? trim($_POST['foto']) : '';
		$pers_birthday = isset($_POST['birthday']) ? trim($_POST['birthday']) : '';
		$pers_birthplace = isset($_POST['birthplace']) ? trim($_POST['birthplace']) : '';
		$kp_id = isset($_POST['kp_id']) ? intval($_POST['kp_id']) : 0;


		if ($submit && $pers_nameru === '') {
			$errors[] = $lang['NO_PERS_NAME'];
		}

		if ($submit && $pers_birthday !== '0000-00-00' && $pers_birthday) {

			$birthday_date = date_parse($pers_birthday);

			if (!empty($birthday_date['year'])) {
				if (strtotime($pers_birthday) >= TIMENOW) {
					$errors[] = $lang['WRONG_BIRTHDAY_FORMAT'];
				} elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] > $bb_cfg['birthday_max_age']) {
					$errors[] = sprintf($lang['BIRTHDAY_TO_HIGH'], $bb_cfg['birthday_max_age']);
				} elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] < $bb_cfg['birthday_min_age']) {
					$errors[] = sprintf($lang['BIRTHDAY_TO_LOW'], $bb_cfg['birthday_min_age']);
				}
			}

		} else if ($submit) {
			$errors[] = $lang['PERS_NO_BIRTHDAY'];
		}

		if ($submit && $pers_images !== '') {
			if (!preg_match('/(\.gif|\.png|\.jpg)$/is', $pers_images)) {
				$pers_images = '';
				$errors[] = $lang['PERS_WORK_IMAGES_FORMAT'];
			}
		}

		if (($kp_id < 0) || !is_numeric($kp_id)) {
			$errors[] = $lang['KP_ID_NO_FORMAT'];
		}

		$sql_ary = array(
			'pers_runame' => (string)$pers_nameru,
			'pers_enname' => (string)$pers_nameen,
			'pers_career' => (string)$pers_career,
			'pers_gender' => (int)$pers_gender,
			'pers_biography' => (string)$pers_biography,
			'pers_foto' => (string)$pers_images,
			'pers_birthdate' => (string)$pers_birthday,
			'pers_birthplace' => (string)$pers_birthplace,
			'kp_id' => (int)$kp_id,
		);

		if ($submit && !$errors) {

			$return_message = '';

			$sql_args = DB()->build_array('UPDATE', $sql_ary);

			// Update actor's data
			DB()->query("UPDATE " . BB_PERSON . " SET $sql_args WHERE pers_id = " . $pers_id);

			$return_message .= $lang['PERS_EDITED'] . '<br /><br /> <a href="person.php?id=' . $pers_id . '" >' . $lang['PERS_U_RETURN'] . '</a>';
			$return_message .= ' <span style="color:#CDCDCD;">|</span> <a href="index.php">' . $lang['INDEX_RETURN'] . '</a>';

			bb_die($return_message);
		}

		$template->assign_vars(array(
			'TPL_PERS_EDIT' => true,
			'PAGE_TITLE' => $lang['PERS_EDIT_TETLE'],

			'ERROR_MESSAGE' => ($errors) ? join('<br />', array_unique($errors)) : '',
			'MODE' => $mode,

			'PERS_NAME_RU' => isset($PersRow['pers_runame']) ? $PersRow['pers_runame'] : '',
			'PERS_NAME_EN' => isset($PersRow['pers_enname']) ? $PersRow['pers_enname'] : '',
			'PERS_GENDER' => build_select('gender', array_flip($lang['GENDER_SELECT']), $pers_gender),
			'PERS_CAREER' => isset($PersRow['pers_career']) ? $PersRow['pers_career'] : '',
			'PERS_BIOGRAPHY' => isset($PersRow['pers_biography']) ? $PersRow['pers_biography'] : '',
			'PERS_IMAGES' => isset($PersRow['pers_foto']) ? $PersRow['pers_foto'] : '',
			'PERS_BIRTHDAY' => isset($PersRow['pers_birthdate']) ? $PersRow['pers_birthdate'] : '',
			'PERS_BIRTHPLACE' => isset($PersRow['pers_birthplace']) ? $PersRow['pers_birthplace'] : '',
			'KP_ID' => isset($PersRow['kp_id']) ? $PersRow['kp_id'] : 0,
			'S_HIDDEN_FIELDS' => '<input type="hidden" name="id" value="' . $act_id . '" />',
			'S_RANK_ACTION' => "person.php",
		));
	}
} else if (!empty($act_id)) {
	$sql = "SELECT * FROM " . BB_PERSON . " WHERE pers_id = " . (int)$act_id;
	if (!$a_data = DB()->fetch_row($sql)) {
		bb_die('PERS_NO');
	}

	if (!$forums = $datastore->get('cat_forums')) {
		$datastore->update('cat_forums');
		$forums = $datastore->get('cat_forums');
	}

	$ennamesearch = ($a_data['pers_enname'] ? " OR pt.post_text LIKE '%" . DB()->escape($a_data['pers_enname']) . "%'" : "");
	$namesearch = ($ennamesearch ? "(pt.post_text LIKE '%" . DB()->escape($a_data['pers_runame']) . "%'{$ennamesearch})" : "pt.post_text LIKE '%" . DB()->escape($a_data['pers_runame']) . "%'");

	$dist_actor = DB()->fetch_rowset("SELECT t.*, u.user_id, u.username, u.user_rank, tor.*, ts.seeders, ts.leechers
            FROM " . BB_TOPICS . " t
            LEFT JOIN " . BB_POSTS . " p ON(p.post_id               = t.topic_first_post_id)
            LEFT JOIN " . BB_USERS . " u ON(p.poster_id            = u.user_id)
            LEFT JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id        = p.post_id)
            LEFT JOIN " . BB_BT_TORRENTS . " tor ON(t.topic_id      = tor.topic_id)
            LEFT JOIN " . BB_BT_TRACKER_SNAP . " ts ON(tor.topic_id = ts.topic_id)
        WHERE {$namesearch}
            AND p.post_id = pt.post_id
            AND t.topic_id = p.topic_id
            AND tor.topic_id = t.topic_id
        ORDER BY p.post_time ASC
    ");

	if ($dist_actor) {
		foreach ($dist_actor as $row) {
			$template->assign_block_vars('distribution', array(
				'POST_ID' => $row['topic_first_post_id'],

				'TOPIC_TITLE' => wbr(str_short($row['topic_title'], 70)),
				'FULL_TOPIC_TITLE' => $row['topic_title'],
				'FORUM_NAME' => $forums['forum'][$row['forum_id']]['forum_name'],
				'PAGINATION' => ($row['topic_status'] == TOPIC_MOVED) ? '' : build_topic_pagination(TOPIC_URL . $row['topic_id'], $row['topic_replies'], $bb_cfg['posts_per_page']),

				'U_FORUM' => FORUM_URL . $row['forum_id'],
				'U_VIEW_TOPIC' => TOPIC_URL . $row['topic_id'],

				'REPLIES' => $row['topic_replies'],
				'VIEWS' => $row['topic_views'],
				'AUTHOR' => profile_url(array('user_id' => $row['user_id'], 'username' => $row['username'], 'user_rank' => $row['user_rank'])),

				'SEEDERS' => ($row['seeders']) ?: 0,
				'LEECHERS' => ($row['leechers']) ?: 0,
				'TOR_SIZE' => humn_size($row['size']),
				'COMPL_CNT' => $row['complete_count'],
				'ATTACH_ID' => $row['attach_id'],
				'TOR_FROZEN' => (!IS_AM) ? isset($bb_cfg['tor_frozen'][$row['tor_status']]) : '',
				'TOR_STATUS_ICON' => $bb_cfg['tor_icons'][$row['tor_status']],
				'TOR_STATUS_TEXT' => $lang['TOR_STATUS_NAME'][$row['tor_status']],
			));
		}
	}

	$template->assign_vars(array(
		'TPL_PERS_VIEW' => true,
		'PAGE_TITLE' => $a_data['pers_runame'],
		'PERS_ID' => $act_id,
		'PERS_NAME_RU' => $a_data['pers_runame'],
		'PERS_NAME_EN' => $a_data['pers_enname'],
		'PERS_CAREER' => $a_data['pers_career'],
		'PERS_IMAGES' => (!empty($a_data['pers_foto'])) ? $bb_cfg['pers_photo_dir'] . '/' . $a_data['pers_foto'] : '',
		'PERS_GENDER' => $lang['GENDER_SELECT'][$a_data['pers_gender']] . ' ' . gender_image($a_data['pers_gender']),
		'PERS_BIOGRAPHY' => bbcode2html($a_data['pers_biography']),
		'PERS_BIRTHPLACE' => $a_data['pers_birthplace'],
		'KP_ID' => $a_data['kp_id'],
		'DIST_ACTOR' => $dist_actor,

		'BIRTHDATA' => bb_date(strtotime($a_data['pers_birthdate']), 'd F Y'),
		'AGE' => birthday_age($a_data['pers_birthdate']),
		'ZODIAC_SIGN' => get_zodiac($a_data['pers_birthdate'], 'images'),
		'U_ACTORS' => "person.php",
		'U_PERS_URL' => "person.php?id=" . $act_id,
		'U_PERS_EDIT' => "person.php?mode=edit&amp;id=" . $a_data['pers_id'],
	));
} else {
	$letter = request_var('letter', '');
	$sql_letter = '';

	if ($letter == 'other') {
		$sql_letter = " WHERE";
		for ($i = 97; $i < 123; $i++) {
			if ($i === 97) {
				$sql_letter .= " pers_enname NOT LIKE '" . DB()->escape(chr($i)) . "%'";
			} else {
				$sql_letter .= " AND pers_enname NOT LIKE '" . DB()->escape(chr($i)) . "%'";
			}
		}
	} else if ($letter) {
		$sql_letter = " WHERE pers_enname LIKE '" . DB()->escape(substr($letter, 0, 1)) . "%'";
	}

	$sql = "SELECT pers_id, pers_runame, pers_enname, pers_birthdate, pers_foto FROM " . BB_PERSON;
	$sql .= $sql_letter . " ORDER BY pers_id ASC LIMIT $page, " . $per_page;

	$template->assign_vars(array(
		'TPL_PERS_LIST' => true,
		'U_PERSON' => "person.php",
	));

	$first_char = array();
	$first_char[''] = $lang['ALL'];

	for ($i = 97; $i < 123; $i++) {
		$first_char[chr($i)] = chr($i - 32);
	}

	$first_char['other'] = $lang['OTHER'];

	foreach ($first_char as $char => $desc) {
		$template->assign_block_vars('first_char', array(
			'DESC' => $desc,
			'VALUE' => $char,
			'S_SELECTED' => ($letter == $char) ? true : false,
			'U_SORT' => "person.php" . (($char === '') ? '' : '?letter=' . $char),
		));
	}

	if ($act_rows = DB()->fetch_rowset($sql)) {
		$template->assign_vars(array(
			'TPL_PERS_LIST_NO_ERROR' => true,
		));

		foreach ($act_rows as $i => $rows) {
			$act_id = $rows['pers_id'];

			$template->assign_block_vars('pers_list', array(
				'PAGE_TITLE' => $lang['PERS_PERSONS'],
				'PERS_ID' => $act_id,
				'RU_NAME' => $rows['pers_runame'],
				'EN_NAME' => $rows['pers_enname'],
				'AGE' => birthday_age($rows['pers_birthdate']),
				'IMAGES' => (!empty($rows['pers_foto'])) ? $bb_cfg['pers_photo_dir'] . '/' . $rows['pers_foto'] : '',

				'U_ACTOR' => "person.php?id=" . $act_id,
				'U_PERS_IDIT' => "person.php?mode=edit&amp;id=" . $act_id,
			));
		}

		$sql = "SELECT COUNT(pers_id) AS total FROM " . BB_PERSON . $sql_letter;
		if (!$result = DB()->sql_query($sql)) {
			bb_die('Error getting total actors');
		}
		if ($total = DB()->sql_fetchrow($result)) {
			$paginationurl = ($letter == '') ? 'person.php' : "person.php?letter=$letter";

			$total_actors = $total['total'];
			generate_pagination($paginationurl, $total_actors, $per_page, $page);
		}
		DB()->sql_freeresult($result);
	} else {
		$template->assign_vars(array(
			'TPL_PERS_LIST_NO_ERROR' => false,
			'NO_PERSON_LIST' => '<br />' . $lang['PERS_NO_LIST'] . '<br /><br /><a href="index.php">' . $lang['INDEX_RETURN'] . '</a><br />'
		));
	}
}

print_page('person.tpl');
