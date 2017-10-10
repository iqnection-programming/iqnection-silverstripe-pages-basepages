<?php

class BlogPage extends Page
{
	private static $icon = "themes/mysite/images/icons/icon-blog";
	
	private static $db = array(
		"BlogURL" => "Varchar(255)"
	);
	
	private static $search_config = array(
		"ignore_in_search" => true
	);
	
	private static $defaults = array(
		'ShowInSearch' => false
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeFieldFromTab("Root", "Content");
		$fields->addFieldToTab("Root.Main", new TextField("BlogURL", "Blog URL Segment (eg. 'blog', 'news', etc.)")); 
		
		if($this->BlogURL)$fields->addFieldToTab("Root.WordpressLogin", new LiteralField("Desc1", "<div id='wp-login'><h1>WordPress</h1><a href='".Director::AbsoluteBaseURL().$this->BlogURL."/wp-login.php' target='_blank'>Login</a></div>")); 
		return $fields;
	}

	public function validate()
	{
		$result = parent::validate();
		if ( ($this->ParentID == 0) && ($this->URLSegment) && ($this->URLSegment == $this->BlogURL) )
		{
			$result->error('The URL Segment for this page may cause an infinite loop if the SilverStripe path is the same as the WordPress directory. I suggest [site-title-blog] format.');
		}
		return $result;
	}
	
	public function updateRefreshCacheVars(&$vars)
	{
		$vars[] = 'BlogURL';
		return $vars;
	}
	
	public function onAfterWrite()
	{
		parent::onAfterWrite();
		
		$path = Director::baseFolder()."/.htaccess";
		$curr_data = @file($path);
		
		//Silverstripe is bad and should feel bad
		$page = DataObject::get_by_id('BlogPage',$this->ID, false);		
					
		$extra = $page->BlogURL ? "RewriteCond %{REQUEST_URI} !^/".$page->BlogURL : "";
		
		$new_file = array();
		
		// first remove any blog redirect already in the file
		$remove_keys = array();
		foreach($curr_data as $key => $line)
		{				
			if (trim($line) == "### Blog Redirect ###")
			{
				$remove_keys[] = $key;
				$remove_keys[] = $key + 1;
			}
			
		}
		foreach($remove_keys as $key) { unset($curr_data[$key]); }
		
		$ss_line = 0;
		$inside = false;
		foreach ($curr_data as $line)
		{
			if (trim($line) == "### SILVERSTRIPE START ###")
				$start = true;
				
			if ($start && trim($line) == "RewriteCond %{REQUEST_FILENAME} !-f")
			{
				$inside = true;
			}
				
			if (trim($line) == "### SILVERSTRIPE END ###")
			{
				$inside = false;
			}
								
			if($inside){
				$new_file[] = trim("### Blog Redirect ###");
				$new_file[] = trim($extra);
				$inside = false;
			}
			$new_file[] = trim($line);		
			
		}
		
		$h = @fopen($path, "w");
		fwrite($h, implode("\n", $new_file));
		@fclose($h);
	}			
}

class BlogPage_Controller extends Page_Controller
{
	private static $allowed_actions = array(
		"index"
	);
	
	public function index()
	{
		// generate the template cache, if needed
		$cachePath = $this->getTemplateCachePath();
		if ( (!file_exists($cachePath)) || (filemtime($cachePath) < strtotime('-1 hour')) )
		{
			$this->generateTemplateCache();
		}
		return $this->redirect('/'.$this->BlogURL.'/');
	}
	
}








