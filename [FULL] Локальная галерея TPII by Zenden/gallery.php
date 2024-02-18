<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'gallery');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');
require(INC_DIR . 'functions_upload.php');

// Start session management
$user->session_start(array('req_login' => true));

$start = intval(request_var('start', 0));
$imgfile = $i = $imgs_count = $user_dir_size = 0;
$errors = $imgs_all = $imgs_tags_img = $imgs_tags_thumb = $imgs_tags_spoiler = array();

if (!empty($_FILES['imgfile']['name']) && $bb_cfg['imgs']['up_allowed']) {
	if (count($_FILES['imgfile']['name']) > $bb_cfg['imgs']['limit_imgs']) {
		bb_die(sprintf($lang['IMGS_LIMIT'], $bb_cfg['imgs']['limit_imgs']));
	}

	$upload = new upload_common();

	for ($i; $i < count($_FILES['imgfile']['name']); $i++) {
		$img = array(
			'name' => $_FILES['imgfile']['name'][$i],
			'type' => $_FILES['imgfile']['type'][$i],
			'size' => $_FILES['imgfile']['size'][$i],
			'tmp_name' => $_FILES['imgfile']['tmp_name'][$i],
			'error' => $_FILES['imgfile']['error'][$i],
		);

		if ($upload->init($bb_cfg['imgs'], $img) and $upload->store('imgfile', $userdata)) {
			$path = get_imgfile_path($userdata['user_id'], $upload->file_ext_id, $userdata['user_id'] . $upload->file['name'] . $upload->file['size']);
			$thumb = get_imgfile_path($userdata['user_id'], $upload->file_ext_id, $userdata['user_id'] . $upload->file['name'] . $upload->file['size'], true);

			$template->assign_block_vars('upload', array(
				'ID' => $i,
				'IMG' => $path,
				'THUMB' => (isset($_POST['thumb'])) ? $thumb : false,
			));

			$imgs_all[] = FULL_URL . $path;
			$imgs_tags_img[] = '[img]' . FULL_URL . $path . '[/img]';
			$imgs_tags_thumb[] = '[url=' . FULL_URL . $path . '][img]' . FULL_URL . $thumb . '[/img][/url]';
			$imgs_tags_spoiler[] = isset($_POST['thumb']) ? '[url=' . FULL_URL . $path . '][img]' . FULL_URL . $thumb . '[/img][/url]' : '[img]' . FULL_URL . $path . '[/img]';

			$upload->thumb($path, isset($_POST['thumb']));
		} else {
			$errors = array_merge($errors, $upload->errors);
		}
	}
}

if (isset($_GET['dir'])) {
	if ($_GET['dir'] == $userdata['user_id']) {
		$dir = array_slice(scandir($bb_cfg['imgs']['upload_path'] . '/' . $userdata['user_id']), 2);
		$dir_page = array_slice($dir, $start, $bb_cfg['imgs']['imgs_page']);

		if ($user_dir = @opendir(BB_ROOT . $bb_cfg['imgs']['upload_path'] . '/' . $userdata['user_id'])) {
			while ($dir_size = @readdir($user_dir)) {
				if ($dir_size != '.' && $dir_size != '..') {
					$user_dir_size += @filesize(BB_ROOT . $bb_cfg['imgs']['upload_path'] . '/' . $userdata['user_id'] . '/' . $dir_size);
				}
			}
			@closedir($user_dir);

			$user_dir_size = humn_size($user_dir_size);
		} else {
			$user_dir_size = $lang['NOT_AVAILABLE'];
		}

		$template->assign_vars(array(
			'USER_DIR' => true,
			'USER_DIR_SIZE' => $user_dir_size
		));

		foreach ($dir_page as $file) {
			$url_imgs = $bb_cfg['imgs']['upload_path'] . '/' . $userdata['user_id'] . '/' . $file;

			if (is_file($url_imgs)) $imgs_count++;

			$template->assign_block_vars('dir', array(
				'ID' => $imgs_count,
				'IMG' => $url_imgs,
				'IMG_SIZE' => humn_size(filesize($url_imgs)),
				'IMG_TIME' => bb_date(filemtime($url_imgs)),
			));
		}
		generate_pagination('?dir=' . $userdata['user_id'], count($dir), $bb_cfg['imgs']['imgs_page'], $start);
	}
}

$template->assign_vars(array(
	'ERROR_MESSAGE' => ($errors) ? join('<br />', array_unique($errors)) : '',
	'IMG_MAX_SIZE' => humn_size($bb_cfg['imgs']['max_size']),
	'IMG_ALLOWED_EXT' => implode(', ', $bb_cfg['imgs']['allowed_ext']),
	'IMGS_ALL_TAGS' => ($i > 1) ? true : false,
	'IMGS_ALL' => implode(' ', $imgs_all),
	'IMGS_TAGS_IMG' => implode(' ', $imgs_tags_img),
	'IMGS_TAGS_THUMB' => isset($_POST['thumb']) ? implode(' ', $imgs_tags_thumb) : false,
	'IMGS_TAGS_SPOILER' => implode(' ', $imgs_tags_spoiler),
	'COUNT_IMGS' => $imgs_count,
));

print_page('gallery.tpl');
