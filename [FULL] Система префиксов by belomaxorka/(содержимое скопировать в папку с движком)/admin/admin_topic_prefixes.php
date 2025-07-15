<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['FORUMS']['TOPIC_PREFIXES'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

if (isset($_GET['mode']) || isset($_POST['mode'])) {
    $mode = $_GET['mode'] ?? $_POST['mode'];
} else {
    //
    // These could be entered via a form button
    //
    if (isset($_POST['add'])) {
        $mode = 'add';
    } elseif (isset($_POST['save'])) {
        $mode = 'save';
    } else {
        $mode = '';
    }
}

if ($mode == 'delete' && isset($_POST['cancel'])) {
    $mode = '';
}

if ($mode != '') {
    if ($mode == 'edit' || $mode == 'add') {
        //
        // They want to add a new prefix, show the form.
        //
        $prefix_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $s_hidden_fields = '';

        if ($mode == 'edit') {
            if (empty($prefix_id)) {
                bb_die($lang['MUST_SELECT_PREFIX']);
            }

            $sql = 'SELECT * FROM ' . BB_PREFIXES . " WHERE prefix_id = $prefix_id";
            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not obtain prefixes data #1');
            }

            $prefix_info = DB()->sql_fetchrow($result);
            $s_hidden_fields .= '<input type="hidden" name="id" value="' . $prefix_id . '" />';
        }

        $s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';

        $template->assign_vars([
            'TPL_PREFIX_EDIT' => true,

            'PREFIX_NAME' => !empty($prefix_info['prefix_name']) ? $prefix_info['prefix_name'] : '',
            'PREFIX_DESC' => !empty($prefix_info['prefix_description']) ? $prefix_info['prefix_description'] : '',
            'PREFIX_COLOR' => !empty($prefix_info['prefix_color']) ? $prefix_info['prefix_color'] : '',

            'S_PREFIX_ACTION' => 'admin_topic_prefixes.php',
            'S_HIDDEN_FIELDS' => $s_hidden_fields
        ]);
    } elseif ($mode == 'save') {
        //
        // Ok, they sent us our info, let's update it.
        //
        $prefix_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $prefix_name = $_POST['name'] ?? '';
        $prefix_description = $_POST['description'] ?? '';
        $prefix_color = $_POST['color'] ?? '';

        if ($prefix_name == '') {
            bb_die($lang['MUST_SELECT_PREFIX']);
        }

        if ($prefix_id) {
            $sql = 'UPDATE ' . BB_PREFIXES . "
				SET prefix_name = '" . DB()->escape($prefix_name) . "',
					prefix_description = '" . DB()->escape($prefix_description) . "',
					prefix_color = '" . DB()->escape($prefix_color) . "'
				WHERE prefix_id = $prefix_id";

            $message = $lang['PREFIX_UPDATED'];
        } else {
            $sql = 'INSERT INTO ' . BB_PREFIXES . " (prefix_name, prefix_description, prefix_color)
				VALUES ('" . DB()->escape($prefix_name) . "', '" . DB()->escape($prefix_description) . "', '" . DB()->escape($prefix_color) . "')";

            $message = $lang['PREFIX_ADDED'];
        }

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not update / insert into prefixes table');
        }

        $message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_PREFIXADMIN'], '<a href="admin_topic_prefixes.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');
        $datastore->update('prefixes');
        bb_die($message);
    } elseif ($mode == 'delete') {
        //
        // Ok, they want to delete their prefix
        //

        $confirmed = isset($_POST['confirm']);
        if (isset($_POST['id']) || isset($_GET['id'])) {
            $prefix_id = isset($_POST['id']) ? (int)$_POST['id'] : (int)$_GET['id'];
        } else {
            $prefix_id = 0;
        }

        if ($confirmed) {
            if ($prefix_id) {
                $sql = 'DELETE FROM ' . BB_PREFIXES . " WHERE prefix_id = $prefix_id";
                if (!$result = DB()->sql_query($sql)) {
                    bb_die('Could not delete prefix data');
                }

                $datastore->update('prefixes');
                bb_die($lang['PREFIX_REMOVED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_PREFIXADMIN'], '<a href="admin_topic_prefixes.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
            } else {
                bb_die($lang['MUST_SELECT_PREFIX']);
            }
        } else {
            $hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';
            $hidden_fields .= '<input type="hidden" name="id" value="' . $prefix_id . '" />';

            print_confirmation([
                'FORM_ACTION' => 'admin_topic_prefixes.php',
                'HIDDEN_FIELDS' => $hidden_fields,
            ]);
        }
    } else {
        bb_die('Invalid mode: ' . $mode);
    }
} else {
    //
    // Show the default page
    //
    $sql = 'SELECT * FROM ' . BB_PREFIXES . ' ORDER BY prefix_name';
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not obtain prefixes data #2');
    }
    $prefix_count = DB()->num_rows($result);
    $prefix_rows = DB()->sql_fetchrowset($result);

    $template->assign_vars([
        'TPL_PREFIX_LIST' => true,
        'S_PREFIX_ACTION' => 'admin_topic_prefixes.php'
    ]);

    for ($i = 0; $i < $prefix_count; $i++) {
        $prefix_name = $prefix_rows[$i]['prefix_name'];
        $prefix_id = $prefix_rows[$i]['prefix_id'];

        $row_class = !($i % 2) ? 'row1' : 'row2';

        $template->assign_block_vars('prefixes', [
            'ROW_CLASS' => $row_class,
            'PREFIX_NAME' => $prefix_name,
            'PREFIX_DESCRIPTION' => $prefix_rows[$i]['prefix_description'],
            'PREFIX_COLOR' => $prefix_rows[$i]['prefix_color'],

            'U_PREFIX_EDIT' => "admin_topic_prefixes.php?mode=edit&amp;id=$prefix_id",
            'U_PREFIX_DELETE' => "admin_topic_prefixes.php?mode=delete&amp;id=$prefix_id"
        ]);
    }
}

print_page('admin_topic_prefixes.tpl', 'admin');
