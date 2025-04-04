<?php

function hcaptcha_get($settings)
{
	return "
		<div class='h-captcha' data-sitekey='{$settings['site_key']}' data-theme='" . (isset($this->settings['theme']) ? $this->settings['theme'] : 'light') . "'></div>
		<script src='https://www.hCaptcha.com/1/api.js?hl={$this->settings['language']}' async defer></script>";
}

function hcaptcha_check($settings)
{
	$data = array(
		'secret' => $settings['secret_key'],
		'response' => isset($_POST['h-captcha-response']) ? $_POST['h-captcha-response'] : null,
	);

	$verify = curl_init();
	curl_setopt($verify, CURLOPT_URL, 'https://hcaptcha.com/siteverify');
	curl_setopt($verify, CURLOPT_POST, true);
	curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($verify);

	$responseData = json_decode($response);
	if ($responseData->success) {
		return true;
	} else {
		return false;
	}
}
