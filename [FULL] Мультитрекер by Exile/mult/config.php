<?php

ini_set("log_errors", "off"); 
//ini_set("error_log", "");

define('DEFAULT_SOCKET_TIMEOUT', 1);
define('TORRENT_PER_CYCLE', 20); //кол-во обноляемых раздач за цикл
define('TIME', time());
define('TIME_UPD', TIME - 43200); //период обновления каждой раздачи, по умолчанию 12 часов.

//Список анонсеров с открытых трекеров, можно добавлять свои, по аналогии
$cfg_ann[] = 'udp://tracker.prq.to:80/announce';
$cfg_ann[] = 'udp://tracker.publicbt.com:80';
$cfg_ann[] = 'udp://tracker.openbittorrent.com:80';
$cfg_ann[] = 'udp://bt.rutor.org:2710';
$cfg_ann[] = 'http://94.228.192.98/announce';
$cfg_ann[] = 'udp://tracker.istole.it:80/announce';
$cfg_ann[] = 'udp://open.demonii.com:1337';

$cfg_db['host']    = 'localhost';
$cfg_db['db']      = ''; // база
$cfg_db['user']    = ''; // юзер
$cfg_db['pass']    = ''; // пароль
$cfg_db['charset'] = 'utf8';
