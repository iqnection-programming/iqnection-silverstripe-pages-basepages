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
	
	public function onAfterWrite()
	{
		parent::onAfterWrite();
		// refresh the cache
		$this->owner->generateTemplateCache();
	}
	
	public function getTemplateCachePath($absolute=true)
	{
		$path = (($absolute) ? BASE_PATH.'/' : null).'template-cache/siteconfig.json';
		$this->owner->extend('updateTemplateCachePath',$path);
		return $path;
	}
	
	public function generateTemplateCache()
	{
		// make sure the cache directory exists
		if (!file_exists(BASE_PATH.'/template-cache'))
		{
			mkdir(BASE_PATH.'/template-cache',0755);
		}
		$cachePath = $this->owner->getTemplateCachePath();
		$cache = array();
		if ($cacheUpdates = $this->owner->extend('updateGeneratedTemplateCache',$cache))
		{
			foreach($cacheUpdates as $cacheUpdate)
			{
				$cache = array_merge($cache,$cacheUpdate);
			}
		}
		file_put_contents($cachePath,json_encode($cache));
		return json_encode($cache);
	}
	
}