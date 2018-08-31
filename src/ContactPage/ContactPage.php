<?php

namespace IQnection\ContactPage;

use IQnection\FormPage\FormPage;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;


class ContactPage extends FormPage
{
	private static $table_name = 'ContactPage';
		
	private static $db = array(
		"MapType" => "Varchar(255)",
		"MapDirections" => "Boolean",
		'GoogleMapsApiKey' => 'Varchar(255)'
	);
	
	private static $has_many = array(
		"ContactPageLocations" => \IQnection\ContactPage\Model\ContactPageLocation::class,
		"ContactPageSubmissions" => \IQnection\ContactPage\Model\ContactPageSubmission::class
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.MapDetails', Forms\TextField::create('GoogleMapsApiKey','Google Maps API Key') );
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



