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

// For PHP 5 (if webp are not supported)
if (!defined('IMAGETYPE_WEBP') || !function_exists('imagecreatefromwebp')) {
	define('USE_JPG_ONLY', true);
} else {
	define('USE_JPG_ONLY', false);
}

$upload_dir = $bb_cfg['ajax_upload_posting_images_path'];
$posterLink = $screenshotsLink = [];

if (!empty($_FILES['poster_images'])) {
	$posterFiles = $_FILES['poster_images'];
	foreach ($posterFiles['tmp_name'] as $key => $tmpName) {
		if ($posterFiles['size'][$key] > $bb_cfg['ajax_upload_posting_images_size_limit']) {
			$this->ajax_die($lang['POSTING_IMAGES_FILE_TOO_LARGE'] . humn_size($bb_cfg['ajax_upload_posting_images_size_limit']));
		}

		$outputFile = $upload_dir . 'poster_' . TIMENOW . '_' . make_rand_str(32);
		if (!convertor($tmpName, $outputFile)) {
			$this->ajax_die($lang['POSTING_IMAGES_ERROR']);
		}
		$posterLink[] = make_url(hide_bb_path($outputFile));
	}
}

if (!empty($_FILES['screenshots_images'])) {
	$screenshotFiles = $_FILES['screenshots_images'];
	foreach ($screenshotFiles['tmp_name'] as $key => $tmpName) {
		$outputFile = $upload_dir . 'screenshot_' . TIMENOW . '_' . make_rand_str(32);
		if (!convertor($tmpName, $outputFile)) {
			$this->ajax_die($lang['POSTING_IMAGES_ERROR']);
		}
		$screenshotsLink[] = make_url(hide_bb_path($outputFile));
	}
}

/**
 * Images convertor (to webp or jpg)
 *
 * @param string $inputFile
 * @param string $outputFile
 * @param int $quality
 * @return bool
 */
function convertor($inputFile, &$outputFile, $quality = 90)
{
	if (!file_exists($inputFile)) {
		bb_log("[Posting Images] Input file does not exist: $inputFile" . LOG_LF);
		return false;
	}

	if (!is_readable($inputFile)) {
		bb_log("[Posting Images] Input file is not readable: $inputFile" . LOG_LF);
		return false;
	}

	if ($quality < 0 || $quality > 100) {
		bb_log("[Posting Images] Quality must be between 0 and 100, got: $quality" . LOG_LF);
		return false;
	}

	$outputFileGIF = $outputFile . '.gif';
	$outputFileWeBP = $outputFile . '.webp';
	$outputFileJPG = $outputFile . '.jpg';

	$mimeType = mime_content_type($inputFile);
	if ($mimeType === false) {
		bb_log("[Posting Images] Cannot determine MIME type for: $inputFile" . LOG_LF);
		return false;
	}
	if (USE_JPG_ONLY && $mimeType === 'application/octet-stream') {
		$mimeType = 'image/webp';
	}

	if ($mimeType === 'image/webp') {
		$outputFile = $outputFileWeBP;
		return copy($inputFile, $outputFile);
	}

	if ($mimeType === 'image/gif') {
		$outputFile = $outputFileGIF;
		return copy($inputFile, $outputFile);
	}

	if (USE_JPG_ONLY && $mimeType === 'image/jpeg') {
		$outputFile = $outputFileJPG;
		return copy($inputFile, $outputFile);
	}

	$image = null;

	switch ($mimeType) {
		case 'image/jpeg':
			$image = imagecreatefromjpeg($inputFile);
			break;
		case 'image/png':
			$image = @imagecreatefrompng($inputFile);
			break;
	}

	if (!$image) {
		bb_log("[Posting Images] Failed to create image from file: $inputFile" . LOG_LF);
		return false;
	}

	if (!imagepalettetotruecolor($image)) {
		bb_log("[Posting Images] Failed to convert palette to true color" . LOG_LF);
		imagedestroy($image);
		return false;
	}

	if ($mimeType === 'image/png') {
		imagepalettetotruecolor($image);
		imagealphablending($image, true);
		imagesavealpha($image, true);
	}

	if (USE_JPG_ONLY) {
		$outputFile = $outputFileJPG;
		$result = imagejpeg($image, $outputFile, $quality);
	} else {
		$outputFile = $outputFileWeBP;
		$result = imagewebp($image, $outputFile, $quality);
	}

	imagedestroy($image);

	return $result;
}

$this->response['success'] = true;
$this->response['poster'] = $posterLink;
$this->response['screenshots'] = $screenshotsLink;
