<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;


class ContactPage extends FormPage
{
	private static $google_maps_api_key = 'AIzaSyAXy4BLGXyLMakRQbrMVrFxS2KiXSj51cM';
	
	private static $db = array(
		"MapType" => "Varchar(255)",
		"MapDirections" => "Boolean"
	);
	
	private static $has_many = array(
		"ContactPageLocations" => ContactPageLocation::class,
		"ContactPageSubmissions" => ContactPageSubmission::class
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.MapDetails", Forms\DropdownField::create("MapType", "Map Display Type", array("ROADMAP"=>"Roadmap","SATELLITE"=>"Satellite","HYBRID"=>"Hybrid","TERRAIN"=>"Terrain"),"Roadmap"));
		$fields->addFieldToTab('Root.MapDetails', Forms\GridField\GridField::create(
			'Locations',
			'Locations',
			$this->ContactPageLocations(),
			Forms\GridField\GridFieldConfig_RecordEditor::create()->addComponent(
				new GridFieldSortableRows('SortOrder')
			)
		));
		$fields->addFieldToTab("Root.MapDetails", Forms\CheckboxField::create("MapDirections", "Display Directions Widget?"));
		return $fields;
	}			
}	



