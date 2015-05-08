<?
	class ContactPage extends FormPage
	{
		private static $db = array(
			"MapZoom" => "Int",
			"MapType" => "Varchar(255)",
			"MapAddress" => "Varchar(255)",
			"MapDirections" => "Boolean"
		);
		
		public function getCMSFields()
		{
			$fields = parent::getCMSFields();
			
			$fields->addFieldToTab("Root.MapDetails", new TextField("MapZoom", "Map Zoom Level (lower number = farther away)"));
			$fields->addFieldToTab("Root.MapDetails", new DropdownField("MapType", "Map Display Type", array("ROADMAP"=>"Roadmap","SATELLITE"=>"Satellite","HYBRID"=>"Hybrid","TERRAIN"=>"Terrain"),"Roadmap"));
			$fields->addFieldToTab("Root.MapDetails", new TextField("MapAddress", "Address for Map"));
			$fields->addFieldToTab("Root.MapDetails", new CheckboxField("MapDirections", "Display Directions Widget?"));

			return $fields;
		}	
	}	
	
	class ContactPageSubmission extends DataObject 
	{
		
        private static $db = array(
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
        );
		
		private static $summary_fields = array(
			"Created" => "Date",
			"FirstName" => "First Name",
			"LastName" => "Last Name",
			"Email" => "Email Address",
			"Recipient" => "Recipient"
		);
		
		private static $default_sort = "Created DESC";
		
    }
	
	class ContactPage_Controller extends FormPage_Controller
	{	
		private static $allowed_actions = array(
			"directions",
			"printview"			
		);	
		
		public function FormFields()
		{
			return array(
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
		}
				
		public function init()
		{
			parent::init();
			if($this->MapAddress){
				Requirements::javascript("http://maps.googleapis.com/maps/api/js?key=AIzaSyAXy4BLGXyLMakRQbrMVrFxS2KiXSj51cM&sensor=false");
			}
		}
		
		function CustomJS()
		{
			$js = parent::CustomJS();
			$js .= "var MapZoom = ".$this->MapZoom.";
					var MapType = '".$this->MapType."';
					var MapAddress = '".$this->MapAddress."';
					var MapDirections = ".$this->MapDirections.";
					var MapLocationTitle = '';
					var PageLink = '".$this->Link()."';";
			
			return $js;
		}
		
		public function directionsAPI()
		{
			$addy = urlencode($this->request->param('ID'));
			$path = "http://maps.googleapis.com/maps/api/directions/json?origin=".$addy."&destination=".urlencode($this->MapAddress)."&sensor=false";
			$rows = file_get_contents($path,0,null,null);
			$directions_output = json_decode($rows, true);
			$ajax_data = false;
			
			if($directions_output['routes']){
				$data = $directions_output['routes'][0]['legs'][0];  //assumes best route and no waypoints
				$i = 1;
				$steps = "";
				foreach($data['steps'] as $step){
					$steps .= "<div class='step'><span class='step_number'>".$i.".</span><span class='step_text'>".$step['html_instructions']."</span><span class='step_distance'>".$step['distance']['text']."</span></div>";	
					$i++;
				}
				
				$ajax_data = array(
					"StartAddress" => $data['start_address'],
					"EndAddress" => $data['end_address'],
					"Distance" => $data['distance']['text'],
					"Duration" => $data['duration']['text'],
					"GoogleLink" => "https://maps.google.com/maps?q=".$addy."+to+".urlencode($this->MapAddress),
					"PrintLink" => $this->AbsoluteLink()."printview/".$addy,
					"PageLink" => $this->AbsoluteLink()."directions/".$addy,
					"Steps" => $steps
				);
				
			}
			
			return $ajax_data;
		}
		
		public function directions()
		{
			$ajax_data = $this->directionsAPI();
			
			if($ajax_data){
				return Director::is_ajax() ? $this->Customise($ajax_data)->renderWith("ContactPage_directions") : $this->Customise($ajax_data);
			} else {
				return "<p>No routes were found from that destination address.</p>";	
			}
		}
		
		public function printview()
		{
			$ajax_data = $this->directionsAPI();
			
			if($ajax_data){
				return $this->Customise($ajax_data)->renderWith("ContactPage_printview");
			} else {
				return "<p>No routes were found from that destination address.</p>";	
			}
		}
		
	}
?>