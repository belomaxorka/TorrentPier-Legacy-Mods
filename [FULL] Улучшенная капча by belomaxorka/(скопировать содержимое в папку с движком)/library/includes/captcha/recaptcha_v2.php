<?php

function recaptcha_v2_get($settings)
{
	return "
		<script type=\"text/javascript\">
			var onloadCallback = function() {
				grecaptcha.render('tp-captcha', {
					'sitekey'  : '" . $settings['public_key'] . "',
					'theme'    : '" . (isset($settings['theme']) ? $settings['theme'] : 'light') . "'
				});
			};
		</script>
		<div id=\"tp-captcha\"></div>
		<script src=\"https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl={$this->settings['language']}\" async defer></script>";
}

function recaptcha_v2_check($settings)
{
	$resp = null;
	$newLib = false;

	if (defined('CLASS_DIR')) {
		require_once(CLASS_DIR . 'recaptcha.php');
		$reCaptcha = new ReCaptcha($settings['secret_key']);
	} elseif (class_exists('\ReCaptcha\ReCaptcha')) {
		$reCaptcha = new \ReCaptcha\ReCaptcha($settings['secret_key']);
		$newLib = true;
	}

	$g_resp = request_var('g-recaptcha-response', '');
	if ($newLib) {
		$resp = $reCaptcha->verify($g_resp, $_SERVER['REMOTE_ADDR']);
		if ($resp->isSuccess()) {
			return true;
		} else {
			return false;
		}
	} else {
		$resp = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $g_resp);
		if ($resp != null && $resp->success) {
			return true;
		} else {
			return false;
		}
	}
}
