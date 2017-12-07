<?php


use SilverStripe\ORM\DataObject;


class FormRecipient extends DataObject
{
	private static $db = array(
		'SortOrder' => 'Int',
		"Title" => "Varchar(255)",
		"Email" => "Varchar(255)"
	);
	
	private static $has_one = array(
		"Page" => Page::class
	);
	
	private static $summary_fields = array(
		"Title" => "Title",
		"Email" => "Email Address"
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->dataFieldByName('Email')->setTitle('Email Address');
		$fields->removeByName('SortOrder');
		return $fields;
	}
	
	public function canCreate($member = null, $context = array()) { return true; }
	public function canDelete($member = null, $context = array()) { return true; }
	public function canEdit($member = null, $context = array())   { return true; }
	public function canView($member = null, $context = array())   { return true; }

	public function validate()
	{
		$result = parent::validate();
		if (empty($this->Email))
		{
			$result->error('Please provide an Email address');
		}
		if (empty($this->Title))
		{
			$result->error('Please provide a Title');
		}
		return $result;
	}
}

