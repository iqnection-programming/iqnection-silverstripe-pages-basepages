<?
	class FormRecipient extends DataObject
	{
		private static $db = array(
			"Title" => "Varchar(255)",
			"Email" => "Varchar(255)"
		);
		
		private static $has_one = array(
			"FormPage" => "FormPage"
		);
		
		private static $summary_fields = array(
			"Title" => "Title",
			"Email" => "Email Address"
		);
		
		public function getCMSFields()
		{
			return new FieldList(
				new TextField('Title', 'Title'),
				new TextField('Email', 'Email Address')
			);
		}
	}
	
	class FormPage extends Page
	{
		static $icon = "themes/mysite/images/icons/icon-form";
		
		private static $db = array(
			"ThankYouText" => "HTMLText"
		);
		
		private static $has_many = array(
			"FormRecipients" => "FormRecipient"
		);
		
		public function getCMSFields()
		{	
			$fields = parent::getCMSFields();
					
			$recips_config = GridFieldConfig::create()->addComponents(
				new GridFieldToolbarHeader(),
				new GridFieldAddNewButton('toolbar-header-right'),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldDetailForm()
			);
			$fields->addFieldToTab('Root.Content.FormControls', new GridField('FormRecipients','Form Recipients',$this->FormRecipients(),$recips_config));
			$fields->addFieldToTab('Root.Content.FormControls', new HTMLEditorField('ThankYouText','Text after form submission'));
			
			$submits_config = GridFieldConfig::create()->addComponents(
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldViewButton(),
				new GridFieldDetailForm(),
				new GridFieldDeleteAction(),
				new GridFieldExportButton()
			);
			$fields->addFieldToTab('Root.Content.FormSubmissions', new GridField('FormSubmissions','Form Submissions',DataObject::get($this->submission_class),$submits_config));
			return $fields;
		}			
	}	
	
	class FormPage_Controller extends Page_Controller
	{	
		
		private static $allowed_actions = array(
			"RenderForm"			
		);
		
		public function init()
		{
			parent::init();
		}
		
		function PageCSS()
		{
			$dir = ViewableData::ThemeDir();
			return array_merge(
				parent::PageCSS(),
				array(
					$dir."/css/form.css"
				)
			);
		}
		
		public function RenderForm() {
			if($form_fields = $this->form_fields)
			{
				$fields = new FieldList();
				$validator = new RequiredFields();
				
				foreach($form_fields as $FieldName => $data)
				{
					if($data['Value'])
					{
						$utils = new FormUtilities();
						$method_home = method_exists($utils,$data['Value']) ? $utils : (method_exists($this,$data['Value']) ? $this : false);
						$data['Value'] = $method_home ? $method_home->$data['Value']() : $data['Value'];
					}
					$field = new $data['FieldType']($FieldName,($data['Label']?$data['Label']:null),($data['Value']?$data['Value']:null),($data['Default']?$data['Default']:null));
					if($data['ExtraClass'])$field->addExtraClass($data['ExtraClass']);
					$fields->push($field);	
					if($data['Required'])$validator->addRequiredField($FieldName);
				}

				$actions = new FieldList(
					new FormAction('SubmitForm', 'Submit')
				);
	
				return new Form($this, 'RenderForm', $fields, $actions, $validator);
			}
			
			return false;
        }
		
		public function SubmitForm($data, $form) {
            $submission = new $form->controller->dataRecord->submission_class();
            $form->saveInto($submission);
            $submission->write();
			
			$form_config = $this->form_config;
			
			// send email to this address if specified
			if($form_config['sendToAll']){
				$EmailFormTo = $this->FormRecipients()->toArray();	
			}else{
				$row = DataObject::get_one("FormRecipient","Title = '".$data['Recipient']."' AND FormPageID = '".$this->ID."'");
				$EmailFormTo = $row->Email;
			}
			
			// Email to site Admin
			if( $EmailFormTo )
			{
				$utils = new FormUtilities();
				if($form_config['sendToAll']){
					foreach($EmailFormTo as $email){
						$utils->SendSSEmail($this,$email->Email,$data);
					}
				}else{
					$utils->SendSSEmail($this,$EmailFormTo,$data);				
				}
			}
			
			if(($as = $this->AutoResponderSubject) && $ab = ($this->AutoResponder))
			{
				$utils = new FormUtilities();
				$utils->SendAutoResponder($as,$ab,$data['Email']);				
			}
			
            return $this->redirect('thanks');
        }
		
		public function FindRecipients()
		{
			$recips = $this->FormRecipients()->toArray();
			$output = array();
			foreach($recips as $recip)
			{
				$output[$recip->Title] = $recip->Title;
			}
			return $output;
		}
		
	}
?>