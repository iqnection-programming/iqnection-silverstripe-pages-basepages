<?php

namespace IQnection\BasePage;

use SilverStripe\ORM;
use SilverStripe\Forms;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Director;

class PageExtension extends ORM\DataExtension
{				
	private static $db = array(
		"SidebarContent" => "HTMLText",
		"LeftColumn" => "HTMLText",
		"CenterColumn" => "HTMLText",
		"RightColumn" => "HTMLText",
		'AdditionalCode' => 'Text',
		"Target" => "Enum('_blank,_new,_parent,_self,_top','_self')",
	);	
	
	private static $defaults = array(
		"Target" => "_self"
	);
	
	public function updateCMSFields(Forms\FieldList $fields)
	{
		$tab = $fields->findOrMakeTab('Root.Developer.AdditionalCode');
		$tab->push( $codeField = Forms\TextareaField::create('AdditionalCode','Additional HTML/JS/CSS Code Placed before </body> tag',50)->addExtraClass('monotype') );
		$codeField->addExtraClass('stacked');
		$codeField->setRows(45);
//		$codeField->setMode('html');
				
		if($this->owner->ClassName == "Page")
		{
			$fields->addFieldToTab("Root.Columns", Forms\HTMLEditor\HTMLEditorField::create("LeftColumn", "Left Column Content")->addExtraClass('stacked') );  
			$fields->addFieldToTab("Root.Columns", Forms\HTMLEditor\HTMLEditorField::create("CenterColumn", "Center Column Content")->addExtraClass('stacked') );
			$fields->addFieldToTab("Root.Columns", Forms\HTMLEditor\HTMLEditorField::create("RightColumn", "Right Column Content")->addExtraClass('stacked') ); 
			$fields->addFieldToTab("Root.Sidebar", Forms\HTMLEditor\HTMLEditorField::create("SidebarContent", "Sidebar Content")->addExtraClass('stacked') );
		}
			
		return $fields;
	}
	
	public function updateSettingsFields(Forms\FieldList $fields)
	{
		$fields->addFieldToTab("Root.Settings", Forms\DropdownField::create("Target", "Link Target", array(
			"_self"=>"Same Tab",
			"_blank"=>"New Tab"
		)));
		
		return $fields;
	}
	
	public function RefreshCacheVars()
	{
		$vars = array(
			'ID',
			'ClassName',
			'ParentID',
			'Title',
			'URLSegment',
			'ShowInMenus'
		);
		$vars = $this->owner->updateRefreshCacheVars($vars);
		return $vars;
	}
	
	public function updateRefreshCacheVars($vars) { return $vars; }
	
	public function onAfterWrite()
	{
		parent::onAfterWrite();
		
		$refreshCache = false;
		foreach($this->RefreshCacheVars() as $var)
		{
			if ($this->owner->isChanged($var))
			{
				$refreshCache = true;
				break;
			}
		}
		if ( ($refreshCache) || (!file_exists(Director::baseFolder().'/site-tree.json')) ) { $this->owner->cacheSiteTree(); }
		// remove the template cache file so it's regenerated on next request
		if ($refreshCache)
		{
			if (file_exists($this->getTemplateCachePath())) { unlink($this->getTemplateCachePath()); }
			if (file_exists(dirname($this->getTemplateCachePath())))
			{
				foreach(scandir(dirname($this->getTemplateCachePath())) as $file)
				{
					if (!preg_match("/^\.|\.$/",$file))
					{
						$path = dirname($this->getTemplateCachePath()).'/'.$file;
						unlink($path);
					}
				}
			}
		}
		
	}
	
	public function updateTemplateCachePath($path,$absolute=true) { return $path; }
	
	public function getTemplateCachePath($absolute=true)
	{
		$path = (($absolute) ? Director::baseFolder().'/' : null).'template-cache/page-'.$this->owner->ID.'.json';
		$path = $this->owner->updateTemplateCachePath($path,$absolute);
		return $path;
	}
		
	/**
	 * Caches the site tree for use in Pinnacle scripts
	 * Stores it to the site root,
	 * file is hashed for the current domain so there is a different file for each 
	 */
	public function cacheSiteTree()
	{
		if (!SiteConfig::current_site_config()->SiteTreeCacheEnabled) { return;	}
		$cache = array();
		foreach(\Page::get()->filter('ParentID','0') as $page)
		{
			$cache['SiteTree'][$page->ID] = $page->dataForCache();
		}
		$cache = $this->owner->updateCachedSiteTree($cache);
		file_put_contents(Director::baseFolder().'/site-tree.json',json_encode($cache));
	}
	
	public function updateCachedSiteTree($cache) { return $cache; }
	
	/**
	 * generates the array of cached data for the current page
	 * adds all children to the array
	 * This method can be overloaded to add additional details about the object
	 * @returns array
	 */
	public function dataForCache()
	{
		$cache = array();
		$cache['ID'] = $this->owner->ID;
		$cache['Title'] = $this->owner->Title;
		$cache['Link'] = $this->owner->Link();
		$cache['AbsoluteLink'] = $this->owner->AbsoluteLink();
		$cache['BasePath'] = Director::absoluteURL($this->owner->RelativeLink());
		$cache['ClassName'] = $this->owner->ClassName;
		$cache['TemplateCacheFilename'] = $this->getTemplateCachePath(false);
		$cache['Children'] = array();
		foreach($this->owner->Children() as $child)
		{
			$cache['Children'][$child->ID] = $child->dataForCache();
		}
		$cache = $this->owner->updateDataForCache($cache);
		return $cache;
	}
	
	public function updateDataForCache($cache) { return $cache; }			
}
