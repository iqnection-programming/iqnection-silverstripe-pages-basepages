<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

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

$editor->setOption('style_formats', [
//	['title' => 'clear', 'styles' => ['clear' => 'both']]
	['title' => 'clear', 'classes' => 'clear']
]);
$editor->setOption('style_formats_merge',true);
$editor->insertButtonsBefore('formatselect','styleselect');
$editor->removeButtons(['formatselect']);
$editor->setOption('importcss_selector_filter','.text');
$editor->setOption('importcss_append',true);

// https://www.tinymce.com/docs/plugins/importcss/



