<?php

set_time_limit(0);

// ACP Header - START
if (!empty($setmodules))
{
	$module['Парсеры']['Rutor.info'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(CLASS_DIR.'snoopy.php');

$mode = request_var('mode', '');

$return_links = array(
	'index' => '<br /><br />'. sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'),
	'config' => '<br /><br />'. sprintf($lang['CLICK_RETURN_CONFIG'], '<a href="admin_rutor.php">', '</a>'),
);

// Get allowed for searching forums list
if (!$forums = $datastore->get('cat_forums'))
{
	$datastore->update('cat_forums');
	$forums = $datastore->get('cat_forums');
}
$cat_title_html = $forums['cat_title_html'];
$forum_name_html = $forums['forum_name_html'];

$template->assign_vars(array(
	'ALL_CATEGORIE'         => false,
));

$sql = DB()->fetch_rowset("SELECT f.*, u.username, u.user_rank FROM rutor_categories f LEFT JOIN ". BB_USERS ." u ON(f.user_id = u.user_id)");
if($sql)
{
	for ($i = (count($sql) - 1); $i >= 0; $i--)
	{
		$row = $sql[$i];
		$template->assign_block_vars('r_f', array(
			'NOMER'         => ($i + 1),
			'CLASS'         => !($i % 2) ? 'row1' : 'row2',
			'FORUM'         => '<a href="'. FORUM_URL . $row['forum'] .'">'. $forum_name_html[$row['forum']] .'</a>',
			'CATEGORIE'     => $row['categorie'],
			'USER'          => ($row['user_id']) ? profile_url($row) : 'Bot',
			'MD5'           => md5($row['categorie']),
			'ALL_CATEGORIE' => $row['all_categorie'],
			'ACTIVE'        => $row['active'],
		));
	}
}

if (IS_ADMIN)
{
	$forum_select_mode = 'admin';
}
else
{
	$not_auth_forums_csv = $user->get_not_auth_forums(AUTH_VIEW);
	$forum_select_mode = explode(',', $not_auth_forums_csv);
}

$template->assign_vars(array(
	'FORUM'    => get_forum_select($forum_select_mode, 'forum', 0),
));

$errors = array();

if($mode == 'add_categories')
{
    $categorie     = (string) urldecode(request_var('categorie', ''));
	$forum         = (int) request_var('forum', '');
    $user_id       = (string) request_var('user_id', '');
    $all_categorie = (int) request_var('all_categorie', 0);

	if(!$categorie) $errors[] = 'Вы не указали откуда парсить';
	if(!$forum)     $errors[] = 'Вы не указали куда сохранять';

    if(!$errors)
    {
		if(preg_match('#rutor.info/tag/#', $categorie)) {}
		elseif(preg_match('#rutor.info/search/0/#', $categorie)) {}
		else $errors[] = 'Неверная ссылка, адрес должен быть такого типа http://rutor.info/tag/ или http://rutor.info/search/0/';
    }

    if($user_id)
    {
    	$row = get_userdata ($user_id);
    	if(!$row) $errors[] = 'Такого пользователя не существует';
    	else $user_id = $row['user_id'];
    }

    if(!DB()->fetch_row("SELECT categorie FROM rutor_categories WHERE categorie = '$categorie'") && !$errors)
	{
		$data = array(
			'categorie'     => $categorie,
			'forum'         => $forum,
			'user_id'       => $user_id,
		    'all_categorie' => $all_categorie,
		);
		$sql = DB()->build_array('INSERT', $data);
		DB()->query("INSERT INTO rutor_categories $sql");

		bb_die($lang['CONFIG_UPDATED'] . $return_links['config'] . $return_links['index']);
	}
	elseif(!$errors)
	{
		$errors[] = 'По данной ссылке уже парсятся раздачи';
	}

	$template->assign_vars(array(
		'PAGE_TITLE'         => 'Ошибка при добавление форума',
		'ERROR_MESSAGE'      => ($errors) ? join('<br />', array_unique($errors)) : '',
		'CATEGORIE'          => $categorie,
		'USER'               => $user_id,
		'FORUM'              => get_forum_select($forum_select_mode, 'forum', $forum),
		'ALL_CATEGORIE'      => !$all_categorie,
	));
}

print_page('admin_rutor.tpl', 'admin');

