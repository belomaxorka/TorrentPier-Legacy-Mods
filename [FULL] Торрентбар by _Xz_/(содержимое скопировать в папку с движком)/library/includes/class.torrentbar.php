<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

@set_time_limit(0);

/**
 * Очистка кеша
 * Пример: unlink_dir(BB_ROOT . 'torrentbar/cache/')
 *
 * @param $dir
 * @return void
 */
function unlink_dir($dir)
{
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				unlink($dir . $file);
			}
		}
		@closedir($dir);
	}
}

class torrentbar
{
	/**
	 * Создать торрентбар
	 *
	 * @param $user_id
	 * @return void
	 */
	function create($user_id)
	{
		$check = $this->check($user_id);

		// Вывод
		if (!$check) {
			$this->create_user($user_id);
		}
		$this->output($user_id);
	}

	/**
	 * Обновить торрент-бар
	 *
	 * @param $user_id
	 * @return void
	 */
	function create_user($user_id)
	{
		$data = $this->sql($user_id);
		$this->make($data);
	}

	/**
	 * Создаем torrentbar для всех пользователей
	 *
	 * @return void
	 */
	function create_all()
	{
		$row = DB()->fetch_rowset("
			SELECT u.*, bu.u_up_total, bu.u_down_total, bu.u_up_release, bu.u_up_bonus
			FROM " . BB_USERS . " u
				LEFT JOIN " . BB_BT_USERS . " bu ON(bu.user_id = u.user_id)
			WHERE user_active = 1");

		foreach ($row as $data) {
			$this->make($data);
		}
	}

	/**
	 * Получаем данные пользователя
	 *
	 * @param $user_id
	 * @return mixed
	 */
	function sql($user_id)
	{
		return DB()->fetch_row("
			SELECT u.*, bu.u_up_total, bu.u_down_total, bu.u_up_release, bu.u_up_bonus
			FROM " . BB_USERS . " u
				LEFT JOIN " . BB_BT_USERS . " bu ON(bu.user_id = u.user_id)
			WHERE u.user_id = $user_id
				AND user_active = 1");
	}

	/**
	 * Создаём
	 *
	 * @param $row
	 * @return void
	 */
	function make($row)
	{
		global $bb_cfg, $domain_name;

		if (!$row['user_bar'])// && (($row['user_level'] == ADMIN) || ($row['user_level'] == MOD)) делать принудительно бар если он отключен в настройках профиля
		{
			$row['user_bar'] = 'original';
		}

		if ($bar = $row['user_bar']) {
			$username = str_replace(array('@', '~', "'"), array('a', '——', '"'), $row['username']);
			$username = html_entity_decode($username);

			$user_id = $row['user_id'];
			$ext_id = $row['avatar_ext_id'];
			$allow_avatar = !bf($row['user_opt'], 'user_opt', 'dis_avatar');

			$user_avatar = $bb_cfg['avatars']['upload_path'] . $bb_cfg['avatars']['no_avatar'];

			if ($user_id == BOT_UID && $bb_cfg['avatars']['bot_avatar']) {
				$user_avatar = $bb_cfg['avatars']['upload_path'] . $bb_cfg['avatars']['bot_avatar'];
			} elseif ($allow_avatar && $ext_id) {
				if (file_exists($bb_cfg['avatars']['upload_path'] . get_avatar_path($user_id, $ext_id))) {
					$user_avatar = $bb_cfg['avatars']['upload_path'] . get_avatar_path($user_id, $ext_id);
				}
			}

			$userbar = ImageCreateFromPNG(TORRENTBAR_DIR . 'images/' . $bar . '.png');

			// Узнаём размеры и тип аватары
			list($avatar_width, $avatar_height, $type) = getimagesize($user_avatar);
			// Открываем
			switch ($type) {
				case 1:
					$avatar = ImageCreateFromGIF($user_avatar);
					break;
				case 2:
					$avatar = ImageCreateFromJPEG($user_avatar);
					break;
				case 3:
					$avatar = ImageCreateFromPNG($user_avatar);
					break;
			}

			// уменьшаем аватару
			$avatar_size = 51;

			if ($avatar_width > $avatar_height) {
				$avatar_bar_width = $avatar_size;
				$avatar_bar_height = $avatar_size * ($avatar_height / $avatar_width);
			} else {
				$avatar_bar_height = $avatar_size;
				$avatar_bar_width = $avatar_size * ($avatar_width / $avatar_height);
			}

			// Создаём уменьшенную аватару
			if (($avatar_width > $avatar_size) || ($avatar_height > $avatar_size)) {
				$avatar_bar = ImageCreateTrueColor($avatar_width, $avatar_height);

				ImageAlphaBlending($avatar_bar, false);
				ImageSaveAlpha($avatar_bar, true);

				ImageCopyResampled($avatar_bar, $avatar, 0, 0, 0, 0, $avatar_bar_width, $avatar_bar_height, $avatar_width, $avatar_height);
			} else {
				$avatar_bar = $avatar;
				$avatar_bar_width = $avatar_width;
				$avatar_bar_height = $avatar_height;
			}

			$w = 296;
			$h = 3;
			if ($avatar_bar_width > $avatar_bar_height) {
				$h = $h + ($avatar_bar_width - $avatar_bar_height) / 2;
			}
			if ($avatar_bar_width < $avatar_bar_height) {
				$w = $w + ($avatar_bar_height - $avatar_bar_width) / 2;
			}
			if ($avatar_width < $avatar_size) {
				$h = $h + ($avatar_size - $avatar_bar_height) / 2;
			}
			if ($avatar_height < $avatar_size) {
				$w = $w + ($avatar_size - $avatar_bar_height) / 2;
			}
			// Наносим уменьшенную аватару
			ImageCopy($userbar, $avatar_bar, $w, $h, 0, 0, $avatar_bar_width, $avatar_bar_height);

			$ratio = (get_bt_ratio($row)) ? 'R: ' . get_bt_ratio($row) : 'R: ---';

			$up = 'U: ' . humn_size($row['u_up_total'] + $row['u_up_bonus'] + $row['u_up_release'], '', '', '');
			$down = 'D: ' . humn_size($row['u_down_total'], '', '', '');

			// Задаем цвет для шрифта
			$blue = imageColorAllocate($userbar, 0, 121, 190);
			$black = imageColorAllocate($userbar, 0, 0, 0);
			$red = imageColorAllocate($userbar, 255, 0, 0);
			$original = $green = imageColorAllocate($userbar, 0, 121, 90);
			$orange = imageColorAllocate($userbar, 234, 102, 69);
			$color = $$bar;

			// Задаем размер шрифта
			$size = '12';
			switch ($row['user_rank']) {
				case 1:
					$rank = 'Administrator';
					$rank_color = $red;
					$l2 = 208;
					break;
				case 2:
					$rank = 'Moderator';
					$rank_color = imageColorAllocate($userbar, 40, 118, 33);
					$l2 = 229;
					break;
				case 3:
					$rank = 'Super Moderator';
					$rank_color = imageColorAllocate($userbar, 40, 118, 33);
					$l2 = 192;
					break;
				case 4:
					$rank = 'Vip';
					$rank_color = imageColorAllocate($userbar, 0, 126, 127);
					$l2 = 268;
					break;
				case 5:
					$rank = 'Super Uploader';
					$rank_color = imageColorAllocate($userbar, 164, 118, 63);
					$l2 = 200;
					break;
				case 6:
					$rank = 'Uploader';
					$rank_color = imageColorAllocate($userbar, 241, 129, 78);
					$l2 = 236;
					break;
				case 7:
					$rank = 'Super User';
					$rank_color = imageColorAllocate($userbar, 203, 61, 79);
					$l2 = 224;
					break;
				case 8:
					$rank = 'Power User';
					$rank_color = imageColorAllocate($userbar, 215, 166, 160);
					$l2 = 221;
					break;
				case 9:
					$rank = 'Leecher';
					$rank_color = imageColorAllocate($userbar, 161, 161, 161);
					$l2 = 245;
					break;
				case 0:
					$rank = 'User';
					$rank_color = imageColorAllocate($userbar, 71, 105, 150);
					$l2 = 261;
					break;
			}

			if ($row['user_id'] == 2 || $row['user_id'] == 1) {
				$rank = 'прородитель всего и вся xD';
				$l2 = 124;
			}

			$font_domain = TORRENTBAR_DIR . 'fonts/domain.ttf';
			$font_username = TORRENTBAR_DIR . 'fonts/username.ttf';
			$font_rank = TORRENTBAR_DIR . 'fonts/user_rank.ttf';
			$font_ratio = TORRENTBAR_DIR . 'fonts/ratio.ttf';

			for ($i = 18, $w = 22; $i >= 10; $i--) {
				if (($username_l = 290 - $this->text_l($i, $font_username, $username)) > 145) {
					ImageTTFtext($userbar, $i, 0, $username_l, $w, $color, $font_username, $username);
					break;
				}
				$w = $w - 1 / 2;
			}

			// Наносим надписи
			ImageTTFtext($userbar, 12, 0, $l2, 37, $rank_color, $font_rank, $rank);
			// Ссылка на трекер
			ImageTTFtext($userbar, 12, 0, 7, 20, 0, $font_domain, $domain_name);

			$ratio_l = $this->text_l(8, $font_ratio, $ratio);
			$up_l = $this->text_l(8, $font_ratio, $up);
			$down_l = $this->text_l(8, $font_ratio, $down);

			$lp = (190 - ($ratio_l + $up_l + $down_l)) / 2;

			$leech = imageColorAllocate($userbar, 148, 14, 17);
			$seed = imageColorAllocate($userbar, 0, 103, 41);
			$rat = imageColorAllocate($userbar, 0, 30, 180);

			$l = 290 - ($ratio_l + $up_l + $down_l + $lp + $lp);
			ImageTTFtext($userbar, 8, 0, 100, 53, $rat, $font_ratio, $ratio);
			$l = 290 - ($up_l + $down_l + $lp);
			ImageTTFtext($userbar, 8, 0, $l, 53, $seed, $font_ratio, $up);
			$l = 290 - ($down_l);
			ImageTTFtext($userbar, 8, 0, $l, 53, $leech, $font_ratio, $down);

			// Сохраняем рисунок в формате PNG
			ImagePNG($userbar, TORRENTBAR_DIR . 'cache/' . $row['user_id'] . '.png');

			// Освобождаем память
			ImageDestroy($userbar);
			ImageDestroy($avatar);
		}
	}

	static function text_l($size, $font, $text)
	{
		$l = imagettfbbox($size, 0, $font, $text);
		return $l[2];
	}

	/**
	 * Проверка в кеше
	 *
	 * @param $user_id
	 * @return bool
	 */
	function check($user_id)
	{
		if (@fopen(TORRENTBAR_DIR . 'cache/' . $user_id . '.png', "r")) {
			return true;
		}
		return false;
	}

	/**
	 * Вывод
	 *
	 * @param $user_id
	 * @return void
	 */
	function output($user_id)
	{
		header('Content-type: image/png');
		header('Content-Disposition: filename=' . $user_id . '.png');
		@readfile(TORRENTBAR_DIR . 'cache/' . $user_id . '.png');
		exit;
	}
}

/**
 * torrentbar
 * Примечание: если нужны бары для всех, запускаем функцию с type = all
 *
 * @param $user_id
 * @param $type
 * @return void
 */
function torrentbar($user_id, $type = false)
{
	global $bar;

	if (!isset($bar)) {
		$bar = new torrentbar();
	}

	if (!$type) {
		$bar->create($user_id);
	} else if ($type == 'all') {
		$bar->create_all();
	} else if ($type == 'user') {
		// Для обновления уже сохраненного бара torrentbar($user_id, 'user');
		$bar->create_user($user_id);
	}
}
