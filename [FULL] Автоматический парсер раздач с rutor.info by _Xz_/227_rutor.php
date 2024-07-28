<?php

define('IN_FORUM',   true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(CLASS_DIR.'snoopy.php');

$ids = array();
$sql = DB()->fetch_rowset("SELECT id FROM rutor_releases");
if($sql)
{
	foreach($sql as $i => $row)
	{
		$ids[$row['id']] = true;
	}
}

$releases = array();

$sql = DB()->fetch_rowset("SELECT * FROM rutor_categories WHERE active = 1");
if($sql)
{
	$snoopy = new Snoopy;
	$snoopy->host = $url;
	$snoopy->agent = "opera";
	$snoopy->rawheaders["Pragma"] = "no-cache";

	for($i=0; $i <= count($sql); $i++)
	{
        if(empty($sql[$i]['categorie'])) break;

        $snoopy->fetch($sql[$i]['categorie']);
		$content = $snoopy->results;

		if($sql[$i]['all_categorie'])
		{
			$content_url = $content;

			preg_match_all ('#alt="M" />.*?<a href="/torrent/(.*?)/.*?">(.*?)</a></td>#siu', $content, $content);
			for($c=0; $c < count($content[1]); $c++)
			{
				if(!empty($ids[$content[1][$c]])) continue;
				$releases[] = array('id' => $content[1][$c] ,'title' => DB()->escape(trim(strip_tags($content[2][$c]))), 'categorie' => $sql[$i]['categorie']);
			}

			preg_match_all ('#">([0-9]*)<\/a><\/b> Результатов#siu', $content_url, $url);

			if(!empty($url[1][0]))
			{
				for($u=1; $u < $url[1][0]; $u++)
				{
					unset($snoopy->results);

                    $rutor_url = $sql[$i]['categorie'];

					$rutor_url = preg_replace('#/tag/([0-9]*)/#', "/search/$u/\\1/010/0/", $rutor_url);
					$rutor_url = preg_replace('#/search/0/([0-9]*)/0/0/#', "/search/$u/\\1/000/0/", $rutor_url);

					$snoopy->fetch($rutor_url);
					$content = $snoopy->results;

                    if(!preg_match('#stylesheet#si', $content))
                    {
                    	break;
                    }
                    else
                    {
						preg_match_all ('#alt="M" />.*?<a href="/torrent/(.*?)/.*?">(.*?)</a></td>#siu', $content, $content);
						for($c=0; $c < count($content[1]); $c++)
						{
							if(!empty($ids[$content[1][$c]])) continue;
							$releases[] = array('id' => $content[1][$c] ,'title' => DB()->escape(trim(strip_tags($content[2][$c]))), 'categorie' => $sql[$i]['categorie']);
						}
                    }
                    if(($u+1) == $url[1][0]) DB()->query("UPDATE rutor_categories SET all_categorie = 0 WHERE categorie = '{$sql[$i]['categorie']}'");
				}
			}
		}
		else
		{
			preg_match_all ('#alt="M" />.*?<a href="/torrent/(.*?)/.*?">(.*?)</a></td>#siu', $content, $content);
			for($c=0; $c < count($content[1]); $c++)
			{
				if(!empty($ids[$content[1][$c]])) continue;

				$releases[] = array('id' => $content[1][$c] ,'title' => DB()->escape(trim(strip_tags($content[2][$c]))), 'categorie' => $sql[$i]['categorie']);
			}
		}
		unset($snoopy->results);
	}
}

if($releases)
{
	$sql = DB()->build_array('MULTI_INSERT', $releases);
	DB()->query("REPLACE INTO rutor_releases $sql");
}