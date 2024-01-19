<?php

define('BB_BT_TORRENTS', 'bb_bt_torrents');
include('init.php');

$seed = $leech = 0;
$SQL = "SELECT info_hash FROM " . BB_BT_TORRENTS . " WHERE last_update < " . TIME_UPD . " ORDER BY reg_time DESC LIMIT " . TORRENT_PER_CYCLE;
if($result = $mysqli->query($SQL))
{
  while ($row = $result->fetch_row())
  {
    $data = $gp->get_peers(1, serialize($cfg_ann), bin2hex($row[0]), false);
    print_r($data);
    if($data && $peer_data = $data['peers'])
    {
      foreach($peer_data as $announce)
      {
        $seed = (int) $seed + $announce[0];
        $leech = (int) $leech + $announce[1];
      }
      $SQL_UPD = "UPDATE " . BB_BT_TORRENTS . " SET last_update = ".$data['last_update'].", ext_seeder = ".$seed.", ext_leecher = ".$leech." WHERE info_hash = '".$mysqli->real_escape_string($row[0])."'";
      if($res_upd = $mysqli->query($SQL_UPD))
      {
        $seed = $leech = 0;
      }
      else
      {
        printf("ошибка при обновлении пиров: %s\n", $mysqli->error);
      }
    }
  }
  $result->close();
}
$mysqli->close();
