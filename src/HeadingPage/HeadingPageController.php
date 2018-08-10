<?php
// The purpose of this page is simply to redirect to the first
//	child page, or the home page if there's none

namespace IQnection\HeadingPage;

class HeadingPageController extends \PageController
{
	private static $allowed_actions = array(
		"index"
	);
	
	public function index()
	{
		$url = "/";
		
		$curr_page = $this->Children()->First();
		
		while ($curr_page->PageType == "HeadingPage" && $curr_page->Children()) 
		{
			$curr_page = $curr_page->Children()->First();
		}
		
		if( $curr_page && $curr_page->PageType != "HeadingPage" ) 
		{
			$url = $curr_page->Link();
		}
		
		return $this->redirect($url);
	}
}


