<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
	$module['GENERAL']['LOGO'] = basename(__FILE__);
	return;
}

require __DIR__ . '/pagestart.php';

$logo_dir = BB_PATH . '/styles/images/logo/';
$logo_url = 'styles/images/logo/';

// Delete logo
if (isset($_POST['delete_logo'], $_POST['confirm_delete'])) {
	$logo_to_delete = isset($_POST['logo_to_delete']) ? $_POST['logo_to_delete'] : '';
	$default_logo = 'styles/images/logo/logo.png';

	if ($logo_to_delete && $logo_to_delete !== $default_logo) {
		$delete_path = BB_PATH . '/' . $logo_to_delete;

		if (file_exists($delete_path) && is_file($delete_path)) {
			// Check if file is in logo directory
			$real_delete_path = realpath($delete_path);
			$real_logo_dir = realpath($logo_dir);

			// Replace str_starts_with() with substr() or strpos()
			if ($real_delete_path && $real_logo_dir && strpos($real_delete_path, $real_logo_dir) === 0) {
				if (unlink($delete_path)) {
					// If deleted logo was current, reset to default
					$current = isset($bb_cfg['site_logo']) ? $bb_cfg['site_logo'] : '';
					if ($current === $logo_to_delete) {
						bb_update_config(array('site_logo' => $default_logo));
					}
					bb_die($lang['LOGO_DELETED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="admin_logo.php">', '</a>'));
				}
			}
		}
	}

	bb_die($lang['LOGO_DELETE_ERROR'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="admin_logo.php">', '</a>'));
}

// Upload new logo
if (isset($_FILES['logo_upload']) && $_FILES['logo_upload']['error'] === UPLOAD_ERR_OK) {
	$allowed_ext = array('png', 'jpg', 'jpeg', 'gif', 'svg', 'webp');
	$file_ext = strtolower(pathinfo($_FILES['logo_upload']['name'], PATHINFO_EXTENSION));

	if (in_array($file_ext, $allowed_ext)) {
		$filename = 'logo_' . time() . '.' . $file_ext;
		$upload_path = $logo_dir . $filename;

		if (move_uploaded_file($_FILES['logo_upload']['tmp_name'], $upload_path)) {
			bb_update_config(array('site_logo' => $logo_url . $filename));
			bb_die($lang['LOGO_UPLOADED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="admin_logo.php">', '</a>'));
		}
	}
} elseif (isset($_POST['submit'])) {
	$logo_path = (isset($_POST['logo_select'])) ? trim($_POST['logo_select']) : '';

	if ($logo_path) {
		bb_update_config(array('site_logo' => $logo_path));
		bb_die($lang['CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
	}
}

$current_logo = (isset($bb_cfg['site_logo'])) ? trim($bb_cfg['site_logo']) : 'styles/images/logo/logo.png';

$logos = array();
if (is_dir($logo_dir)) {
	$files = scandir($logo_dir);
	foreach ($files as $file) {
		if ($file !== '.' && $file !== '..' && is_file($logo_dir . $file)) {
			$logos[] = $logo_url . $file;
		}
	}
}

$default_logo = 'styles/images/logo/logo.png';

$template->assign_vars(array(
	'S_LOGO_ACTION' => 'admin_logo.php',
	'CURRENT_LOGO' => $current_logo,
	'CURRENT_LOGO_PREVIEW' => '../' . $current_logo,
	'DEFAULT_LOGO' => $default_logo,
));

foreach ($logos as $logo) {
	$template->assign_block_vars('logos', array(
		'LOGO_PATH' => $logo,
		'LOGO_PATH_PREVIEW' => '../' . $logo,
		'LOGO_NAME' => basename($logo),
		'SELECTED' => ($logo === $current_logo) ? 'checked' : '',
		'IS_DEFAULT' => ($logo === $default_logo),
	));
}

print_page('admin_logo.tpl', 'admin');
