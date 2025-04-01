<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
	die(basename(__FILE__));
}

global $bb_cfg, $lang;

$upload_dir = $bb_cfg['ajax_upload_posting_images_path'];
$posterLink = $screenshotsLink = [];

if (!empty($_FILES['poster_images'])) {
	$posterFiles = $_FILES['poster_images'];
	foreach ($posterFiles['tmp_name'] as $key => $tmpName) {
		if ($posterFiles['size'][$key] > $bb_cfg['ajax_upload_posting_images_size_limit']) {
			$this->ajax_die($lang['POSTING_IMAGES_FILE_TOO_LARGE'] . humn_size($bb_cfg['ajax_upload_posting_images_size_limit']));
		}

		$destination = $upload_dir . 'poster_' . TIMENOW . '_' . prepareFile($posterFiles['name'][$key]);
		if (!convertToWebP($tmpName, $destination)) {
			$this->ajax_die($lang['POSTING_IMAGES_ERROR']);
		}
		$posterLink[] = make_url(hide_bb_path($destination));
	}
}

if (!empty($_FILES['screenshots_images'])) {
	$screenshotFiles = $_FILES['screenshots_images'];
	foreach ($screenshotFiles['tmp_name'] as $key => $tmpName) {
		$destination = $upload_dir . 'screenshot_' . TIMENOW . '_' . prepareFile($screenshotFiles['name'][$key]);
		if (!convertToWebP($tmpName, $destination)) {
			$this->ajax_die($lang['POSTING_IMAGES_ERROR']);
		}
		$screenshotsLink[] = make_url(hide_bb_path($destination));
	}
}

function convertToWebP($inputFile, $outputFile, $quality = 90)
{
	$imageInfo = getimagesize($inputFile);
	if (!$imageInfo) {
		return false;
	}

	if ($imageInfo['mime'] === 'image/webp') {
		return copy($inputFile, $outputFile);
	}

	$imageType = $imageInfo[2];
	$image = null;

	switch ($imageType) {
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($inputFile);
			break;
		case IMAGETYPE_PNG:
			$image = @imagecreatefrompng($inputFile);
			break;
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($inputFile);
			break;
	}

	if (!$image) {
		return false;
	}

	imagepalettetotruecolor($image);
	imagealphablending($image, true);
	imagesavealpha($image, true);

	$result = imagewebp($image, $outputFile, $quality);
	imagedestroy($image);

	return $result;
}

function prepareFile($filename)
{
	$fileInfo = pathinfo($filename);
	$fileInfo['filename'] = make_rand_str(32);
	return $fileInfo['filename'] . '.webp';
}

$this->response['success'] = true;
$this->response['poster'] = $posterLink;
$this->response['screenshots'] = $screenshotsLink;
