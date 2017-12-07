<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

use SilverStripe\Core\EnvironmentLoader;
if(file_exists(BASE_PATH.'/mysite/.env'))
{
	$loader = new EnvironmentLoader();
	$loader->loadFile(BASE_PATH.'/mysite/.env');
}