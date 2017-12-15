<?php


use SilverStripe\Forms;
use SilverStripe\Control\Director;
use SilverStripe\ORM;

class BlogPage extends Page
{
	private static $icon = "iq-basepages/images/icons/icon-blog-file.gif";
	
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
		$fields->removeByName('Metadata');
		$fields->removeByName('Developer');
		$fields->addFieldToTab("Root.Main", Forms\TextField::create("BlogURL", "Blog URL Segment (eg. 'blog', 'news', etc.)")); 
		
		if ($this->BlogURL)
		{
			$fields->addFieldToTab("Root.Main", Forms\LiteralField::create("Desc1", '<div id="wp-login"><h1><img src="/iq-basepages/images/wordpress-logo.png" alt="WordPress" /></h1>
			<a href="'.Director::AbsoluteBaseURL().$this->BlogURL.'/wp-login.php" target="_blank"><img src="iq-basepages/images/wordpress-login.png" alt="Login" /></a></div>')); 
		}
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
					
		$extra = $this->BlogURL ? "RewriteCond %{REQUEST_URI} !^/".$this->BlogURL."$" : "";
		$extra2 = $this->BlogURL ? "RewriteCond %{REQUEST_URI} !^/".$this->BlogURL."/" : "";
		
		$new_file = array();
		
		// first remove any blog redirect already in the file
		$remove_keys = array();
		foreach($curr_data as $key => $line)
		{				
			if (trim($line) == "### Blog Redirect ###")
			{
				$remove_keys[] = $key;
				$remove_keys[] = $key + 1;
				$remove_keys[] = $key + 2;
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
				$new_file[] = trim($extra2);
				$inside = false;
			}
			$new_file[] = trim($line);		
			
		}
		
		$h = @fopen($path, "w");
		fwrite($h, implode("\n", $new_file));
		@fclose($h);
	}			
}








