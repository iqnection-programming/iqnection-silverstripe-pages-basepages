<?php

namespace IQnection\ContactPage;

use SilverStripe\View;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use IQnection\FormPage\FormPageController;

class ContactPageController extends FormPageController
{	
	private static $allowed_actions = array(
		"directions",
		"printview",
	);	
			
	public function FormFields()
	{
		$fields = array(
			"FirstName" => array(
				"FieldType" => "TextField",
				"Required" => true	
			),
			"LastName" => array(
				"FieldType" => "TextField",
				"Required" => true	
			),
			"Address" => array(
				"FieldType" => "TextField"
			),
			"Address2" => array(
				"FieldType" => "TextField",
				"Label" => "Address (line 2)"
			),
			"City" => array(
				"FieldType" => "TextField"
			),
			"State" => array(
				"FieldType" => "DropdownField",
				"Value" => "GetStates",
				"Default" => "PA"
			),
			"ZipCode" => array(
				"FieldType" => "TextField",
				"Label" => "Zip Code"	
			),
			"Phone" => array(
				"FieldType" => "TextField"
			),
			"Email" => array(
				"FieldType" => "EmailField",
				"Required" => true	
			),
			"Recipient" => $this->RecipientFieldConfig(),
			"Comments" => array(
				"FieldType" => "TextAreaField"
			),
		);	
		
		$this->extend('updateFormFields',$fields);
		return $fields;
	}
	
	public function FormConfig()
	{
		$config = array(
			'useNospam' => true
		);
		$this->extend('updateFormConfig',$config);
		return $config;
	}
			
	public function init()
	{
		parent::init();
		if($this->ContactPageLocations()->Count())
		{
			$maps_url = "https://maps.googleapis.com/maps/api/js?sensor=false";
			if ($key = $this->GoogleMapsApiKey)
			{
				$maps_url .= "&key=".$key;
			}
			View\Requirements::javascript($maps_url);
		}
	}
	
	public function CustomJS()
	{
		$js = parent::CustomJS();
		if ($this->ContactPageLocations()->Count())
		{
			$TotalLat = 0;
			$TotalLong = 0;
			$Total = 0;
			$Avgs = [0,0];
			$addressObjects = [];
			if($locations = $this->ContactPageLocations())
			{
				foreach($locations as $key => $l)
				{
					$TotalLat += $l->MapLatitude;
					$TotalLong += $l->MapLongitude;
					$Total++;
					$addressObjects[$key] = [
						'Title' => $l->Title,
						'Address' => $l->Address,
						'LatLng' => [
							$l->MapLatitude,
							$l->MapLongitude
						]
					];
				}
				if ( ($Total) && ($TotalLat != 0) && ($TotalLong != 0) )
				{
					$Avgs = [$TotalLat/$Total,$TotalLong/$Total];
				}
			}
			$js .= '
var MapType = "'.$this->MapType.'";
var address_objects = '.json_encode($addressObjects).';
var Avgs = '.json_encode($Avgs).';
var PageLink = "'.$this->Link().'";'."\n";
		}
		
		return $js;
	}
	
	public function directionsAPI()
	{
		$to_addy = urlencode(urldecode($this->request->param('ID')));
		$from_addy = urlencode(urldecode($this->request->param('OtherID')));
		$path = "https://maps.googleapis.com/maps/api/directions/json?origin=".$from_addy."&destination=".$to_addy."&sensor=false";
		if ($key = $this->GoogleMapsApiKey)
		{
			$path .= "&key=".$key;
		}
		$rows = file_get_contents($path,0,null,null);
		$directions_output = json_decode($rows, true);
		$ajax_data = false;
		if($directions_output['routes'])
		{
			$data = $directions_output['routes'][0]['legs'][0];  //assumes best route and no waypoints
			$i = 1;
			$steps = "";
			foreach($data['steps'] as $step)
			{
				$steps .= "<div class='step'><span class='step_number'>".$i.".</span><span class='step_text'>".$step['html_instructions']."</span><span class='step_distance'>".$step['distance']['text']."</span></div>";	
				$i++;
			}
			
			$ajax_data = array(
				"StartAddress" => $data['start_address'],
				"EndAddress" => $data['end_address'],
				"Distance" => $data['distance']['text'],
				"Duration" => $data['duration']['text'],
				"GoogleLink" => "https://maps.google.com/maps?q=".$from_addy."+to+".$to_addy,
				"PrintLink" => $this->AbsoluteLink("printview/".$from_addy."/".$to_addy),
				"PageLink" => $this->AbsoluteLink("directions/".$from_addy."/".$to_addy),
				"Steps" => $steps
			);
			
		}
		
		return $ajax_data;
	}
	
	public function directions()
	{
		$ajax_data = $this->directionsAPI();
		
		if($ajax_data)
		{
			return Director::is_ajax() ? $this->Customise($ajax_data)->renderWith("Layout/ContactPage_directions") : $this->Customise($ajax_data);
		} 
		else 
		{
			return "<p>No routes were found from that destination address.</p>";	
		}
	}
	
	public function printview()
	{
		$ajax_data = $this->directionsAPI();
		
		if($ajax_data)
		{
			return $this->Customise($ajax_data)->renderWith("Layout/ContactPage_printview");
		} 
		else 
		{
			return "<p>No routes were found from that destination address.</p>";	
		}
	}
	
	public function NeedLocationsSelect()
	{
		return $this->ContactPageLocations()->Count() > 1;	
	}
}

