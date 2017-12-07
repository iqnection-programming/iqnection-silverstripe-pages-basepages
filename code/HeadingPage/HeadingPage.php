<?php
// The purpose of this page is simply to redirect to the first
//	child page, or the home page if there's none

namespace IqBasePages\HeadingPage;

class HeadingPage extends \Page
{
	private static $icon = "iq-basepages/images/icons/icon-heading-file.gif";
	
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
		return $fields;
	}			
}


