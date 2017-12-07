<?php

use SilverStripe\ORM\DataObject;
use IqBasePages\FormPage;

class ContactPageLocation extends DataObject
{
	private static $db = array( 
		"SortOrder" => "Int",
		"Title" => "Varchar(255)",
		"Address" => "Varchar(255)",
		"MapLatitude" => "Varchar(255)",
		"MapLongitude" => "Varchar(255)"
	);
	
	private static $default_sort = "SortOrder";
	
	private static $summary_fields = array(
		"Title" => "Title",
		"Address" => "Address"
	);
	
	private static $has_one = array(
		"ContactPage" => ContactPage::class
	); 		
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->dataFieldByName('Title')->setTitle('Location Title');
		$fields->dataFieldByName('Address')->setTitle('Location Address');
		$fields->removeByName('SortOrder');
		return $fields;
	}
	
	public function getLocation($address=false){
		$google = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";
		$url = $google.urlencode($address);
		
		$resp_json = $this->curl_file_get_contents($url);
		$resp = json_decode($resp_json, true);

		if($resp['status']='OK'){
			return $resp['results'][0]['geometry']['location'];
		}else{
			return false;
		}
	}
	
	private function curl_file_get_contents($URL)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents) return $contents;
			else return FALSE;
	}
	
	function onAfterWrite()
	{
		parent::onAfterWrite();
		
		$location = $this->getLocation($this->Address);
		if ($location)
		{
			$this->MapLatitude = $location['lat'];
			$this->MapLongitude = $location['lng'];
			$this->write();
		}
	}
	
	public function canCreate($member = null, $context = array()) { return true; }
	public function canDelete($member = null, $context = array()) { return true; }
	public function canEdit($member = null, $context = array())   { return true; }
	public function canView($member = null, $context = array())   { return true; }
}


