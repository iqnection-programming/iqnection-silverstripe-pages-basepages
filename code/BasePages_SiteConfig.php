<?php


class BasePages_SiteConfig extends DataExtension
{
	private static $db = array(
		'SiteTreeCacheEnabled' => 'Boolean'
	);
	
	public function updateCMSFields(&$fields)
	{
		if (Permission::check('ADMIN'))
		{
			$fields->addFieldToTab('Root.Caching', CheckboxField::create('SiteTreeCacheEnabled','Cache Site Tree to JSON File')->setDescription('File located at /site-tree.json') );
		}
	}
}