<?php

namespace IQnection\ContactPage\Model;

use IQnection\FormPage\Model\FormPageSubmission;

class ContactPageSubmission extends FormPageSubmission 
{
	private static $table_name = 'ContactPageSubmission';
	
	private static $db = [
		'FirstName' => 'Varchar(255)',
		'LastName' => 'Varchar(255)',
		'Address' => 'Varchar(255)',
		'Address2' => 'Varchar(255)',
		'City' => 'Varchar(255)',
		'State' => 'Varchar(255)',
		'ZipCode' => 'Varchar(255)',
		'Phone' => 'Varchar(255)',
		'Email' => 'Varchar(255)',
		'Recipient' => 'Varchar(255)',
		'Comments' => 'Text'
	];
	
	private static $summary_fields = [
		"Created" => "Date",
		"FirstName" => "First Name",
		"LastName" => "Last Name",
		"Email" => "Email Address",
		"Recipient" => "Recipient"
	];
	
	private static $export_fields = [
		'Created' => 'Date',
		'FirstName' => 'First Name',
		'LastName' => 'Last Name',
		'Address' => 'Address',
		'Address2' => 'Address 2',
		'City' => 'City',
		'State' => 'State',
		'ZipCode' => 'ZipCode',
		'Email' => 'Email',
		'Phone' => 'Phone',
		'Recipient' => 'Recipient',
		'Comments' => 'Comments'
	];
	
	private static $default_sort = "Created DESC";
	
	public function canCreate($member = null, $context = array()) { return false; }
	public function canDelete($member = null, $context = array()) { return true; }
	public function canEdit($member = null, $context = array())   { return true; }
	public function canView($member = null, $context = array())   { return true; }
	
}

