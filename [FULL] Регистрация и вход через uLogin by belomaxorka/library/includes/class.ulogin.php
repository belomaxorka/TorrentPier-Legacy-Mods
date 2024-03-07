<?php
/**
 * Auth via uLogin.ru
 * @package phpBB
 * @subpackage uLogin MOD
 * @author uLogin team@ulogin.ru http://ulogin.ru/
 * @license GPL3
 */

class uLogin
{
	/**
	 * uLogin user data
	 *
	 * @var array
	 */
	private $user = null;

	/**
	 * Max nesting level
	 *
	 * @var int
	 */
	private $max_level = 5;

	/**
	 * Ulogin constructor
	 */
	public function __construct()
	{
		$this->get_user();
	}

	/**
	 * Get current user email or generate random
	 *
	 * @param bool $random
	 * @return mixed|string
	 */
	public function email($random = false)
	{
		if (!empty($this->user['email'])) {
			DB()->fetch_row("SELECT * FROM " . BB_USERS . " WHERE user_email = '" . DB()->escape($this->user['email']) . "'");
			return $this->user['email'];
		}

		return '';
	}

	/**
	 * Get current user name or generate random
	 *
	 * @param string $name
	 * @param int $level
	 * @return mixed|string|void
	 */
	public function name($name = '', $level = 0)
	{
		if ($level == $this->max_level) {
			return '';
		}

		if ($name) {
			$name = $name . $this->random(1);
		} else if (!empty($this->user['first_name']) && !empty($this->user['last_name'])) {
			$name = $this->user['first_name'] . ' ' . $this->user['last_name'];
		} elseif (!empty($this->user['email']) && preg_match('/^(.+)\@/i', $this->user['email'], $nickname)) {
			$name = $nickname[1];;
		} else if (!empty($this->user['first_name'])) {
			$name = $this->user['first_name'];
		} else if (!empty($this->user['last_name'])) {
			$name = $this->user['last_name'];
		} else {
			return;
		}

		if (DB()->fetch_row("SELECT * FROM " . BB_USERS . " WHERE username = '" . DB()->escape($name) . "'")) {
			return $this->name($name, ($level + 1));
		}

		return $name;
	}

	/**
	 * Get current user location (city/country)
	 *
	 * @return string
	 */
	public function from()
	{
		if (!empty($this->user['country']) && !empty($this->user['city'])) {
			return ucfirst(strtolower($this->user['country'])) . ', ' . ucfirst(strtolower($this->user['city']));
		} else if (!empty($this->user['country'])) {
			return ucfirst(strtolower($this->user['country']));
		} else if (!empty($this->user['city'])) {
			return ucfirst(strtolower($this->user['city']));
		}

		return '';
	}

	/**
	 * Read response with available wrapper
	 *
	 * @param string $url
	 * @return bool|string|string[]
	 */
	private function get_response($url = "")
	{
		$s = array("error" => "file_get_contents or curl required");

		if (in_array('curl', get_loaded_extensions())) {
			$request = curl_init($url);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($request, CURLOPT_BINARYTRANSFER, 1);
			$result = curl_exec($request);
			$s = $result ?: $s;
		} elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
			$result = file_get_contents($url);
			$s = $result ?: $s;
		}

		return $s;
	}

	/**
	 * Generates password
	 *
	 * @param int $len
	 * @param string $char_list
	 * @return string
	 */
	public function password($len = 6, $char_list = 'a-z,0-9')
	{
		$chars = array();

		$chars['a-z'] = 'qwertyuiopasdfghjklzxcvbnm';
		$chars['A-Z'] = strtoupper($chars['a-z']);
		$chars['0-9'] = '0123456789';
		$chars['~'] = '~!@#$%^&*()_+=-:";\'/\\?><,.|{}[]';

		$charset = $password = '';

		if (!empty($char_list)) {
			$char_types = explode(',', $char_list);

			foreach ($char_types as $type) {
				if (array_key_exists($type, $chars)) {
					$charset .= $chars[$type];
				} else {
					$charset .= $type;
				}
			}
		}

		for ($i = 0; $i < $len; $i++) {
			$password .= $charset[rand(0, strlen($charset) - 1)];
		}

		return $password;
	}

	/**
	 * Get user from ulogin.ru by token
	 *
	 * @return mixed
	 */
	private function get_user()
	{
		if ($this->user) {
			return $this->user;
		}

		if ($_POST['token']) {
			$info = $this->get_response('https://ulogin.ru/token.php?token=' . $_POST['token']);

			if (function_exists('json_decode')) {
				$this->user = json_decode($info, true);
			}

			return $this->user;
		}

		return null;
	}

	/**
	 * Generate random string
	 *
	 * @param int $length
	 * @return string
	 */
	public function random($length = 10)
	{
		return make_rand_str($length);
	}

	/**
	 * Auth user
	 *
	 * @return bool
	 */
	public function auth()
	{
		if (empty($this->user['email'])) return false;

		if (!$row = DB()->fetch_row("SELECT * FROM bb_ulogin WHERE identity = '" . DB()->escape($this->user['identity']) . "'")) {
			return false;
		}

		if (!$user = DB()->fetch_row("SELECT * FROM " . BB_USERS . " WHERE user_id = " . $row['userid'])) {
			DB()->query("DELETE FROM bb_ulogin WHERE userid = " . $row['userid']);
			return false;
		}

		return $user;
	}

	/**
	 * Identity
	 *
	 * @return mixed|string
	 */
	public function identity()
	{
		return !empty($this->user['identity']) ? $this->user['identity'] : '';
	}
}
