<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang, $bb_cfg, $userdata;

set_time_limit(600);

$redirect = (string)@$this->request['redir'];

$this->response['html'] = '';

switch ($this->request['type']) {
	case 'delete':
		$pers_id = (int)$this->request['pers_id'];
		if ($pers_id) {
			if (empty($this->request['confirmed'])) {
				$this->prompt_for_confirm($lang['PERS_CONFIRM_DELETE']);
			}

			$sql = "DELETE FROM " . BB_PERSON . " WHERE pers_id = $pers_id";
			$result = DB()->sql_query($sql);
			if (!$result) {
				$this->ajax_die('Could not removed disallowed user');
			}

			$this->response['hide'] = true;
			$this->response['pers_id'] = $pers_id;

			if ($redirect) {
				$this->response['redirect'] = make_url($redirect);
			}
		} else {
			$this->ajax_die(sprintf($lang['SORRY_AUTH_DELETE'], strip_tags($is_auth['auth_delete_type'])));
		}
		break;
	case 'up_post':
		$dist_actor = DB()->fetch_rowset("SELECT t.topic_id, t.topic_title, ph.post_id, ph.post_html
                            FROM " . BB_TOPICS . " t
                            LEFT JOIN " . BB_POSTS . " p ON(p.post_id         = t.topic_first_post_id)
                            LEFT JOIN " . BB_FORUMS . " f ON(p.forum_id       = f.forum_id)
                            LEFT JOIN " . BB_POSTS_HTML . " ph ON(ph.post_id  = p.post_id)
                            WHERE f.allow_reg_tracker
                            AND p.post_id = ph.post_id
                            AND t.topic_id = p.topic_id
                        ");

		$ActList = DB()->fetch_rowset("SELECT pers_id, pers_runame, pers_enname FROM " . BB_PERSON);

		foreach ($dist_actor as $i => $postrow) {
			$name = $replace = array();
			$message = $postrow['post_html'];

			if ($ActList) {
				foreach ($ActList as $row) {
					$runame = explode(',', $row['pers_runame']);
					$enname = explode(',', $row['pers_enname']);
					$array_name = array_diff(array_merge($runame, $enname), array(''));

					foreach ($array_name as $key => $word) {

						$repl = str_replace(array('{id}', '{name}'), array($row['pers_id'], $word), $bb_cfg['pers_repl_text']);

						if (!preg_match("/" . preg_quote($repl, '/') . "/i", $message, $matches)) {
							$name[] = $word;
							$replace[] = $repl;
						} else break;
					}
				}

				$message = str_replace($name, $replace, $message, $count);
				$topic_url = TOPIC_URL . $postrow['topic_id'];
				$topic_title = "<a title='{$postrow['topic_title']}' class='med' href='$topic_url'>" . wbr(str_short($postrow['topic_title'], 45)) . "</a>";

				if ($count) {
					DB()->shutdown['post_html'][] = array(
						'post_id' => (int)$postrow['post_id'],
						'post_html' => (string)$message,
					);
					$this->response['html'] .= "<li><p>В $topic_title - Заменено: " . declension($count, 'TIMES') . "</p></li>";
				} else {
					$this->response['html'] .= "<li><p>В $topic_title - Не чего не найдено</p></li>";
				}
			}
		}
		break;
	case 'auto_parser':
		if ($bb_cfg['pers_idkp_list']) {
			$kpID = explode(",", str_replace(' ', '', $bb_cfg['pers_idkp_list']));
			$IDCount = count($kpID);
			$this->response['idcount'] = $IDCount;
			$step = (int)$this->request['step'];
			$this->response['stop'] = false;

			if (is_array($kpID)) {
				$PersInfo = new Person\parser();
				if ($IDCount > $step) {
					$id = $kpID[$step];
					$PersData = $PersInfo->GetParser($id);
					if ($PersData) {
						$nameRU = (isset($PersData['name'])) ? trim($PersData['name']) : '';
						$nameEN = (isset($PersData['originalname'])) ? trim($PersData['originalname']) : '';

						if (!$nameRU) {
							$this->response['html'] .= "<li style=\"color:#b44642;\"><p>" . sprintf($lang['AD_PERS_PARSER_ERROR_NOT_NAME'], $id) . "</p></li>";
							break;
						}

						$persname_sql = DB()->escape($nameRU);

						if ($row = DB()->fetch_row("SELECT pers_id, pers_runame FROM " . BB_PERSON . " WHERE pers_runame = '$persname_sql' LIMIT 1")) {
							$this->response['html'] .= "<li style=\"color:#6876B4;\"><p>" . sprintf($lang['AD_PERS_PARSER_THERE'], $row['pers_id'], $nameRU) . "</p></li>";
							break;
						}

						$sql_ary = array(
							'kp_id' => (int)$id,
							'pers_runame' => (string)$nameRU,
							'pers_enname' => (string)$nameEN,
							'pers_career' => (string)(isset($PersData['career'])) ? $PersData['career'] : '',
							'pers_gender' => (int)((isset($PersData['gender'])) ? (($PersData['gender'] == 'супруг') ? 2 : 1) : 0),
							'pers_foto' => (string)(isset($PersData['poster_url'])) ? $PersData['poster_url'] : '',
							'pers_birthdate' => (string)(isset($PersData['birthdate'])) ? $PersData['birthdate'] : '',
							'pers_birthplace' => (string)(isset($PersData['birthplace'])) ? $PersData['birthplace'] : '',
						);

						$sql_args = DB()->build_array('INSERT', $sql_ary);
						// Create new Person
						DB()->query("INSERT INTO " . BB_PERSON . " $sql_args");
						$idPers = DB()->sql_nextid();
						$this->response['html'] .= "<li><p>" . sprintf($lang['AD_PERS_PARSER_ADD_NEWS'], $idPers, $PersData['name']) . "</p></li>";
					} else {
						$this->response['html'] .= "<li style=\"color:#b44642;\"><p>" . sprintf($lang['AD_PERS_PARSER_NOT_FOUND'], $id) . "</p></li>";
						$this->response['stop'] = true;
						break;
					}
				}
			} else {
				$this->response['html'] .= "<li style=\"color:#b44642;\"><p>{$lang['AD_PERS_PARSER_ERROR']}</p></li>";
				$this->response['stop'] = true;
			}
		} else {
			$this->response['html'] .= "<li style=\"color:#b44642;\"><p>{$lang['AD_PERS_PARSER_LIST_EMPTY']}</p></li>";
			$this->response['stop'] = true;
		}
		break;
	case 'filling':
		$KpID = (int)$this->request['id'];
		$PersInfo = new Person\parser();
		$PersData = $PersInfo->GetParser($KpID);
		if ($PersData) {
			$this->response['runame'] = (isset($PersData['name'])) ? trim($PersData['name']) : '';
			$this->response['enname'] = (isset($PersData['originalname'])) ? trim($PersData['originalname']) : '';
			$this->response['career'] = (isset($PersData['career'])) ? $PersData['career'] : '';
			$this->response['gender'] = ((isset($PersData['gender'])) ? (($PersData['gender'] == 'супруг') ? 2 : 1) : 0);
			$this->response['foto'] = (isset($PersData['poster_url'])) ? $PersData['poster_url'] : '';
			$this->response['birthdate'] = (isset($PersData['birthdate'])) ? $PersData['birthdate'] : '';
			$this->response['birthplace'] = (isset($PersData['birthplace'])) ? $PersData['birthplace'] : '';
		} else {
			$this->ajax_die($lang['AD_PERS_PARSER_INVALID_ID']);
		}
		break;
	default:
		$this->ajax_die('empty type: ' . $this->request['type']);
		break;
}
