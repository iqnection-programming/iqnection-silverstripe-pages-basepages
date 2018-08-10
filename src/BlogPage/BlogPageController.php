<?php

namespace IQnection\BlogPage;

class BlogPageController extends \PageController
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








