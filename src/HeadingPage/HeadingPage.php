<?php
// The purpose of this page is simply to redirect to the first
//	child page, or the home page if there's none

namespace IQnection\HeadingPage;

class HeadingPage extends \Page
{
	private static $table_name = 'HeadingPage';
	
	private static $icon = "iqnection-pages/basepages:images/icons/icon-heading-file.gif";
	
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
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}			
}


