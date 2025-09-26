<?php
function get_user_thanks ($uid) //Сколько раз юзер поблагодарил
{
    $query = "SELECT COUNT(user_id) AS thanks FROM ". BB_ATTACHMENTS_RATING ." WHERE user_id=$uid" ;
    $result = DB()->query($query);
    return mysqli_fetch_assoc($result)['thanks'] ?? 0;
}
function get_user_thanked ($uid) //сколько раз юзера благодарили
{
    $query = "SELECT COUNT(r.user_id) AS thanked FROM (". BB_ATTACHMENTS_RATING ." r
    LEFT JOIN bb_attachments a ON ( a.attach_id=r.attach_id) )
    WHERE a.user_id_1=$uid AND r.thanked =1";
    $result = DB()->query($query);

    return mysqli_fetch_assoc($result)['thanked'] ?? 0;
}