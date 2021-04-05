<?php

namespace IQnection\Base;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Director;
use SwiftDevLabs\CodeEditorField\Forms\CodeEditorField;

class PageExtension extends DataExtension
{
	private static $db = [
        'SidebarContent' => 'HTMLText',
        'AdditionalHeadCode' => 'Text',
		'AdditionalFootCode' => 'Text',
		"Target" => "Enum('_blank,_new,_parent,_self,_top','_self')",
		'HideDesktopMenu' => 'Boolean',
		'HideMobileMenu' => 'Boolean',
	];

	private static $defaults = [
		"Target" => "_self",
		'ShowInMobileMenu' => true
	];

	public function updateCMSFields(Forms\FieldList $fields)
	{
		$tab = $fields->findOrMakeTab('Root.Developer.AdditionalCode');
        $tab->push( CodeEditorField::create('AdditionalHeadCode','Additional JS/CSS Code inside the <head> element')
            ->setMode('ace/mode/html') );
		$tab->push( CodeEditorField::create('AdditionalFootCode','Additional HTML/JS/CSS Code Placed before </body> tag')
            ->setMode('ace/mode/html') );

        if ($this->owner->AllowSidebar())
        {
            $fields->addFieldToTab('Root.Sidebar', Forms\HTMLEditor\HTMLEditorField::create('SidebarContent','Sidebar Content')->addExtraClass('stacked'));
        }
		return $fields;
	}

    public function AllowSidebar()
    {
        $provide = $this->owner->getClassName() == \Page::class;
        $this->owner->extend('updateAllowSidebar', $provide);
        return $provide;
    }

	public function updateSettingsFields(Forms\FieldList $fields)
	{
		$fields->addFieldToTab("Root.Settings", Forms\DropdownField::create("Target", "Link Target", array(
			"_self"=>"Same Tab",
			"_blank"=>"New Tab"
		)));

		$fields->insertAfter('ShowInMenus', Forms\CheckboxField::create('HideDesktopMenu','Hide from Desktop Menu') );
		$fields->insertAfter('HideDesktopMenu', Forms\CheckboxField::create('HideMobileMenu','Hide from Mobile Menu') );

		return $fields;
	}

    public function MobileNavChildren($level = 2)
    {
        $children = [];
        foreach($this->owner->Children()->Exclude('HideMobileMenu',1) as $child)
        {
            $children[] = [
                'id' => $child->ID,
                'title' => $child->MenuTitle,
                'link' => $child->AbsoluteLink(),
                'level' => $level,
                'children' => $child->MobileNavChildren($level+1)
            ];
        }
        $this->owner->extend('updateMobileNavChildren', $children);
        return $children;
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
		$cache['Link'] = preg_replace('/\?.*/','',$this->owner->Link());
		$cache['AbsoluteLink'] = preg_replace('/\?.*/','',$this->owner->AbsoluteLink());
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

	public function ShowSidebar()
	{
		$show = (bool) strlen($this->owner->SidebarContent);
		$this->owner->extend('updateShowSidebar',$show);
		return $show;
	}
}
