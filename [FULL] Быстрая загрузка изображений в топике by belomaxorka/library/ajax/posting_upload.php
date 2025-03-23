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

$upload_dir = DATA_DIR . '/posts_images/';
$posterLink = $screenshotsLink = [];

if (!empty($_FILES['poster_images'])) {
	$posterFiles = $_FILES['poster_images'];
	foreach ($posterFiles['tmp_name'] as $key => $tmpName) {
		$destination = $upload_dir . 'poster_' . TIMENOW . '_' . replaceExtensionWithWebP($posterFiles['name'][$key]);
		if (!convertToWebP($tmpName, $destination)) {
			$this->ajax_die($lang['POSTING_IMAGES_ERROR']);
		}
		$posterLink[] = make_url(hide_bb_path($destination));
	}
}

if (!empty($_FILES['screenshots_images'])) {
	$screenshotFiles = $_FILES['screenshots_images'];
	foreach ($screenshotFiles['tmp_name'] as $key => $tmpName) {
		$destination = $upload_dir . 'screenshot_' . TIMENOW . '_' . replaceExtensionWithWebP($screenshotFiles['name'][$key]);
		if (!convertToWebP($tmpName, $destination)) {
			$this->ajax_die($lang['POSTING_IMAGES_ERROR']);
		}
		$screenshotsLink[] = make_url(hide_bb_path($destination));
	}
}

function convertToWebP($inputFile, $outputFile, $quality = 90)
{
	// Определение типа исходного изображения
	$imageInfo = getimagesize($inputFile);
	if (!$imageInfo) {
		return false;
	}

	$imageType = $imageInfo[2];

	// Загрузка изображения в зависимости от типа
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

	// Проверка, успешно ли загружено изображение
	if (!$image) {
		return false;
	}

	// Сохранение в формате WebP
	$result = imagewebp($image, $outputFile, $quality);

	// Освобождение памяти
	imagedestroy($image);

	return $result;
}

function replaceExtensionWithWebP($filename)
{
	$fileInfo = pathinfo($filename);
	if (!isset($fileInfo['extension'])) {
		return $filename . '.webp';
	}

	return $fileInfo['filename'] . '.webp';
}

$this->response['success'] = true;
$this->response['poster'] = $posterLink;
$this->response['screenshots'] = $screenshotsLink;
