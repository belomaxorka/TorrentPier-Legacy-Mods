<?php

define('IN_FORUM',   true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(CLASS_DIR.'snoopy.php');
require(INC_DIR .'functions_torrent.php');
require(INC_DIR .'bbcode.php');
require(INC_DIR .'functions_post.php');

$attach_dir = get_attachments_dir();

$ids = array();
$sql = DB()->fetch_rowset("SELECT id FROM rutor_releases");
if($sql)
{
	foreach($sql as $i => $row)
	{
		$ids[$row['id']] = true;
	}
}

$sql = DB()->fetch_rowset("SELECT f.forum, f.user_id, r.* FROM rutor_releases r, rutor_categories f WHERE r.time = 0 AND f.categorie = r.categorie AND f.active = 1 GROUP BY r.id ORDER BY RAND() DESC LIMIT 5");

//$sql = DB()->fetch_rowset("SELECT f.forum, f.user_id, r.* FROM rutor_releases r, rutor_categories f WHERE r.time = 0 AND f.categorie = r.categorie AND f.active = 1 ORDER BY r.id DESC LIMIT 5");

if($sql)
{
	$snoopy = new Snoopy;
	$snoopy->host = "rutor.info";
	$snoopy->agent = "opera";
	$snoopy->rawheaders["Pragma"] = "no-cache";

	for($i=0; $i < count($sql); $i++)
	{
		if(empty($sql[$i]['id'])) break;

		$subject = DB()->escape($sql[$i]['title']);

		$snoopy->fetch('http://rutor.info/torrent/'. $sql[$i]['id']);

		$content = $snoopy->results;
		$message = prepare_message(DB()->escape(rutor($content)));

		if ($message)
		{
            unset($snoopy->results);

            preg_match_all ("#<img src=\"http://s.rutor.info/t/down.png\"> .*? ([\s\S]*?).torrent</a>#", $content, $source, PREG_SET_ORDER);
            $tor_name = $source[0][1];

            //$snoopy->fetch("http://new-rutor.org/parse/d.rutor.org/download/". $sql[$i]['id']);
			$snoopy->fetch("http://d.rutor.info/download/". $sql[$i]['id']);
			//$snoopy->fetch("http://tracker.rutor.org/d.rutor.org/download/". $sql[$i]['id']);
			$torrent = $snoopy->results;
			$tor     = bdecode($torrent);

            if(!empty($tor['info']))
            {
            	$info_hash     = pack('H*', sha1(bencode($tor['info'])));
            	$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
				$info_hash_md5 = md5($info_hash);

				if($row = DB()->fetch_row("SELECT topic_id FROM ". BB_BT_TORRENTS ." WHERE info_hash = '$info_hash_sql' LIMIT 1"))
				{
				    DB()->sql_query("UPDATE rutor_releases SET time = ". TIMENOW ." WHERE title = '$subject'");
				}
				else
				{
					$tor_name = $info_hash_md5;
					
				    $file = fopen("$attach_dir/$tor_name.torrent", 'w');
					fputs($file, $torrent);
					fclose($file);

					$torfile = array(
						'physical_filename'		=> DB()->escape("$tor_name.torrent"),
						'real_filename'			=> DB()->escape("$tor_name"),
						'filesize'				=> (int) filesize("$attach_dir/$tor_name.torrent"),
					);

					submit_torrent($subject, $message, $sql[$i]['forum'], $torfile, (TIMENOW + $i), $sql[$i]['user_id']);
				}
            }
            else
            {
				//DB()->sql_query("UPDATE rutor_releases SET time = ". TIMENOW ." WHERE title = '$subject'");
			}
			unset($tor);
		}
		else
		{
			DB()->sql_query("UPDATE rutor_releases SET time = ". TIMENOW ." WHERE title = '$subject'");
		}
	}
}
echo 'усЁ';
function rutor($text)
{
	preg_match_all ("#<tr><td style=\"vertical-align:top;\"></td><td>([\s\S]*?)</td></tr>#si", $text, $source, PREG_SET_ORDER);
	$text = $source[0][1];

	$text = preg_replace('/<br.*?>/', "", $text);
	$text = preg_replace('/<a href="\/tag\/.*?" target="_blank">([\s\S]*?)<\/a>/', '$1', $text);
	$text = preg_replace('/<div class="hidewrap"><div class="hidehead" onclick="hideshow.*?">([\s\S]*?)<\/div><div class="hidebody"><\/div><textarea class="hidearea">([\s\S]*?)<\/textarea><\/div>/', "[spoiler=\"\\1\"]\\2[/spoiler]", $text);

	$text = str_replace('<center>', '[align=center]', $text);
	$text = str_replace('</center>', '[/align]', $text);
	$text = str_replace('<hr />', '[hr]', $text);

	$text = str_replace('&#039;', "'", $text);
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('&gt;', '>', $text);
	$text = str_replace('&lt;', '<', $text);

	for ($i=0; $i<=20; $i++)
	{
		$text = preg_replace('/<a href="([^<]*?)" target="_blank">([^<]*?)<(?=\/)\/a>/siu', '[url=$1]$2[/url]', $text);
		$text = preg_replace('/<img src="([^<]*?)" style="float:(.*?);" \/>/siu', '[img=$2]$1[/img]', $text);
		$text = preg_replace('/<img src="([^<]*?)" \/>/siu', '[img]$1[/img]', $text);
		$text = preg_replace('/<b>([^<]*?)<(?=\/)\/b>/', '[b]$1[/b]', $text);
		$text = preg_replace('/<u>([^<]*?)<(?=\/)\/u>/', '[u]$1[/u]', $text);
		$text = preg_replace('/<i>([^<]*?)<(?=\/)\/i>/', '[i]$1[/i]', $text);
		$text = preg_replace('/<s>([^<]*?)<(?=\/)\/s>/', '[s]$1[/s]', $text);
		$text = preg_replace('/<font size="([^<]*?)">([^<]*?)<(?=\/)\/font>/', "[size=2\\1]\\2[/size]", $text);
		$text = preg_replace('/<span style="color:([^<]*?);">([^<]*?)<(?=\/)\/span>/', '[color=$1]$2[/color]', $text);
		$text = preg_replace('/<span style="font-family:([^<]*?);">([^<]*?)<(?=\/)\/span>/', '[font="$1"]$2[/font]', $text);
	}
	$text = strip_tags(html_entity_decode($text));

	return $text;
}


function submit_torrent($subject, $message, $forum_id, $torfile, $time, $user_id)
{
	global $lang, $bb_cfg;

    $user_ip = '7f000001';
    if(!$user_id) $user_id = BOT_UID;

	$subject = preg_replace("/(FREEISLAND|HQCLUB|HQ-ViDEO|HELLYWOOD|ExKinoRay|NewStudio|LostFilm|RiperAM|Generalfilm|Files-x|NovaLan|Scarabey|New-Team|HD-NET|MediaClub|Baibako|CINEMANIA|Rulya74|RG WazZzuP|Ash61|egoleshik|Т-Хzona|TORRENT - BAGIRA|F-Torrents|2LT_FS|Bagira|Pshichko66|Занавес|msltel|Leo.pard|Точка Zрения|BenderBEST|PskovLine|HDReactor|Temperest|Element-Team|BT-Club|Filmoff CLUB|HD Club|HDCLUB|potroks|fox-torrents|HYPERHD|GORESEWAGE|NoLimits-Team|New Team|FireBit-Films|NNNB|New-team|Youtracker|marcury|Neofilm|Filmrus|Deadmauvlad|Torrent-Xzona|Brazzass|Кинорадиомагия|Assassin&#039;s Creed|GOLDBOY|ClubTorrent|AndreSweet|TORRENT-45|0ptimus|Torrange|Sanjar &amp; NeoJet|Leonardo|BTT-TEAM и Anything-group|BTT-TEAM|Anything-group|Gersuzu|Xixidok|PEERATES|ivandubskoj|R. G. Jolly Roger|Fredd Kruger|Киномагия|RG MixTorrent|RusTorents|Тorrent-Хzona|R.G. Mega Best|Gold Cartoon KINOREAKTOR (Sheikn)|ImperiaFilm|RG Jolly Roger|Sheikn|R.G. Mobile-Men|KinoRay &amp; Sheikn|HitWay|mcdangerous|Тorren|Stranik 2.0|Romych|R.G. AVI|Lebanon|Big111|Dizell|СИНЕМА-ГРУПП|PlanetaUA|RG Superdetki|potrokis|olegek70|bAGrat|Alekxandr48|Mao Dzedyn|Fartuna|R.G.Mega Best|DenisNN|Киномагии|UAGet|Victorious|Gold Cartoon KINOREAKTOR|KINOREAKTOR|KinoFiles|HQRips|F-Torrent|A.Star|Beeboop|Azazel|Leon-masl|Vikosol|RG Orient Extreme|R.G.TorrBy|ale x2008|Deadmauvlad|semiramida1970|Zelesk|CineLab SoundMix|Сотник|ALGORITM|E76|datynet|Дяди Лёши| leon030982|GORESEWAGE|Hot-Film|КинозалSAT|ENGINEER|CinemaClub|Zlofenix|pro100shara|FreeRutor|FreeHD|гаврила|vadi|SuperMin|GREEN TEA|Kerob|AGR - Generalfilm|R.G. DHT-Music|Витек 78|Twi7ter|KinoGadget|BitTracker|KURD28|Gears Media|KINONAVSE100|Just TeMa)/si","ТВОЙ_ТРЕКЕР.РУ",$subject);

    DB()->sql_query("INSERT INTO ". BB_TOPICS ."
		(topic_title, topic_poster, topic_time, forum_id, topic_attachment, topic_dl_type, topic_last_post_time)
		VALUES
		('$subject', $user_id, '$time', $forum_id, '1', '1', '$time')");
	$topic_id = DB()->sql_nextid();

	DB()->sql_query("INSERT INTO ". BB_POSTS ."
		(topic_id, forum_id, poster_id, post_time, poster_ip, post_attachment)
		VALUES
		($topic_id, $forum_id, $user_id, '$time', '$user_ip', '1')");
	$post_id = DB()->sql_nextid();

	DB()->sql_query("UPDATE ". BB_TOPICS ." SET
		topic_first_post_id = $post_id,
		topic_last_post_id = $post_id
		WHERE topic_id = $topic_id");

	DB()->sql_query("INSERT INTO ". BB_POSTS_TEXT ."
		(post_id, post_text)
		VALUES
		($post_id, '$message')");

	add_search_words($post_id, stripslashes($message), stripslashes($subject));

	DB()->sql_query("INSERT INTO ". BB_ATTACHMENTS_DESC ."
		(physical_filename, real_filename, extension, mimetype, filesize, filetime)
		VALUES
		('". $torfile['physical_filename'] ."', '". $torfile['real_filename'] ."', 'torrent', 'application/x-bittorrent', '". $torfile['filesize'] ."', ". $time .")");
	$attach_id = DB()->sql_nextid();

	DB()->sql_query("INSERT INTO ". BB_ATTACHMENTS ."
		(attach_id, post_id, user_id_1)
		VALUES
		($attach_id, $post_id, ". $user_id .")");

    DB()->sql_query("UPDATE ". BB_FORUMS ." SET
		forum_posts = forum_posts + 1,
		forum_last_post_id = $post_id,
		forum_topics = forum_topics + 1
		WHERE forum_id = $forum_id");

	DB()->sql_query("UPDATE rutor_releases SET time = ". TIMENOW ." WHERE title = '$subject'");

	if ($bb_cfg['last_added'])
	{
		$row = DB()->fetch_row("SELECT post_text FROM ". BB_POSTS_TEXT ." WHERE post_id = $post_id");			
	        preg_match_all('/\[gposter=right\](.*?)\[\/gposter\]/i', $row['post_text'], $poster7, PREG_SET_ORDER);
	        preg_match_all('/\[gposter=left\](.*?)\[\/gposter\]/i', $row['post_text'], $poster6, PREG_SET_ORDER);
	        preg_match_all('/\[gposter\](.*?)\[\/gposter\]/i', $row['post_text'], $poster5, PREG_SET_ORDER);
	        preg_match_all('/\[poster\](.*?)\[\/poster\]/i', $row['post_text'], $poster4, PREG_SET_ORDER);
	        preg_match_all('/\[img=right\](.*?)\[\/img\]/i', $row['post_text'], $poster3, PREG_SET_ORDER);
	        preg_match_all('/\[img=left\](.*?)\[\/img\]/i', $row['post_text'], $poster2, PREG_SET_ORDER);
	        preg_match_all('/\[img\](.*?)\[\/img\]/i', $row['post_text'], $poster1, PREG_SET_ORDER);
	    $url = '';
	    if (isset($poster7[0][1])) $url = $poster7[0][1];
	    elseif (isset($poster6[0][1])) $url = $poster6[0][1];
	    elseif (isset($poster5[0][1])) $url = $poster5[0][1];
	    elseif (isset($poster4[0][1])) $url = $poster4[0][1];
	    elseif (isset($poster3[0][1])) $url = $poster3[0][1];
	    elseif (isset($poster2[0][1])) $url = $poster2[0][1];
	    elseif (isset($poster1[0][1])) $url = $poster1[0][1];
	    
		DB()->query("UPDATE ". BB_TOPICS ." SET topic_image = '$url' WHERE topic_id = $topic_id");
	}
	
	tracker_register ($attach_id, '2', TOR_APPROVED);
}
