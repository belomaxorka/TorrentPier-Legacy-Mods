<?php

define('IN_PHPBB', true);
define('BB_SCRIPT', 'warning');
require('./common.php');

$start  = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;
$id = (int) request_var('id', '');
$user_id = (int) request_var(POST_USERS_URL, '');

$per_page = $bb_cfg['topics_per_page'];
$page_cfg['usesorter']   = true;
$page_cfg['include_bbcode_js'] = true;

// Start session
$user->session_start();

if(!$bb_cfg['warning']['enabled']) bb_die('Запреты отключены.');

if($id)
{
	// Проверка на доступ
	if(!IS_AM) bb_die($lang['NOT_MODERATOR']);

	$warning = DB()->fetch_row("SELECT w.*, u.username, u.user_rank, u.avatar_ext_id, u.user_opt
		FROM ". BB_WARNINGS ." w, ". BB_USERS ." u
		WHERE w.user_id = u.user_id
			AND w.id = $id
		GROUP BY w.id DESC
		LIMIT 1");
    if(!$warning) bb_die('Такого запрета не существует.');

	$warning_user = profile_url($warning);

	$warning_type = array();
	foreach ($bb_cfg['warning']['type'] as $type => $key)
	{
		$warning_type[$key] = $type;
	}

    $warning_time = array();
	foreach ($bb_cfg['warning']['time'] as $time => $key)
	{
		$warning_time[$key] = $time;
	}

	$warning_auth = array();
	foreach ($bb_cfg['warning']['auth'] as $auth => $key)
	{
		$warning_auth[$key] = $auth;
	}

	$time = ($warning['time_end']) ? ($warning['time_end'] - $warning['time_start']) / $warning['term'] : $bb_cfg['warning']['time_select'];

    switch($warning['type'])
    {
    	case 'topic':
	    	$text = '<a class="txtb" href="'. POST_URL . $warning['type_id'] .'#'. $warning['type_id'] .'" onclick="ajax.view_post('. $warning['type_id'] .', this); return false;">это сообщение</a>';
    	break;

    	case 'ещё чего нить':
	    	$text = '';
    	break;

    	default:
	    	$text = 'через профиль пользователя';
    	break;
    }

	$template->assign_vars(array(
		'PAGE_TITLE'  => 'Редактирование запрета',
		'TERM'         => $warning['term'],
		'TIME'         => build_select('warnings-time', $warning_time, $time, null, null),
		'WARNING'      => build_select('warnings-type', $warning_type, $warning['warning'], null, null),
		'ID'           => $warning['id'],
		'USER_ID'      => $warning['user_id'],
		'TYPE'         => $text,
		'TYPE_ID'      => $warning['type_id'],
		'REASON'       => $warning['reason'],
		'WARNING_USER' => $warning_user,
		'AUTH'         => build_select('warnings-auth', $warning_auth, $warning['auth'], null, null),
		'EDIT'         => true,
		'AVATAR'       => get_avatar($warning['user_id'], $warning['avatar_ext_id'], !bf($warning['user_opt'], 'user_opt', 'dis_avatar')),
	));
}
else
{
	$where_user_id = $and_user_id = '';
	if($user_id)
	{
	    $where_user_id = "WHERE user_id = $user_id";
	    $and_user_id = "AND w.user_id = $user_id";
	}

	$row = DB()->fetch_row("SELECT COUNT(id) as count FROM ". BB_WARNINGS ."  $where_user_id");
	$warning_count = ($row) ? $row['count'] : 0;

	if ($warning_count)
	{
	    $sql = DB()->fetch_rowset("SELECT w.*, u.username, u.user_id, u.user_rank,
			    u2.username as w_username, u2.user_id as w_user_id, u2.user_rank as w_user_rank
			FROM ". BB_WARNINGS ." w, ". BB_USERS ." u, ". BB_USERS ." u2
			WHERE w.user_id = u.user_id
				AND w.poster_id = u2.user_id
				$and_user_id
			GROUP BY w.id DESC
			LIMIT $start, $per_page");

		foreach ($sql as $i => $warning)
		{
			$warning_user = profile_url($warning);

			$poster_user = profile_url(array('username' => $warning['w_username'], 'user_id' => $warning['w_user_id'], 'user_rank' => $warning['w_user_rank']));

			$type = '<b>'. $bb_cfg['warning']['type'][$warning['warning']]. '</b>: ';
			if(($warning['time_end'] > TIMENOW && $warning['auth'] == 1) || $warning['auth'] == 3)
			{
				$time = ($warning['time_end'] > TIMENOW) ? 'Осталось '. delta_time($warning['time_end']) : $bb_cfg['warning']['auth'][0];
			}
			else
			{
				$time = $bb_cfg['warning']['auth'][0];
			}

            switch($warning['type'])
			{
				case 'topic':
					$text = '<a class="txtb" href="'. POST_URL . $warning['type_id'] .'#'. $warning['type_id'] .'" onclick="ajax.view_post('. $warning['type_id'] .', this); return false;">это сообщение</a>';
				break;

				case 'ещё чего нить':
					$text = '';
				break;

				default:
					$text = 'через профиль пользователя';
				break;
			}

			$template->assign_block_vars('warning', array(
				'ROW_CLASS'         => (!($i % 2)) ? 'row1' : 'row2',
				'WARNING_ID'        => $warning['id'],
				'WARNING'           => $type . $time,
				'TYPE'              => $text,
				'TYPE_ID'           => $warning['type_id'],
				'WARNING_TEXT'      => stripslashes($warning['reason']),
				'WARNING_USER'      => $warning_user,
				'POSTER_USER'       => $poster_user,
				'AUTH'              => (IS_ADMIN || ($userdata['user_id'] == $warning['poster_id'])),
			));
		}

		$warning_url = ($user_id) ? "warnings.php?warnings&u=$user_id" : 'warnings.php?warnings';

		generate_pagination($warning_url, $warning_count, $per_page, $start);

		$template->assign_vars(array(
			'PAGE_TITLE'  => 'Список провинившихся пользователей',
			'WARNINGS'    => true,
		));
	}
	else
	{
		if($user_id)
		{
			bb_die('Этот пользователь не нарушал законы трекера =)');
		}
		else
		{
			bb_die('Возрадуйтесь, ваш трекер является эталоном порядка xD');
		}
	}
}
print_page('warnings.tpl');
