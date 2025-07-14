<?php

define('BB_SCRIPT', 'thumb');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

// Start session management
$user->session_start(array('req_login' => true));

$folder = BB_ROOT . 'data/thumbnails/';

// Force cleanup thumbs
if (IS_SUPER_ADMIN && isset($_GET['force_cleanup'])) {
    $images = array_merge(
        glob($folder . '*.webp'),
        glob($folder . '*.jpg'),
        glob($folder . '*.png'),
        glob($folder . '*.gif')
    );
    $images = array_unique($images);
    $images = array_values($images);
    $processed_files = array();
    foreach ($images as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                $processed_files[] = $file;
            } else {
                bb_log('[Thumb] Cant remove file (Force delete): ' . $file . LOG_LF);
            }
        }
    }
    if (!empty($processed_files)) {
        die('<ul><li>' . implode('</li><li>', $processed_files) . '</li></ul>');
    }
    die;
}

// Получаем ID раздачи (темы)
$topic_id = isset($_GET[POST_TOPIC_URL]) ? (int)$_GET[POST_TOPIC_URL] : 0;
if (!$topic_id) {
    bb_die('INVALID_TOPIC_ID');
}

// Получаем данные о раздаче из базы
$row = DB()->fetch_row("
    SELECT pt.post_text
    FROM " . BB_TOPICS . " t
    LEFT JOIN " . BB_POSTS . " p ON (t.topic_id = p.topic_id)
    LEFT JOIN " . BB_POSTS_TEXT . " pt ON (pt.post_id = p.post_id)
    WHERE t.topic_id = $topic_id
");

if (!$row) {
    bb_die('INVALID_TOPIC_ID_DB');
}

// Поддерживаемые теги изображений
$tags = array(
    '/\[case\](.*?)\[\/case\]/i',
    '/\[gposter=right\](.*?)\[\/gposter\]/i',
    '/\[gposter=left\](.*?)\[\/gposter\]/i',
    '/\[gposter\](.*?)\[\/gposter\]/i',
    '/\[poster\](.*?)\[\/poster\]/i',
    '/\[img=right\](.*?)\[\/img\]/i',
    '/\[img=left\](.*?)\[\/img\]/i',
    '/\[img\](.*?)\[\/img\]/i',
);

$image_url = '';
foreach ($tags as $tag) {
    if (preg_match($tag, $row['post_text'], $matches)) {
        $image_url = trim($matches[1]);
        break;
    }
}

if (empty($image_url)) {
    bb_log('[Thumb] No image url. Used: noposter.png' . LOG_LF);
    $image_url = BB_ROOT . 'styles/images/noposter.png';
}

// Проверяем, является ли URL локальным для текущего домена
$image_url = convertToLocalPath($image_url);

// Создание папки с миниатюрами
if (!is_dir($folder)) {
    bb_mkdir($folder);
}

// Генерация имени файла миниатюры
$filename = basename($image_url);
$thumb_file = $folder . $filename;

// Проверка наличия миниатюры
if (file_exists($thumb_file)) {
    serveImage($thumb_file);
}

// Если миниатюры нет, создаем её
try {
    if (!defined('IMAGETYPE_WEBP') || !function_exists('imagecreatefromwebp')) {
        throw new Exception('Webp images are not supported');
    }

    $image_info = getimagesize($image_url);
    if (!$image_info) {
        throw new Exception('Invalid image file: ' . $image_url);
    }

    list($original_width, $original_height, $type) = $image_info;
    $mime_type = image_type_to_mime_type($type);

    // Создание миниатюры
    $max_width = 100;
    $ratio = $original_width / $original_height;
    $thumb_width = $max_width;
    $thumb_height = round($thumb_width / $ratio);

    $source_image = createImageFromType($image_url, $type);
    $thumb = imagecreatetruecolor($thumb_width, $thumb_height);

    // Обработка прозрачности для PNG/GIF/WEBP
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF || $type === IMAGETYPE_WEBP) {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $source_image, 0, 0, 0, 0, $thumb_width, $thumb_height, $original_width, $original_height);

    // Сохранение миниатюры
    saveThumbnail($thumb, $thumb_file, $type);

    // Вывод миниатюры
    serveImage($thumb_file);

    // Очистка ресурсов
    imagedestroy($source_image);
    imagedestroy($thumb);
} catch (Exception $e) {
    // В случае ошибки выводим плейсхолдер
    bb_log('[Thumb] Exception: ' . $e->getMessage() . LOG_LF);
    serveImage('styles/images/noposter.png');
}

/**
 * Функция для проверки и конвертации URL в локальный путь
 */
function convertToLocalPath($url)
{
    global $bb_cfg;

    // Получаем текущий хостнейм
    $current_host = '';
    if (isset($bb_cfg['server_name'])) {
        $current_host = $bb_cfg['server_name'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $current_host = $_SERVER['HTTP_HOST'];
    }

    // Если это относительный путь или уже локальный файл, возвращаем как есть
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }

    $parsed_url = parse_url($url);
    $url_host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

    // Проверяем, совпадает ли хостнейм
    if ($url_host === $current_host) {
        // Конвертируем URL в локальный путь
        $local_path = BB_ROOT . ltrim($parsed_url['path'], '/');

        // Проверяем, существует ли локальный файл
        if (file_exists($local_path)) {
            // bb_log('[Thumb] Converted to local path: ' . $local_path . LOG_LF);
            return $local_path;
        } else {
            // bb_log('[Thumb] Local file not found: ' . $local_path . ', using original URL' . LOG_LF);
        }
    }

    return $url;
}

/**
 * Функция для создания изображения из URL в зависимости от типа
 */
function createImageFromType($url, $type)
{
    switch ($type) {
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($url);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($url);
        case IMAGETYPE_GIF:
            return imagecreatefromgif($url);
        case IMAGETYPE_WEBP:
            return imagecreatefromwebp($url);
        default:
            throw new Exception('Unknown image type: ' . $type);
    }
}

/**
 * Функция для сохранения миниатюры
 */
function saveThumbnail($image, $file, $type)
{
    switch ($type) {
        case IMAGETYPE_JPEG:
            return imagejpeg($image, $file, 85);
        case IMAGETYPE_PNG:
            return imagepng($image, $file, 0);
        case IMAGETYPE_GIF:
            return imagegif($image, $file);
        case IMAGETYPE_WEBP:
            return imagewebp($image, $file, 85);
        default:
            throw new Exception('Unknown image type: ' . $type);
    }
}

/**
 * Функция для отправки изображения клиенту
 */
function serveImage($file)
{
    $mime_type = '';
    if (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file);
    } else {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $mime_type = 'image/jpeg';
                break;
            case 'png':
                $mime_type = 'image/png';
                break;
            case 'gif':
                $mime_type = 'image/gif';
                break;
            case 'webp':
                $mime_type = 'image/webp';
                break;
            default:
                $mime_type = 'application/octet-stream';
        }
    }

    header("Content-type: $mime_type");
    header('Content-Disposition: filename=' . basename($file));
    readfile($file);
    exit;
}
