<?php

define( 'DS', DIRECTORY_SEPARATOR );

require_once __DIR__ . DS . ".." . DS . "dh-config.php";
require_once __DIR__ . DS . "dh-actions.php";
require_once __DIR__ . DS . "dh-themes.php";
require_once __DIR__ . DS . "dh-utilities.php";

theme_init();
