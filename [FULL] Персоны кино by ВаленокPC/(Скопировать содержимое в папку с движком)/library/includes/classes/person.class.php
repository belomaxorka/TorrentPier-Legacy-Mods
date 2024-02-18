<?php
/**
 * @version 1.0.0
 * Date: 18.02.2024
 */

namespace Person;

class parser
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * getContent
	 *
	 * @param $link
	 * @return bool|string
	 */
	private function getContent($link)
	{
		return parser::getUrlContent(
			array(
				'url' => 'https://www.kinopoisk.ru/name/' . $link . '/',
				'type' => 'GET',
				'returntransfer' => 1,
				'sendHeader' => array(
					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language' => 'ru,en-us;q=0.7,en;q=0.3',
					'Accept-Charset' => 'windows-1251,utf-8;q=0.7,*;q=0.7',
					'Keep-Alive' => '300',
					'Connection' => 'keep-alive',
					'Referer' => 'https://www.kinopoisk.ru/',
				),
				'convert' => array('Windows-1251', 'utf-8'),
			)
		);
	}

	/**
	 * getUrlContent
	 *
	 * @param null $param
	 */
	static function getUrlContent($param = NULL)
	{
		if (is_array($param)) {
			$ch = curl_init();
			if ($param['type'] == 'POST') {
				curl_setopt($ch, CURLOPT_POST, 1);
			}

			if ($param['type'] == 'GET') {
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
			}

			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0');

			if (isset($param['header'])) {
				curl_setopt($ch, CURLOPT_HEADER, 1);
			}

			if (isset($param['location'])) {
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $param['location']);
			}

			curl_setopt($ch, CURLOPT_TIMEOUT, 120);

			if (isset($param['returntransfer'])) {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			}

			curl_setopt($ch, CURLOPT_URL, $param['url']);

			if (isset($param['postfields'])) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postfields']);
			}

			if (isset($param['cookie'])) {
				curl_setopt($ch, CURLOPT_COOKIE, $param['cookie']);
			}

			if (isset($param['sendHeader'])) {
				$header = array();
				foreach ($param['sendHeader'] as $k => $v) {
					$header[] = $k . ': ' . $v . "\r\n";
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			}

			if (isset($param['referer'])) {
				curl_setopt($ch, CURLOPT_REFERER, $param['referer']);
			}

			if (isset($param['userpwd'])) {
				curl_setopt($ch, CURLOPT_USERPWD, $param['userpwd']);
			}

			$result = curl_exec($ch);
			curl_close($ch);

			if (isset($param['convert'])) {
				$result = iconv($param['convert'][0], $param['convert'][1], $result);
			}

			return $result;
		}

		return false;
	}

	/**
	 * GetParser
	 *
	 * @param $id
	 * @return array|false
	 */
	public function GetParser($id)
	{
		$info = array();
		$parse = array(
			'name' => '#<h1.*?class="moviename-big" itemprop="name".*?>([а-яА-Я].*?)</h1>#si',
			'originalname' => '#<span itemprop="alternativeHeadline">([a-zA-Z].*?)</span>#si',
			'genre' => '#жанры</td><td[^>]*>[^<]*<span>(.*?)</span>#si',
			'gender' => '#((супруг|супруга).*?)</td>.*?<td class="female">#si',
			'birthdate' => '#дата рождения</td>.*?<td class="birth" birthdate="(.*?)">#si',
			'birthplace' => '#место рождения</td><td[^>]*>[^<]*<span>(.*?)</span>#si',
			'career' => '#карьера</td><td[^>]*>(.*?)</td></tr>#si',
			'poster_url' => '#<div id="photoBlock".*?<img.*? src="(.*?)"[^>]*>#si',
		);

		$html = parser::getContent($id);
		$html = str_replace("charset=windows-1251", "charset=utf-8", $html);

		foreach ($parse as $index => $value) {
			if (preg_match($value, $html, $matches)) {
				if ($index == 'poster_url') {
					if (is_dir(DATA_DIR . 'person/')) {
						$info[$index] = $this->PhotoInit($matches[1], $id);
					} else {
						return false;
					}
				} else {
					$info[$index] = preg_replace('#\\n\s*#si', '', html_entity_decode(strip_tags($matches[1]), ENT_COMPAT | ENT_HTML401, 'UTF-8'));
					$info[$index] = $this->ResultClear($info[$index], $index);
				}
			}
		}

		return $info;
	}

	/**
	 * PhotoInit
	 *
	 * @param $url
	 * @param $id
	 * @return string
	 */
	private function PhotoInit($url, $id)
	{
		global $bb_cfg;

		$PathInfo = pathinfo($url);
		if ($PathInfo['filename'] !== 'photo_none') {
			$PhotoName = "photo_$id." . $PathInfo['extension'];
			$PhotoPatch = $bb_cfg['pers_photo_dir'] . '/' . $PhotoName;
			if (!file_exists($PhotoPatch))
				file_put_contents($PhotoPatch, file_get_contents($url));
		} else {
			$PhotoName = 'photo_none.png';
		}

		return $PhotoName;
	}

	/**
	 * ResultClear
	 * @param $val
	 * @param string $key
	 * @return array|string|string[]
	 */
	private function ResultClear($val, $key = '')
	{
		if (empty($val) || $val == '-') {
			$val = '';
		} else {
			$pattern = array('&nbsp;', '&laquo;', '&raquo;');
			$pattern_replace = array(' ', '', '');
			$val = str_replace($pattern, $pattern_replace, $val);
		}
		switch ($key) {
			case 'genre':
			case 'career':
				$val = str_replace(', ...', '', $val);
				break;
		}
		return $val;
	}
}
