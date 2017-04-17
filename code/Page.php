<?php
	
class IQBase_Page extends DataExtension
{				
	
	private static $db = array(
		"SidebarContent" => "HTMLText",
		"LeftColumn" => "HTMLText",
		"CenterColumn" => "HTMLText",
		"RightColumn" => "HTMLText",
		'AdditionalCode' => 'Text',
		"Target" => "enum('_blank,_new,_parent,_self,_top','_self')",
	);	
	
	private static $has_one = array(
	);
	
	private static $defaults = array(
		"Target" => "_self"
	);
	
	public function updateCMSFields(FieldList $fields)
	{
		$tab = $fields->findOrMakeTab('Root.Developer.AdditionalCode');
		$tab->push( $codeField = new CodeEditorField('AdditionalCode','Additional HTML/JS/CSS Code',50) );
		$codeField->addExtraClass('stacked');
		$codeField->setRows(45);
		$codeField->setMode('html');
				
		if($this->owner->ClassName == "Page")
		{
			$fields->addFieldToTab("Root.Columns", new HTMLEditorField("LeftColumn", "Left Column Content"));  
			$fields->addFieldToTab("Root.Columns", new HTMLEditorField("CenterColumn", "Center Column Content"));  
			$fields->addFieldToTab("Root.Columns", new HTMLEditorField("RightColumn", "Right Column Content")); 
			$fields->addFieldToTab("Root.Sidebar", new HTMLEditorField("SidebarContent", "Sidebar Content"));
		}
			
		return $fields;
	}
	
	public function updateSettingsFields(FieldList $fields)
	{
		$fields->addFieldToTab("Root.Settings", new DropdownField("Target", "Link Target", array(
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
		if ( ($refreshCache) || (!file_exists(BASE_PATH.'/site-tree.json')) ) { $this->owner->cacheSiteTree(); }
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
		$path = (($absolute) ? BASE_PATH.'/' : null).'template-cache/page-'.$this->owner->ID.'.json';
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
		foreach(Page::get()->filter('ParentID','0') as $page)
		{
			$cache['SiteTree'][$page->ID] = $page->dataForCache();
		}
		$cache = $this->owner->updateCachedSiteTree($cache);
		file_put_contents(BASE_PATH.'/site-tree.json',json_encode($cache));
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
	
class IQBase_Page_Controller extends Extension 
{
	
	private static $allowed_actions = array(
		"thanks",
		'RenderTemplates'			
	);
	
	public function onBeforeInit()
	{
		$dir = $this->owner->ThemeDir();
		Requirements::javascript($dir."/javascript/jquery-1.9.1.min.js");
	}
		
	public function onAfterInit() 
	{			
		$dir = $this->owner->ThemeDir();
		Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");			
		Requirements::set_combined_files_folder($dir.'/combined');
		
		Requirements::css($dir."/css/fonts.css");
		
		$BaseCSS = array(
			$dir."/css/base.css",
			$dir."/css/fontawesome/font-awesome.min.css",
			$dir."/css/layout.css",
			$dir."/css/typography.css"
		);
		Requirements::combine_files('Base.css', $BaseCSS);
		
		$BaseJS = array(
			$dir."/javascript/responsive.js",
			$dir."/javascript/scripts.js",
			$dir."/javascript/navigation.js",
		);
		Requirements::combine_files('Base.js', $BaseJS);	

		if($this->owner->ParsedPageCSS())Requirements::combine_files($this->owner->ClassName.'.css', $this->owner->ParsedPageCSS());
		if($this->owner->ParsedPageJS())Requirements::combine_files($this->owner->ClassName.'.js', $this->owner->ParsedPageJS());
		if($this->owner->CustomJS())Requirements::customScript($this->owner->CustomJS()); 
		if($this->owner->ResponsiveCSS())Requirements::combine_files('Responsive.css', $this->owner->ResponsiveCSS());
	
	}
			
	function GetPossibleDirs()
	{
		$dirs = array();
		$dirs[] = "iq-basepages";
		$dirs[] = "iq-".strtolower($this->owner->ClassName);
		$dirs[] = "themes/mysite";		
		return $dirs;
	}
	
	function ParsedPageCSS()
	{
		$CssFiles = array();
		if ($files = $this->owner->PageCSS())
		{
			foreach($files as $file)
			{
				$CssFiles[$file] = $file;
			}
		}
		return $CssFiles;
	}
	
	function ParsedPageJS()
	{
		$JsFiles = array();
		if ($files = $this->owner->PageJS())
		{
			foreach($files as $file)
			{
				$JsFiles[$file] = $file;
			}
		}
		return $JsFiles;
	}
	
	function PageCSS()
	{			
		$CSSFiles = array();
		foreach($this->GetPossibleDirs() as $dir)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir."/css/pages/".$this->owner->ClassName.".css"))
			{
				$CSSFiles[] = $dir."/css/pages/".$this->owner->ClassName.".css";
			}
		}
		$this->owner->extend('updatePageCSS',$CSSFiles);
		return $CSSFiles;
	}
	
	function ResponsiveCSS()
	{
		$CSSFiles = array();
		$CSSFiles[] = "themes/mysite/css/responsive.css";
		foreach($this->GetPossibleDirs() as $dir)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir."/css/pages/".$this->owner->ClassName."_responsive.css"))
			{
				$CSSFiles[] = $dir."/css/pages/".$this->owner->ClassName."_responsive.css";
			}
		}
		$this->owner->extend('updateResponsiveCSS',$CSSFiles);
		return $CSSFiles;
	}
	
	function PageJS()
	{
		$JSFiles = array();
		foreach($this->GetPossibleDirs() as $dir)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir."/javascript/pages/".$this->owner->ClassName.".js"))
			{
				$JSFiles[] = $dir."/javascript/pages/".$this->owner->ClassName.".js";
			}
		}
		$this->owner->extend('updatePageJS',$JSFiles);
		return $JSFiles;
	}
	
	function CustomJS()
	{
		$js = null;
		$this->owner->extend('updateCustomJS',$js);
		return $js;
	}
	
	public function CopyrightYear()
	{
		return date("Y");
	}
	
	public function CopyrightName()
	{
		$arr_path = explode(".", $_SERVER['HTTP_HOST']);	
		return $arr_path[1].".".$arr_path[2];
	}
	
	public function ColAmount(){
		$i = 0;
		if($this->owner->LeftColumn)$i++;
		if($this->owner->CenterColumn)$i++;
		if($this->owner->RightColumn)$i++;
		return $i;
	}
	
	public function thanks()
	{
		return $this->owner->Customise(array());
	}
		
	public function generateTemplateCache()
	{
		// make sure the cache directory exists
		if (!file_exists(BASE_PATH.'/template-cache'))
		{
			mkdir(BASE_PATH.'/template-cache',0755);
		}
		$cachePath = $this->owner->getTemplateCachePath();
		$cache = array(
			'header' => preg_replace('/\t/','',$this->owner->Customise(array('ForCache' => true))->renderWith('Header')->AbsoluteLinks()),
			'footer' => preg_replace('/\t/','',$this->owner->Customise(array('ForCache' => true))->renderWith('Footer')->AbsoluteLinks())
		);
		$cache = $this->owner->updateGeneratedTemplateCache($cache);
		file_put_contents($cachePath,json_encode($cache));
		// regenerate SiteConfig cache
		SiteConfig::current_site_config()->generateTemplateCache();
		return json_encode($cache);
	}
	
	public function updateGeneratedTemplateCache($cache) { return $cache; }
	
	public function RenderTemplates()
	{
		header('Content-type: application/json');
		print $this->generateTemplateCache();
		die();
	}
	
}