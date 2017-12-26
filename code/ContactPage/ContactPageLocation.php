<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Core\Config\Config;
use IqBasePages\FormPage;

class ContactPageLocation extends DataObject
{
	private static $db = [
		"SortOrder" => "Int",
		"Title" => "Varchar(255)",
		"Address" => "Varchar(255)",
		"MapLatitude" => "Varchar(255)",
		"MapLongitude" => "Varchar(255)"
	];
			
	private static $has_one = [
		"ContactPage" => ContactPage::class
	];
	
	private static $summary_fields = [
		"Title" => "Title",
		"Address" => "Address"
	];
	
	private static $default_sort = "SortOrder";
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->dataFieldByName('Title')->setTitle('Location Title');
		$fields->dataFieldByName('Address')->setTitle('Location Address');
		$fields->removeByName('SortOrder');
		$fields->insertAfter('Address', $coordinates = Forms\FieldGroup::create('Coordinates') );
		$coordinates->setDescription('Leave empty to retrieve Latitude and Longitude from Google');
		$coordinates->push( $fields->dataFieldByName('MapLatitude')->setTitle('Latitude') );
		$coordinates->push( $fields->dataFieldByName('MapLongitude')->setTitle('Longitude') );
		return $fields;
	}
	
	public function validate()
	{
		$result = parent::validate();
		if (!$this->Address)
		{
			$result->error('Please provide an Address');
		}
		return $result;
	}
	
	public function getLocation($address=false)
	{
		$maps_url = "https://maps.google.com/maps/api/geocode/json?sensor=false";
		if ($key = Config::inst()->get('ContactPage','google_maps_api_key'))
		{
			$maps_url .= "&key=".$key;
		}
		$url = $maps_url."&address=".urlencode($address);
		
		if (!$resp_json = $this->curl_file_get_contents($url))
		{
			return false;
		}
		$resp = json_decode($resp_json, true);

		if($resp['status']='OK')
		{
			return $resp['results'][0]['geometry']['location'];
		}
		else
		{
			return false;
		}
	}
	
	protected function curl_file_get_contents($URL)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents) 
		{
			return $contents;
		}
		return false;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if ( ($this->isChanged('Address')) || (!$this->MapLatitude) || (!$this->MapLongitude) )
		{
			$location = $this->getLocation($this->Address);
			if ($location)
			{
				$this->MapLatitude = $location['lat'];
				$this->MapLongitude = $location['lng'];
			}
		}
	}
	
	public function canCreate($member = null, $context = array()) { return true; }
	public function canDelete($member = null, $context = array()) { return true; }
	public function canEdit($member = null, $context = array())   { return true; }
	public function canView($member = null, $context = array())   { return true; }
}


