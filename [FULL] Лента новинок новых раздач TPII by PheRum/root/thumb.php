<?php

define('BB_SCRIPT', 'thumb');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

// Start session management
$user->session_start(array('req_login' => true));

// Получаем id раздачи (темы)
$topic_id = (int)request_var('t', '0');
if (!$topic_id) {
	bb_die('INVALID_TOPIC_ID');
}

// Получаем данные о раздаче из базы
$row = DB()->fetch_row("SELECT pt.post_text
		FROM " . BB_BT_TORRENTS . " tr
			LEFT JOIN " . BB_TOPICS . " t ON(tr.topic_id = t.topic_id)
			LEFT JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id = tr.post_id)
		WHERE t.topic_id  = $topic_id");
if (!$row) {
	bb_die('INVALID_TOPIC_ID_DB');
}

// Поддерживаемые теги изображений
preg_match_all('/\[case\](.*?)\[\/case\]/i', $row['post_text'], $poster8, PREG_SET_ORDER);
preg_match_all('/\[gposter=right\](.*?)\[\/gposter\]/i', $row['post_text'], $poster7, PREG_SET_ORDER);
preg_match_all('/\[gposter=left\](.*?)\[\/gposter\]/i', $row['post_text'], $poster6, PREG_SET_ORDER);
preg_match_all('/\[gposter\](.*?)\[\/gposter\]/i', $row['post_text'], $poster5, PREG_SET_ORDER);
preg_match_all('/\[poster\](.*?)\[\/poster\]/i', $row['post_text'], $poster4, PREG_SET_ORDER);
preg_match_all('/\[img=right\](.*?)\[\/img\]/i', $row['post_text'], $poster3, PREG_SET_ORDER);
preg_match_all('/\[img=left\](.*?)\[\/img\]/i', $row['post_text'], $poster2, PREG_SET_ORDER);
preg_match_all('/\[img\](.*?)\[\/img\]/i', $row['post_text'], $poster1, PREG_SET_ORDER);

// Настройки
$no_poster_img_name = 'noposter.png'; // Имя файла плейсхолдера (для релизов, в которых нет постера)
$no_poster = BB_ROOT . 'styles/images/' . $no_poster_img_name; // Абсолютный путь до файла плейсхолдера
$folder = BB_ROOT . 'data/thumbnails'; // Папка куда сохраняем

$url = empty($url) ? $no_poster_img_name : '';
if (isset($poster8[0][1])) $url = $poster8[0][1];
elseif (isset($poster7[0][1])) $url = $poster7[0][1];
elseif (isset($poster6[0][1])) $url = $poster6[0][1];
elseif (isset($poster5[0][1])) $url = $poster5[0][1];
elseif (isset($poster4[0][1])) $url = $poster4[0][1];
elseif (isset($poster3[0][1])) $url = $poster3[0][1];
elseif (isset($poster2[0][1])) $url = $poster2[0][1];
elseif (isset($poster1[0][1])) $url = $poster1[0][1];

// Получаем информацию о картинке
$filetype = substr(strrchr($url, '.'), 1);
$filename = substr($url, strrpos($url, '/'));

// Генерируем путь до файла
$thumb_file = $folder . $filename;
if (!is_dir($folder)) bb_mkdir($folder);

// Проверяем на наличие и выводим
if (@fopen($thumb_file, "r")) {
	switch ($filetype) {
		case 'jpg':
		case 'jpeg':
			header('Content-type: image/jpeg');
			header('Content-Disposition: filename=' . $filename);
			break;
		case 'png':
			header('Content-type: image/png');
			header('Content-Disposition: filename=' . $filename);
			break;
		case 'gif':
			header('Content-type: image/gif');
			header('Content-Disposition: filename=' . $filename);
			break;
		case 'webp':
			header('Content-type: image/webp');
			header('Content-Disposition: filename=' . $filename);
			break;
		default:
			bb_die('Unknown filetype: ' . $filetype);
			break;
	}

	readfile($thumb_file); // Выводим
	exit;
} else {
	// Пробуем открыть файл для чтения
	if (@fopen($url, "r")) {
		// Узнаём размеры
		if ($filetype == 'webp') {
			$img_webp = ImageCreateFromWEBP($url);
			$poster_width = imagesx($img_webp);
			$poster_height = imagesy($img_webp);
			unset($img_webp);
		} else {
			list($poster_width, $poster_height) = getimagesize($url);
		}

		if (!$poster_width || !$poster_height) // Проверяем на изображение
		{
			header('Content-type: image/png');
			header('Content-Disposition: filename=' . $no_poster_img_name);
			readfile($no_poster);
			exit;
		} else {
			// Узнаём размеры
			if ($filetype == 'webp') {
				$img_webp = ImageCreateFromWEBP($url);
				$poster_width = imagesx($img_webp);
				$poster_height = imagesy($img_webp);
				unset($img_webp);
			} else {
				@list($poster_width, $poster_height) = getimagesize($url);
			}

			if (!$poster_width && !$poster_height) // Проверяем на наличие
			{
				header('Content-type: image/png');
				header('Content-Disposition: filename=' . $no_poster_img_name);
				readfile($no_poster);
				exit;
			}

			// Открываем
			$poster = '';

			switch ($filetype) {
				case 'jpg':
				case 'jpeg':
					$poster = ImageCreateFromJPEG($url);
					break;
				case 'png':
					$poster = ImageCreateFromPNG($url);
					break;
				case 'gif':
					$poster = ImageCreateFromGIF($url);
					break;
				case 'webp':
					$poster = ImageCreateFromWEBP($url);
					break;
				default:
					bb_die('Unknown filetype: ' . $filetype);
					break;
			}

			$max_width = 100; // Уменьшение по ширине
			$thumb_width = $max_width;
			$thumb_height = round(($poster_height * $max_width) / $poster_width);

			if (($poster_width > $max_width)) {
				$thumb = imagecreatetruecolor($thumb_width, $thumb_height);

				if ($filetype == 'gif' || $filetype == 'png') {
					imagecolortransparent($thumb, imagecolorallocate($thumb, 0, 0, 0));
					if ($filetype == 'png') {
						imagealphablending($thumb, false);
						imagesavealpha($thumb, true);
					}
				}

				imagecopyresampled($thumb, $poster, 0, 0, 0, 0, $thumb_width, $thumb_height, $poster_width, $poster_height);
			} else {
				$thumb = $poster;
			}

			// Создаём миниатюру
			switch ($filetype) {
				case "jpg":
				case "jpeg":
					header('Content-type: image/jpeg');
					header('Content-Disposition: filename=' . $filename);
					ImageJPEG($thumb, $thumb_file, 85);
					break;
				case "png":
					header("Content-type: image/png");
					header('Content-Disposition: filename=' . $filename);
					ImagePNG($thumb, $thumb_file);
					break;
				case "gif":
					header("Content-type: image/gif");
					header('Content-Disposition: filename=' . $filename);
					ImageGIF($thumb, $thumb_file);
					break;
				case "webp":
					header("Content-type: image/webp");
					header('Content-Disposition: filename=' . $filename);
					ImageWEBP($thumb, $thumb_file);
					break;
				default:
					bb_die('Unknown filetype: ' . $filetype);
					break;
			}

			// Закрываем
			imagedestroy($thumb);
			imagedestroy($poster);

			// Выводим
			readfile($thumb_file);
			exit;
		}
	} else {
		header('Content-type: image/png');
		header('Content-Disposition: filename=' . $no_poster_img_name);
		readfile($no_poster);
		exit;
	}
}
