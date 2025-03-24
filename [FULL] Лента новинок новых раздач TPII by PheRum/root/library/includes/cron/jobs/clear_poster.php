<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$clear_dir = BB_ROOT . 'data/thumbnails'; // Директория в которой хранятся миниатюры

$dir = opendir($clear_dir);
while (($file = readdir($dir))) {
	if ($file === '.keep') {
		continue;
	}
	if (is_file($clear_dir . '/' . $file)) {
		unlink($clear_dir . '/' . $file);
	}
}
