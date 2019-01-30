<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

/** Tiny MCE configurations **/
$editor = \SilverStripe\Forms\HTMLEditor\HTMLEditorConfig::get('cms');

$editor->enablePlugins([
	'hr',
	'importcss',
	'charmap',
	'advlist',
	'anchor'
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
$editor->insertButtonsBefore('sslink','anchor');
$editor->removeButtons(['formatselect']);
$editor->setOption('importcss_selector_filter','.text');
$editor->setOption('importcss_append',true);
$editor->setOption('body_class','typography');
$extended_valid_elements = explode(',',$editor->getOption('extended_valid_elements'));
$extended_valid_elements = array_merge(['-ol[start|class]'],$extended_valid_elements);
$editor->setOption('extended_valid_elements',implode(',',$extended_valid_elements));

// https://www.tinymce.com/docs/plugins/importcss/

SilverStripe\Admin\CMSMenu::remove_menu_item('SilverStripe-CampaignAdmin-CampaignAdmin');

