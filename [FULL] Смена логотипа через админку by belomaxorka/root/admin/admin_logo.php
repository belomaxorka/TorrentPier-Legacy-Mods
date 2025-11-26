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

if (isset($_FILES['logo_upload']) && $_FILES['logo_upload']['error'] === UPLOAD_ERR_OK) {
    $allowed_ext = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
    $file_ext = strtolower(pathinfo($_FILES['logo_upload']['name'], PATHINFO_EXTENSION));
    
    if (in_array($file_ext, $allowed_ext)) {
        $filename = 'logo_' . time() . '.' . $file_ext;
        $upload_path = $logo_dir . $filename;
        
        if (move_uploaded_file($_FILES['logo_upload']['tmp_name'], $upload_path)) {
            bb_update_config(['site_logo' => $logo_url . $filename]);
            bb_die($lang['LOGO_UPLOADED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="admin_logo.php">', '</a>'));
        }
    }
} elseif (isset($_POST['submit'])) {
    $logo_path = $_POST['logo_select'] ?? '';
    
    if ($logo_path) {
        bb_update_config(['site_logo' => $logo_path]);
        bb_die($lang['CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

$current_logo = $bb_cfg['site_logo'] ?? 'styles/images/logo/logo.png';

$logos = [];
if (is_dir($logo_dir)) {
    $files = scandir($logo_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($logo_dir . $file)) {
            $logos[] = $logo_url . $file;
        }
    }
}

$template->assign_vars([
    'S_LOGO_ACTION' => 'admin_logo.php',
    'CURRENT_LOGO' => $current_logo,
    'CURRENT_LOGO_PREVIEW' => '../' . $current_logo,
]);

foreach ($logos as $logo) {
    $template->assign_block_vars('logos', [
        'LOGO_PATH' => $logo,
        'LOGO_PATH_PREVIEW' => '../' . $logo,
        'LOGO_NAME' => basename($logo),
        'SELECTED' => ($logo === $current_logo) ? 'checked' : '',
    ]);
}

print_page('admin_logo.tpl', 'admin');
