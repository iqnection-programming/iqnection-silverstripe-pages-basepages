<?php

namespace IQnection\BasePage;

use SilverStripe\Core;
use SilverStripe\View;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Director;
	
class PageControllerExtension extends Core\Extension 
{
	private static $base_theme_name = 'mysite';
	
	private static $allowed_actions = array(
		"thanks",
		'RenderTemplates'			
	);
	
	public function onBeforeInit()
	{
		View\Requirements::javascript("iqnection-pages/basepages:javascript/jquery-1.9.1.min.js");
	}
		
	public function onAfterInit() 
	{
		$themeName = $this->owner->Config()->get('base_theme_name');;
		$dir = View\ThemeResourceLoader::inst()->getPath($themeName);

		View\Requirements::set_combined_files_folder('combined');
		
		if ($fontsCss = View\ThemeResourceLoader::inst()->findThemedCSS('fonts',array($themeName)))
		{
			View\Requirements::css($fontsCss);
		}
		
		$BaseCSS = array(
			"reset",
			"fontawesome/font-awesome.min",
			"form",
			"layout",
			"responsive",
			"typography"
		);
		$baseCssFiles = array();
		foreach($BaseCSS as $cssFile)
		{
			$cssFile = preg_replace('/\.css|\.scss/','',$cssFile);
			// searching this way will favor a .scss file over .css
			foreach(['.css','.scss'] as $ext)
			{
				if ($CssFilePath = View\ThemeResourceLoader::inst()->findThemedResource($cssFile.$ext,array($themeName)))
				{
					$baseCssFiles[$cssFile] = $CssFilePath;
				}
				elseif ($CssFilePath = View\ThemeResourceLoader::inst()->findThemedResource('css/'.$cssFile.$ext,array($themeName)))
				{
					$baseCssFiles[$cssFile] = $CssFilePath;
				}
			}
		}
		View\Requirements::combine_files('base.css', $baseCssFiles);
		
		$BaseJS = array(
			"responsive",
			"scripts",
			"navigation",
		);
		$baseJsFiles = array();
		foreach($BaseJS as $jsFile)
		{
			if ($JsFilePath = View\ThemeResourceLoader::inst()->findThemedJavascript($jsFile,array($themeName)))
			{
				$baseJsFiles[] = $JsFilePath;
			}
		}
		View\Requirements::combine_files('base.js', $baseJsFiles);	

		if ( ($parsedCssFiles = array_diff($this->owner->ParsedPageCSS(),$baseCssFiles)) && (count($parsedCssFiles)) )
		{ 
			View\Requirements::combine_files($this->owner->CombinedCssFileName().'.css', $parsedCssFiles);
		}
		if ( ($parsedJsFiles = array_diff($this->owner->ParsedPageJS(),$baseJsFiles)) && (count($parsedJsFiles)) )
		{
			View\Requirements::combine_files($this->owner->CombinedJsFileName().'.js', $parsedJsFiles);
		}
		if ($customJs = $this->owner->CustomJS()) 
		{
			View\Requirements::customScript($customJs); 
		}
	}
	
	public function CombinedCssFileName()
	{
		$fileName = Core\ClassInfo::shortName($this->owner->dataRecord->getClassName());
		$fileName = $this->owner->extend('updateCombinedCssFileName',$fileName);
		return (is_array($fileName)) ? end($fileName) : $fileName;
	}
	
	public function updateCombinedCssFileName($fileName)
	{
		return $fileName;
	}
	
	public function CombinedJsFileName()
	{
		$fileName = Core\ClassInfo::shortName($this->owner->dataRecord->getClassName());
		$fileName = $this->owner->extend('updateCombinedJsFileName',$fileName);
		return (is_array($fileName)) ? end($fileName) : $fileName;
	}
	
	public function updateCombinedJsFileName($fileName)
	{
		return $fileName;
	}
	
	public function ParsedPageCSS()
	{
		$CssFiles = array();
		$files = array_merge(
			$this->PageTypeCSS(),
			$this->owner->PageCSS()
		);
		foreach($files as $filePath)
		{
			$filePath = preg_replace('/\.css|\.scss/','',$filePath);
			$hasScss = false;
			// searching this way will favor a .scss file over .css
			foreach(['.scss','.css'] as $ext)
			{
				if ( (!$hasScss) && ($ThemeResourcePath = View\ThemeResourceLoader::inst()->findThemedResource($filePath.$ext,View\SSViewer::get_themes())) )
				{
					if ($ext == '.scss') { $hasScss = true; }
					$CssFiles[$filePath] = $ThemeResourcePath;
				}
			}
		}
		return $CssFiles;
	}
	
	public function ParsedPageJS()
	{
		$JsFiles = array();
		$files = array_merge(
			$this->PageTypeJS(),
			$this->owner->PageJS()
		);
		foreach($files as $filePath)
		{
			if (!preg_match("/^\//",$filePath))
			{
				$filePath = "/".$filePath;
			}
			if ($ThemeResourcePath = View\ThemeResourceLoader::inst()->findThemedResource($filePath,View\SSViewer::get_themes()))
			{
				$JsFiles[$ThemeResourcePath] = $ThemeResourcePath;
			}
		}
		return $JsFiles;
	}
	
	public function PageTypeCSS()
	{
		$CSSFiles = array();
		// Find a page type specific CSS file
		$PageType = Core\ClassInfo::shortName($this->owner->dataRecord->getClassName());
		$CSSFiles["/css/pages/".$PageType] = "/css/pages/".$PageType;
		$CSSFiles["/css/pages/".$PageType."_extension"] = "/css/pages/".$PageType."_extension";
		$extends = $this->owner->extend('updatePageCSS',$CSSFiles);
		foreach($extends as $updates)
		{
			$CSSFiles = array_merge(
				$CSSFiles,
				$updates
			);
		}	
		return $CSSFiles;
	}
	
	public function PageCSS()
	{			
		return [];
	}
	
	public function PageTypeJS()
	{
		$JSFiles = array();
		$PageType = Core\ClassInfo::shortName($this->owner->dataRecord->getClassName());
		$JSFiles["/javascript/pages/".$PageType.".js"] = "/javascript/pages/".$PageType.".js";
		$JSFiles["/javascript/pages/".$PageType."_extension.js"] = "/javascript/pages/".$PageType."_extension.js";
		$extends = $this->owner->extend('updatePageJS',$JSFiles);
		foreach($extends as $updates)
		{
			$JSFiles = array_merge(
				$JSFiles,
				$updates
			);
		}
		return $JSFiles;
	}
		
	public function PageJS()
	{
		return [];
	}
	
	public function CustomJS()
	{
		$js = null;
		foreach($this->owner->extend('updateCustomJS',$js) as $moreJs)
		{
			$js .= $moreJs;
		}
		return $js;
	}
	
	public function CopyrightYear()
	{
		return date("Y");
	}
	
	public function CopyrightName()
	{
		$arr_path = explode(".", $_SERVER['HTTP_HOST']);
		$suffix = array_pop($arr_path);
		$domain = array_pop($arr_path).'.'.$suffix;
		return $domain;
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
		if (!file_exists(Director::baseFolder().'/template-cache'))
		{
			mkdir(Director::baseFolder().'/template-cache',0755);
			file_put_contents(Director::baseFolder().'/template-cache/.htaccess',"Order deny,allow\nDeny from all\nAllow from 127.0.0.1");
		}
		$cachePath = $this->owner->getTemplateCachePath();
		$cache = array(
			'header' => preg_replace('/\t/','',$this->owner->Customise(array('ForCache' => true))->renderWith(['Header','Includes/Header'])->AbsoluteLinks()),
			'footer' => preg_replace('/\t/','',$this->owner->Customise(array('ForCache' => true))->renderWith(['Footer','Includes/Footer'])->AbsoluteLinks())
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