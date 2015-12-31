<?php
	// The purpose of this page is simply to redirect to the first
	//	child page, or the home page if there's none

	class HeadingPage extends Page
	{
	    public static $icon = "themes/mysite/images/icons/icon-heading";
		
	    public static $search_config = array(
			"ignore_in_search" => true
		);
		
	    public function getCMSFields()
	    {
	        $fields = parent::getCMSFields();
	        $fields->removeFieldFromTab("Root", "Content");
	        return $fields;
	    }
	}
	
	class HeadingPage_Controller extends Page_Controller
	{
	    public function init()
	    {
	        parent::init();
	    }
		
	    public static $allowed_actions = array(
			"index"
		);
		
	    public function index()
	    {
	        $url = "/";
			
	        $curr_page = $this->Children()->First();
			
	        while ($curr_page->PageType == "HeadingPage" && $curr_page->Children()) {
	            $curr_page = $curr_page->Children()->First();
	        }
			
	        if ($curr_page && $curr_page->PageType != "HeadingPage") {
	            $url = $curr_page->Link();
	        }
			
	        $this->redirect($url);
	    }
	}
?>