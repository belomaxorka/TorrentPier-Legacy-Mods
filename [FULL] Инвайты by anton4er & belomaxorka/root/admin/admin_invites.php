<?php

if (!empty($setmodules)) {
	$filename = basename(__FILE__);
	$module['MODS']['INVITE_ADMIN_RULES'] = $filename . '?mode=rules';
	$module['MODS']['INVITE_ADMIN_HIST'] = $filename . '?mode=history';
	return;
}

$mode = '';
if (isset($_POST['mode']) || isset($_GET['mode'])) {
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];
}

require('./pagestart.php');
require(INC_DIR . 'functions_group.php');

if (isset($_POST['change_rule'])) {
	$rule_change_list = get_var('rule_change_list', array(0));
	$rule_user_rating_list = get_var('rule_user_rating_list', array(0));
	$rule_user_age_list = get_var('rule_user_age_list', array(0));
	$rule_user_group_list = get_var('rule_user_group_list', array(0));
	$rule_invites_count_list = get_var('rule_invites_count_list', array(0));

	$rules = array();
	for ($i = 0; $i < sizeof($rule_change_list); $i++) {
		$rules['_' . $rule_change_list[$i]]['user_rating'] = intval($rule_user_rating_list[$i]);
		$rules['_' . $rule_change_list[$i]]['user_age'] = intval($rule_user_age_list[$i]);
		$rules['_' . $rule_change_list[$i]]['user_group'] = intval($rule_user_group_list[$i]);
		$rules['_' . $rule_change_list[$i]]['invites_count'] = intval($rule_invites_count_list[$i]);
	}

	$sql = 'SELECT * FROM ' . BB_INVITE_RULES . ' ORDER BY rule_id';
	if (!($result = DB()->sql_query($sql))) {
		bb_die('Could not get a list of rules for the Invite: ' . __LINE__ . ',' . __FILE__ . ',' . $sql);
	}

	$num_rows = DB()->num_rows($result);
	$rule_row = DB()->sql_fetchrowset($result);
	DB()->sql_freeresult($result);

	if ($num_rows > 0) {
		for ($i = 0; $i < sizeof($rule_row); $i++) {
			if (intval($rule_row[$i]['user_rating']) != intval($rules['_' . $rule_row[$i]['rule_id']]['user_rating']) || intval($rule_row[$i]['user_age']) != intval($rules['_' . $rule_row[$i]['rule_id']]['user_age']) || intval($rule_row[$i]['user_group']) != intval($rules['_' . $rule_row[$i]['rule_id']]['user_group']) || intval($rule_row[$i]['invites_count']) != intval($rules['_' . $rule_row[$i]['rule_id']]['invites_count'])) {
				$sql_ary = array(
					'user_rating' => (int)$rules['_' . $rule_row[$i]['rule_id']]['user_rating'],
					'user_age' => (int)$rules['_' . $rule_row[$i]['rule_id']]['user_age'],
					'user_group' => (int)$rules['_' . $rule_row[$i]['rule_id']]['user_group'],
					'invites_count' => (int)$rules['_' . $rule_row[$i]['rule_id']]['invites_count'],
				);

				$sql = 'UPDATE ' . BB_INVITE_RULES . ' SET ' . attach_mod_sql_build_array('UPDATE', $sql_ary) . ' WHERE `rule_id` = ' . (int)$rule_row[$i]['rule_id'];
				if (!DB()->sql_query($sql)) {
					bb_die('Could not save data: ' . __LINE__ . ',' . __FILE__ . ',' . $sql);
				}
			}
		}
	}

	// Удаление правил
	$rule_id_list = get_var('rule_id_list', array(0));
	$rule_id_sql = implode(', ', $rule_id_list);
	if ($rule_id_sql != '') {
		$sql = 'DELETE FROM ' . BB_INVITE_RULES . ' WHERE rule_id IN (' . $rule_id_sql . ')';
		if (!$result = DB()->sql_query($sql)) {
			bb_die('Could not delete rule: ' . __LINE__ . ',' . __FILE__ . ',' . $sql);
		}
	}
}

if (isset($_POST['add_rule'])) {
	$rule_user_rating = get_var('add_rule_user_rating', '');
	$rule_user_age = get_var('add_rule_user_age', '');
	$rule_user_group = $_POST['add_rule_user_group'];
	$rule_invites_count = get_var('add_rule_invites_count', '');
	$sql_ary = array(
		'user_rating' => (int)$rule_user_rating,
		'user_age' => (int)$rule_user_age,
		'user_group' => (int)$rule_user_group,
		'invites_count' => (int)$rule_invites_count
	);

	$sql = 'INSERT INTO ' . BB_INVITE_RULES . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);
	if (!DB()->sql_query($sql)) {
		bb_die('Could not add rule: ' . __LINE__ . ',' . __FILE__ . ',' . $sql);
	}
}

switch ($mode) {
	case 'rules':
		$template->assign_vars(array(
			'TPL_INVITES_RULES' => true,
			'TPL_INVITES_HISTORY' => false,
			'S_ADD_GROUP_SELECT' => get_groupname('add_rule_user_group', '0', false),
			'S_RULES_ACTION' => "admin_invites.php?mode=rules"
		));

		$sql = 'SELECT * FROM ' . BB_INVITE_RULES . ' ORDER BY `invites_count`';
		if (!($result = DB()->sql_query($sql))) {
			bb_die('Could not get a list of rules for the Invite: ' . __LINE__ . ',' . __FILE__ . ',' . $sql);
		}

		$rule_row = DB()->sql_fetchrowset($result);
		$num_rule_row = DB()->num_rows($result);
		DB()->sql_freeresult($result);

		if ($num_rule_row > 0) {
			$rule_row = sort_multi_array($rule_row, 'invites_count', 'ASC');
			for ($i = 0; $i < $num_rule_row; $i++) {
				$template->assign_block_vars('rule_row', array(
					'RULE_ID' => $rule_row[$i]['rule_id'],
					'USER_RATING' => $rule_row[$i]['user_rating'],
					'USER_AGE' => $rule_row[$i]['user_age'],
					'USER_GROUP' => $rule_row[$i]['user_group'],
					'S_GROUP_SELECT' => get_groupname('rule_user_group_list[]', $rule_row[$i]['user_group'], false),
					'INVITES_COUNT' => $rule_row[$i]['invites_count']
				));
			}
		}
		break;

	case 'history':
		$template->assign_vars(array(
			'TPL_INVITES_RULES' => false,
			'TPL_INVITES_HISTORY' => true
		));

		$sql = 'SELECT * FROM ' . BB_INVITES . ' ORDER BY `generation_date` DESC';
		if (!($result = DB()->sql_query($sql))) {
			bb_die('Could not get a list of invites: ' . __LINE__ . ',' . __FILE__ . ',' . $sql);
		}

		$invite_row = DB()->sql_fetchrowset($result);
		$num_invite_row = DB()->num_rows($result);
		DB()->sql_freeresult($result);

		if ($num_invite_row > 0) {
			for ($i = 0; $i < $num_invite_row; $i++) {
				$user_data = get_userdata($invite_row[$i]['user_id']);
				$new_user_data = get_userdata($invite_row[$i]['new_user_id']);
				$template->assign_block_vars('invite_row', array(
					'USER' => profile_url($user_data),
					'GENERATION_DATE' => bb_date($invite_row[$i]['generation_date'], 'd-M-y H:i'),
					'INVITE_CODE' => $invite_row[$i]['invite_code'],
					'ACTIVE' => ($invite_row[$i]['active'] == '1') ? $lang['INVITE_ACTIV_YES'] : $lang['INVITE_ACTIV_NO'],
					'NEW_USER' => ($invite_row[$i]['active'] == '1') ? '-' : profile_url($new_user_data),
					'ACTIVATION_DATE' => ($invite_row[$i]['active'] == '1') ? '-' : bb_date($invite_row[$i]['activation_date'], 'd-M-y H:i'),
				));
			}
		}
		break;

	default:
		bb_die('[Invites] Invalid mode: ' . $mode);
}

print_page('admin_invites.tpl', 'admin');
