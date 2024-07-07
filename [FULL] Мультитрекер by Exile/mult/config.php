<?php

// Настройки
define('USE_AUTO_TRACKERS_UPDATE', true); // Использовать авто-обновление списка хостов
define('HOSTS_FILE_PATH', __DIR__ . '/trackers.txt'); // Файл в который сохранять актуальные хосты
define('AUTO_UPDATE_SOURCE', 'https://raw.githubusercontent.com/ngosang/trackerslist/master/trackers_all.txt'); // Основной источник
define('AUTO_UPDATE_SOURCE_MIRROR_1', 'https://ngosang.github.io/trackerslist/trackers_all.txt'); // Поставьте значение null, если нет зеркал
define('AUTO_UPDATE_SOURCE_MIRROR_2', 'https://cdn.jsdelivr.net/gh/ngosang/trackerslist@master/trackers_all.txt'); // Поставьте значение null, если нет зеркал

define('LIMIT_MAX_TRACKERS', null); // Ограничить число опрашиваемых хостов из массива $cfg_ann (Указать число сколько опрашивать | null - выключить лимит)
define('ANNOUNCER_TIMEOUT_CONNECT', 5); // Максимальное время на подключение к хосту (В секундах)
define('SHOW_DEAD_ANNOUNCERS', false); // Показывать список мертвых / недоступных хостов
define('TORRENT_PER_CYCLE', 20); // Количество обновляемых раздач за цикл
define('TIME_UPD', (time() - 43200)); // Период обновления раздачи. По умолчанию: Каждые 12 часов.

// Список хостов / Резерв (Если включено авто-обновление)
$cfg_ann[] = 'udp://tracker.openbittorrent.com:6969';
$cfg_ann[] = 'udp://tracker.openbittorrent.com:6969/announce';
$cfg_ann[] = 'udp://tracker.openbittorrent.com:80/announce';
$cfg_ann[] = 'udp://exodus.desync.com:6969/announce';
$cfg_ann[] = 'udp://tracker.torrent.eu.org:451/announce';
$cfg_ann[] = 'udp://tracker.moeking.me:6969/announce';

// Данные для подключения к базе
$cfg_db['host'] = 'localhost';
$cfg_db['db'] = 'torrentpier';
$cfg_db['user'] = 'root';
$cfg_db['pass'] = 'pass';
$cfg_db['charset'] = 'utf8';
