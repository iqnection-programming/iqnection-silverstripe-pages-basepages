<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

/** Use the environment file in /mysite/ to put the system in dev mode **/
if(file_exists(BASE_PATH.'/mysite/.env'))
{
	$loader = new \SilverStripe\Core\EnvironmentLoader();
	$loader->loadFile(BASE_PATH.'/mysite/.env');
}

/** Tiny MCE configurations **/
$editor = \SilverStripe\Forms\HTMLEditor\HTMLEditorConfig::get('cms');

$editor->enablePlugins([
	'hr',
	'importcss',
]);
$editor->addButtonsToLine(1,[
	'blockquote',
	'subscript',
	'superscript',
	'hr'
]);

$editor->insertButtonsBefore('formatselect','styleselect');
$editor->setOption('importcss_selector_filter','.text');

// https://www.tinymce.com/docs/plugins/importcss/



