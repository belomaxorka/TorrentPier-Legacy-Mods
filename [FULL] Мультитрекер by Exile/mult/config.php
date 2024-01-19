<?php

define('DEFAULT_SOCKET_TIMEOUT', 1);
define('TORRENT_PER_CYCLE', 20); //кол-во обноляемых раздач за цикл
define('TIME', time());
define('TIME_UPD', TIME - 43200); //период обновления каждой раздачи, по умолчанию 12 часов.

// Список анонсеров с открытых трекеров, можно добавлять свои, по аналогии
$cfg_ann[] = 'udp://tracker.openbittorrent.com:6969';
$cfg_ann[] = 'udp://tracker.openbittorrent.com:6969/announce';
$cfg_ann[] = 'udp://tracker.openbittorrent.com:80/announce';
$cfg_ann[] = 'udp://exodus.desync.com:6969/announce';
$cfg_ann[] = 'udp://tracker.torrent.eu.org:451/announce';
$cfg_ann[] = 'udp://tracker.moeking.me:6969/announce';

$cfg_db['host'] = 'localhost'; // хост
$cfg_db['db'] = ''; // база
$cfg_db['user'] = ''; // юзер
$cfg_db['pass'] = ''; // пароль
$cfg_db['charset'] = 'utf8';
