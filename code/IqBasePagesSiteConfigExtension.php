<?php


use SilverStripe\ORM;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms;
use SilverStripe\Control\Director;

class IqBasePagesSiteConfigExtension extends ORM\DataExtension
{
	private static $db = array(
		'SiteTreeCacheEnabled' => 'Boolean'
	);
	
	public function updateCMSFields(Forms\FieldList $fields)
	{
		$tab = $fields->findOrMakeTab('Root.Developer.Caching');
		$tab->push( Forms\CheckboxField::create('SiteTreeCacheEnabled','Cache Site Tree to JSON File')->setDescription('File located at /site-tree.json') );
	}
	
	public function onAfterWrite()
	{
		parent::onAfterWrite();
		// refresh the cache
		$this->owner->generateTemplateCache();
	}
	
	public function getTemplateCachePath($absolute=true)
	{
		$path = (($absolute) ? Director::baseFolder().'/' : null).'template-cache/siteconfig.json';
		$this->owner->extend('updateTemplateCachePath',$path);
		return $path;
	}
	
	public function generateTemplateCache()
	{
		// make sure the cache directory exists
		if (!file_exists(Director::baseFolder().'/template-cache'))
		{
			mkdir(Director::baseFolder().'/template-cache',0755);
			file_put_contents(Director::baseFolder().'/template-cache/.htaccess',"Order deny,allow\nDeny from all\nAllow from 127.0.0.1");
		}
		$cachePath = $this->owner->getTemplateCachePath();
		$cache = array();
		if ($cacheUpdates = $this->owner->extend('updateGeneratedTemplateCache',$cache))
		{
			foreach($cacheUpdates as $cacheUpdate)
			{
				if (is_array($cacheUpdate))
				{
					$cache = array_merge($cache,$cacheUpdate);
				}
			}
		}
		file_put_contents($cachePath,json_encode($cache));
		return json_encode($cache);
	}
	
}