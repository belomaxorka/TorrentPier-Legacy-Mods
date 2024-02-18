<?php

if (!empty($setmodules)) {
	$module['MODS']['AD_PERS_PANEL'] = basename(__FILE__);
	return;
}
require('./pagestart.php');

$mode = request_var('mode', '');
$page = isset($_REQUEST['start']) ? abs(intval($_REQUEST['start'])) : 0;
$act_id = request_var('id', 0);

$submit = !empty($_POST['submit']);

$errors = array();

switch ($mode) {
	case 'add':
	case 'edit':
		$s_hidden_fields = '';

		if ($mode === 'edit') {

			$sql = "SELECT * FROM " . BB_PERSON . " WHERE pers_id = " . (int)$act_id;

			if (!$pers_info = DB()->fetch_row($sql)) {
				bb_die('PERS_NO');
			}

			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $act_id . '" />';
		}

		$pers_gender = isset($pers_info['pers_gender']) ? $pers_info['pers_gender'] : 0;

		$template->assign_vars(array(

			'PERS_NAME_RU' => isset($pers_info['pers_runame']) ? $pers_info['pers_runame'] : '',
			'PERS_NAME_EN' => isset($pers_info['pers_enname']) ? $pers_info['pers_enname'] : '',
			'PERS_GENDER' => build_select('gender', array_flip($lang['GENDER_SELECT']), $pers_gender),
			'PERS_CAREER' => isset($pers_info['pers_career']) ? $pers_info['pers_career'] : '',
			'PERS_BIOGRAPHY' => isset($pers_info['pers_biography']) ? $pers_info['pers_biography'] : '',
			'PERS_IMAGES' => isset($pers_info['pers_foto']) ? $pers_info['pers_foto'] : '',
			'PERS_BIRTHDAY' => isset($pers_info['pers_birthdate']) ? $pers_info['pers_birthdate'] : '',
			'PERS_BIRTHPLACE' => isset($pers_info['pers_birthplace']) ? $pers_info['pers_birthplace'] : '',
			'KP_ID' => isset($pers_info['kp_id']) ? $pers_info['kp_id'] : 0,
			'S_HIDDEN_FIELDS' => $s_hidden_fields,
		));

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

		if ($submit && $pers_nameru !== '') {
			$persname_sql = DB()->escape($pers_nameru);
			if ($row = DB()->fetch_row("SELECT pers_runame FROM " . BB_PERSON . " WHERE pers_runame = '$persname_sql' LIMIT 1")) {
				$errors[] = $lang['PERS_THERE'];
			}
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
			if ($mode === 'edit') {
				$sql_args = DB()->build_array('UPDATE', $sql_ary);
				// Update actor's data
				DB()->query("UPDATE " . BB_PERSON . " SET $sql_args WHERE pers_id = " . $pers_id);
				$datastore->update('actors');
				$return_message .= $lang['PERS_EDITED'] . '<br /><br /> <a href="admin_person.php?id=' . $pers_id . '" >' . $lang['PERS_U_RETURN'] . '</a>';
			} else {
				$sql_args = DB()->build_array('INSERT', $sql_ary);
				// Create new actors
				DB()->query("INSERT INTO " . BB_PERSON . " $sql_args");
				$id = DB()->sql_nextid();
				$return_message .= $lang['PERS_ADD'] . '<br /><br /> <a href="admin_person.php?id=' . $id . '" >' . $lang['PERS_U_RETURN'] . '</a>';
			}
			$return_message .= ' <span style="color:#CDCDCD;">|</span> <a href="index.php">' . $lang['INDEX_RETURN'] . '</a>';
			bb_die($return_message);
		}

		$template->assign_vars(array(
			'TPL_PERS_EDIT' => true,
			'PAGE_TITLE' => ($mode == 'edit') ? $lang['PERS_EDIT_TETLE'] : $lang['PERS_NEW_ADD_TETLE'],
			'ERROR_MESSAGE' => ($errors) ? join('<br />', array_unique($errors)) : '',
			'MODE' => $mode,
			'S_RANK_ACTION' => "admin_person.php",
			'S_HIDDEN_FIELDS' => $s_hidden_fields,
		));
		break;
	case 'up':
		$template->assign_vars(array(
			'TPL_PERS_POST_UP' => true,
		));
		break;
	case 'parser':
		$template->assign_vars(array(
			'TPL_PERS_PARSER' => true,
		));
		break;
	case 'config':
		$return_links = array(
			'list' => '<br /><br />' . sprintf($lang['AD_CLICK_RETURN_PERS_LIST'], '<a href="admin_person.php">', '</a>'),
			'config' => '<br /><br />' . sprintf($lang['AD_CLICK_RETURN_PERS_CONFIG'], '<a href="admin_person.php?mode=config">', '</a>')
		);
		$sql = "SELECT * FROM " . BB_CONFIG;
		if (!$result = DB()->sql_query($sql)) {
			bb_die('Could not query config information in admin_board');
		} else {
			while ($row = DB()->sql_fetchrow($result)) {
				$config_name = $row['config_name'];
				$config_value = $row['config_value'];
				$default_config[$config_name] = $config_value;

				$new[$config_name] = isset($_POST[$config_name]) ? $_POST[$config_name] : $default_config[$config_name];

				if (isset($_POST['submit']) && $row['config_value'] != $new[$config_name]) {
					bb_update_config(array($config_name => $new[$config_name]));
				}
			}
			if (isset($_POST['submit'])) {
				bb_die($lang['AD_PERS_CONFIG_UPDATED'] . $return_links[$mode] . $return_links['list']);
			}
		}

		$template->assign_vars(array(
			'PERS_CONFIG' => true,

			'PERS_ENABLE_MOD' => ($new['pers_enable']) ? true : false,
			'PERS_REPLACE_TEXT' => htmlspecialchars(stripslashes($new['pers_repl_text'])),
			'PERS_PER_PAGE' => $new['pers_per_page'],
			'PERS_IDKP_PARSER' => $new['pers_idkp_list'],
			'PERS_PARSER_PHOTO_DIR' => $new['pers_photo_dir'],
		));
		break;
	default:
		$sql = "SELECT pers_id, pers_runame, pers_enname FROM " . BB_PERSON . " ORDER BY pers_id ASC LIMIT $page, 100";

		$template->assign_vars(array(
			'TPL_PERS_LIST' => true,
			'U_ACTORS' => "admin_person.php",
		));

		if ($act_rows = DB()->fetch_rowset($sql)) {
			$template->assign_vars(array(
				'TPL_PERS_LIST_NO_ERROR' => true,
			));

			foreach ($act_rows as $i => $rows) {
				$act_id = $rows['pers_id'];
				if (!$rows['pers_runame']) {
					$act_name = (!$rows['pers_enname'] ? $rows['pers_runame'] : $rows['pers_enname']);
				} else {
					$act_name = (!$rows['pers_runame'] ? $rows['pers_enname'] : $rows['pers_runame']);
				}

				$template->assign_block_vars('person_list', array(
					'ROW_CLASS' => !($i % 2) ? 'row2' : 'row1',
					'PERS_ID' => $act_id,
					'PERS_NAME' => $act_name,

					'U_ACTOR' => "admin_person.php?id=" . $act_id,
					'U_PERS_IDIT' => "admin_person.php?mode=edit&amp;id=" . $act_id,
				));
			}

			$sql = "SELECT COUNT(pers_id) AS total FROM " . BB_PERSON;
			if (!$result = DB()->sql_query($sql)) {
				bb_die('Error getting total actors');
			}
			if ($total = DB()->sql_fetchrow($result)) {
				$paginationurl = 'admin_person.php';

				$total_actors = $total['total'];
				generate_pagination($paginationurl, $total_actors, 100, $page);
			}
			DB()->sql_freeresult($result);

		} else {
			$template->assign_vars(array(
				'TPL_PERS_LIST_NO_ERROR' => false,
				'NO_ACTORS_LIST' => '<br />' . $lang['PERS_NO_LIST'] . '<br /><br /><a href="admin_person.php?mode=add">' . $lang['AD_PERS_ADD_NEW'] . '</a><br />'
			));
		}
		break;
}

$template->assign_vars(array(
	'U_PERS_ADD' => "admin_person.php?mode=add",
	'U_PERS_CONF' => "admin_person.php?mode=config",
	'U_PERS_POST_UP' => "admin_person.php?mode=up",
	'U_PERS_PARSER' => "admin_person.php?mode=parser",
));

print_page('admin_person.tpl', 'admin');
