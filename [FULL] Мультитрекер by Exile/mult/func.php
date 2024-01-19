<?php

function n($class, $name = false)
{
	return $name ? $class : new $class();
}

function longval($string)
{
	$string = floatval(trim($string));
	$a = explode('.', $string);
	$i = reset($a);
	return $i;
}

function arr_cleanup($arr)
{
	$res = array();
	for ($i = 0, $j = 0; $i < sizeof($arr); $i++)
		if ($arr[$i] !== '') $res[$j++] = $arr[$i];
	return $res;
}
