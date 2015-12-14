<?

	class FormPageSubmission extends DataObject
	{
		private static $has_one = array(
			'FormPage' => 'FormPage'
		);
		
		public function getCMSFields()
		{
			$fields = parent::getCMSFields();
			
			// see if we have any File or Image upload fields
			foreach($this->has_one() as $relName => $relObj)
			{
				if ($relObj == 'File' || $relObj == 'Image')
				{
					$fields->replaceField( $relName, new LiteralField($relName,'<div class="field"><label class="left">'.FormField::name_to_label($relName).'</label><div class="middleColumn"><span class="readonly text"><a href="'.$this->{$relName}()->getAbsoluteURL().'" target="_blank">'.$this->{$relName}()->getFilename().'</a></span></div></div>'));	
				}
			}
			$fields->removeByName('FormPageID');
			return $fields;
		}
		
		public function canDelete($member = null) { return true; }
		public function canEdit($member = null)   { return true; }
		public function canView($member = null)   { return true; }
	}
	
	class FormRecipient extends DataObject
	{
		private static $db = array(
			'SortOrder' => 'Int',
			"Title" => "Varchar(255)",
			"Email" => "Varchar(255)"
		);
		
		private static $has_one = array(
			"Page" => "Page"
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
		
		public function canCreate($member = null) { return true; }
		public function canDelete($member = null) { return true; }
		public function canEdit($member = null)   { return true; }
		public function canView($member = null)   { return true; }
	
		function validate()
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
				new GridFieldSortableRows('SortOrder'),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewButton('toolbar-header-right'),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldDetailForm()
			);
			$fields->addFieldToTab('Root.FormControls', new GridField('FormRecipients','Form Recipients',$this->FormRecipients(),$recips_config));
			$fields->addFieldToTab('Root.FormControls', new HTMLEditorField('ThankYouText','Text after form submission'));
			
			$submits_config = GridFieldConfig::create()->addComponents(
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldViewButton(),
				new GridFieldDetailForm(),
				new GridFieldDeleteAction(),
				$exportBtn = new GridFieldExportButton()
			);
			$submission_class = $this->ClassName."Submission";
			$export_fields = Config::inst()->get($submission_class, 'export_fields', Config::UNINHERITED);
			if (!empty($export_fields))
			{
				$exportBtn->setExportColumns($export_fields);
			}
			$fields->addFieldToTab('Root.FormSubmissions', new GridField($submission_class,'Form Submissions',DataObject::get($submission_class,"FormPageID = ".$this->ID),$submits_config));
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
					$dir."/javascript/jquery.ui.themes.css",
					$dir."/css/form.css"
				)
			);
		}
		
		function PageJS()
		{
			$dir = ViewableData::ThemeDir();
			return array_merge(
				parent::PageJS(),
				array(
					$dir."/javascript/jquery.validate.nospam.js",
					$dir."/javascript/jquery-ui.js",
					$dir."/javascript/additional-methods.js"
				)
			);
		}
		
		function CustomJS()
		{
			$JS = parent::CustomJS();
			$FormConfig = $this->FormConfig();
			$JS .= "
$(document).ready(function(){
	$(\"#Form_RenderForm\").validate({
		".(($FormConfig['useNospam']) ? "useNospam: true," : null)."
	});
});
			";
			return $JS;
		}
		
		public function FormFields()
		{
			return array();
		}
		
		public function FormConfig()
		{
			return array();
		}
		
		public function RenderForm() 
		{
			if($form_fields = $this->FormFields())
			{
				$fields = new FieldList();
				if ($form_error = Session::get('FormError'))
				{
					Session::set('FormError',false);
					$fields->push( new LiteralField('form_error','<p class="form-error">'.$form_error.'</p>'));
				}
				$validator = new RequiredFields();
				$utils = new FormUtilities();
				$fieldGroups = array();
				foreach($form_fields as $FieldName => $data)
				{
					if ( ($data['Value']) && (is_string($data['Value'])) )
					{
						$method_home = method_exists($this,$data['Value']) ? $this : (method_exists($utils,$data['Value']) ? $utils : false);
						$data['Value'] = $method_home ? $method_home->$data['Value']() : $data['Value'];
					}

					$Label = ($data['Group']) ? '' : ($data['Label']?$data['Label']:FormField::name_to_label($FieldName));
					$field = new $data['FieldType']($FieldName,$Label,($data['Value']?$data['Value']:null),($data['Default']?$data['Default']:null));
					if($data['ExtraClass'])$field->addExtraClass($data['ExtraClass']);
					if($data['Config'] && is_array($data['Config']))
					{
						foreach($data['Config'] as $key => $value)
						{
							$field->setConfig($key,$value);
						}
					}	
					if($data['Required'])
					{
						$validator->addRequiredField($FieldName);
						$field->addExtraClass('required');
					}
					if($data['Group'])
					{
						if (!isset(${$data['Group']}))
						{
							${$data['Group']} = new FieldGroup($data['Group']);
							$fields->push(${$data['Group']});
							${$data['Group']}->FieldCount = 0;
							$fieldGroups[] = ${$data['Group']};
						}
						$field->setRightTitle((($data['Label'])?$data['Label']:FormField::name_to_label($FieldName)));

						${$data['Group']}->push($field);
						${$data['Group']}->FieldCount++;
					}
					else
					{
						$fields->push($field);
					}
				}
				
				// update the class on the field groups to properly display the grouped fields horizontally
				foreach($fieldGroups as $fieldGroup)
				{
					$fieldGroup->addExtraClass('stacked col'.$fieldGroup->FieldCount);
				}

				$submitText = "Submit";
				if($config = $this->FormConfig())
				{
					$submitText = $config['submitText'] ? $config['submitText'] : $submitText;
				}

				$actions = new FieldList(
					new FormAction('SubmitForm', $submitText)
				);
	
				$form = new Form($this, 'RenderForm', $fields, $actions, $validator);
				if ($defaults = Session::get("FormInfo.Form_RenderForm.data"))
				{
					$form->loadDataFrom($defaults);
					Session::set("FormInfo.Form_RenderForm.data",false);
				}
				return $form;
			}
			
			return false;
        }
		
		public function SubmitForm($data, $form) 
		{
			$form_config = $this->FormConfig();
			// magical spam protection
			if ( (!FormUtilities::validateAjaxCode()) && ($form_config['useNospam']) )
			{
				Session::set("FormInfo.Form_RenderForm.data", $data);
				Session::set("FormError", "Error, please enable javascript to use this form.");
				return $this->redirectBack();	
			}
			
			$submission_class = $this->ClassName."Submission";
            $submission = new $submission_class;
            $form->saveInto($submission);
			$submission->FormPageID = $this->ID;
            $submission->write();
			
			$form_config = $this->FormConfig();
			
			// send email to this address if specified
			if($form_config['sendToAll']){
				$EmailFormTo = $this->FormRecipients()->toArray();	
			}else{
				$EmailFormTo = $this->FormRecipients()->filter(array("Title" => $data['Recipient']));
			}
			
			$utils = new FormUtilities();
			// Email to site Admin
			if( $EmailFormTo )
			{
				foreach($EmailFormTo as $email)
				{
					$utils->SendSSEmail($this,$email->Email,$data,$submission);
				}
			}
			
			if(($as = $this->AutoResponderSubject) && $ab = ($this->AutoResponder))
			{
				$utils->SendAutoResponder($as,$ab,$data['Email']);				
			}
			
			$this->extend('onAfterSubmit',$submission);
			
			if($form_config['PageAfterSubmit'])
			{
				$page = $this->ClassName."_".$form_config['PageAfterSubmit'];
				return $this->customise($data)->renderWith(array($page,'Page'));
			}
            return $this->redirect($this->Link('thanks'));
        }
		
		public function RecipientFieldConfig()
		{
			$recips = $this->FindRecipients();
			if (count($recips))
			{
				$form_config = $this->FormConfig();
				if ((count($recips) == 1) || ($form_config['sendToAll']))
				{
					$config = array(
						'FieldType' => 'HiddenField',
						'Value' => key($recips)
					);
				}
				else
				{
					$config = array(
						'FieldType' => 'DropdownField',
						'Value' => $recips,
						'Label' => 'How May We Direct Your Inquiry'
					);
				}
				return $config;
			}
			$config = array(
				'FieldType' => 'HiddenField',
				'Value' => ''
			);
			return $config;
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