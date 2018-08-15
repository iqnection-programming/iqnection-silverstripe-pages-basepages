<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

/** Tiny MCE configurations **/
$editor = \SilverStripe\Forms\HTMLEditor\HTMLEditorConfig::get('cms');

$editor->enablePlugins([
	'hr',
	'importcss',
	'charmap'
]);
$editor->disablePlugins([
	'contextmenu'
]);
$editor->addButtonsToLine(1,[
	'blockquote',
	'subscript',
	'superscript',
	'hr'
]);

//$editor->setOption('style_formats', [
////	['title' => 'clear', 'styles' => ['clear' => 'both']]
//	['title' => 'cleartext', 'classes' => ".clear"]
//]);
$editor->setOption('style_formats_merge',true);
$editor->insertButtonsBefore('code','charmap');
$editor->insertButtonsBefore('formatselect','styleselect');
$editor->removeButtons(['formatselect']);
$editor->setOption('importcss_selector_filter','.text');
$editor->setOption('importcss_append',true);
$editor->setOption('body_class','typography');

// https://www.tinymce.com/docs/plugins/importcss/

SilverStripe\Admin\CMSMenu::remove_menu_item('SilverStripe-CampaignAdmin-CampaignAdmin');

