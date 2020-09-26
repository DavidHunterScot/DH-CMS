<?php

$config = array(
	'name' => 'CMS Test',
	'theme' => 'test-theme',
	'lang' => 'en-GB',
	'charset' => 'UTF-8',
	'url' => 'http' . ( isset( $_SERVER['HTTPS'] ) ? "s" : "" ) . '://' . $_SERVER['HTTP_HOST']
);
