<?php

namespace IQnection\FormPage;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Core;
use IQnection\FormUtilities\FormUtilities;

class FormPageController extends \PageController
{
	private static $allowed_actions = array(
		"RenderForm",
		"thanks"
	);

	public function PageCSS()
	{
		return [
			"javascript/jquery-ui.min.scss",
			"javascript/jquery.ui.theme.scss",
			"css/form.scss"
		];
	}

	public function PageJS()
	{
		return [
			"javascript/jquery.validate.nospam.js",
			"javascript/jquery-ui.js",
			"javascript/additional-methods.js"
		];
	}

	public function CustomJS()
	{
		$FormConfig = $this->FormConfig();
		$JS = "
(function($){
	\"use strict\";
	$(document).ready(function(){

		$(\"#Form_RenderForm\").validate({\n";
		if ($FormConfig['useNospam'])
		{
			$JS .= "useNospam: true,\n";
		}
		if ($this->GAT_Activate)
		{
			$JS .= "trackFormSubmit:{category:\"".htmlspecialchars($this->GAT_Category)."\",action:\"submit\",label:\"".htmlspecialchars($this->GAT_Label)."\",value:1},\n";
		}
		$JS .= "
		});\n";

		if ($this->hasDateField())
		{
			$JS .= "
		$('input.datePicker').each(function(){
			$(this).datepicker();
		});
";
		}

		$JS .= "
	});
}(jQuery));\n";
		return $JS;
	}

	public function hasDateField()
	{
		if ($form_fields = $this->FormFields())
		{
			foreach($form_fields as $form_field)
			{
				if ( (isset($form_field['DatePicker'])) && ($form_field['DatePicker']) )
				{
					return true;
				}
			}
		}
		return false;
	}

	public function FormFields()
	{
		return array();
	}

	public function FormConfig()
	{
		/*
		array(
			'useNospam' => bool,
			'submitText' => string 'Submit',
			'HoneyPot => string 'FieldName',
			'sendToAll => bool
		)
		*/
		return array();
	}

	public function RenderForm()
	{
		if($form_fields = $this->FormFields())
		{
			$fields = Forms\FieldList::create();
			if ($form_error = $this->request->getSession()->get('FormError'))
			{
				$this->request->getSession()->set('FormError',false);
				$fields->push( Forms\LiteralField::create('form_error','<p class="form-error">'.$form_error.'</p>'));
			}
			$validator = Forms\RequiredFields::create();
			$utils = new FormUtilities();
			$fieldGroups = array();
			foreach($form_fields as $FieldName => $data)
			{
				if ( (isset($data['Value'])) && ($data['Value']) && (is_string($data['Value'])) )
				{
					$method_home = method_exists($this,$data['Value']) ? $this : (method_exists($utils,$data['Value']) ? $utils : false);
					$data['Value'] = $method_home ? $method_home->{$data['Value']}() : $data['Value'];
				}

				$Label = (isset($data['Group'])) ? '' : (isset($data['Label']) ? $data['Label'] : Forms\FormField::name_to_label($FieldName));
				$fieldType = (preg_match('/\\\/',$data['FieldType'])) ? $data['FieldType'] : '\\SilverStripe\\Forms\\'.$data['FieldType'];
				if ($data['FieldType'] == \SilverStripe\Forms\DateField::class)
				{
					$data['FieldType'] = \SilverStripe\Forms\TextField::class;
					$data['DatePicker'] = true;
					if (!isset($data['Attributes']['autocomplete']))
					{
						$data['Attributes']['autocomplete'] = 'off';
					}
				}
				$field = $fieldType::create($FieldName,$Label,(isset($data['Value'])?$data['Value']:null),(isset($data['Default'])?$data['Default']:null));
				if ( (isset($data['DatePicker'])) && ($data['DatePicker']) )
				{
					$field->addExtraClass('date datePicker');
				}
				if ( (isset($data['EmptyString'])) && ($emptyString = $data['EmptyString']) )
				{
					$field->setEmptyString($emptyString);
				}
				if(isset($data['ExtraClass']))$field->addExtraClass($data['ExtraClass']);
				if(isset($data['Attributes']) && is_array($data['Attributes']))
				{
					foreach($data['Attributes'] as $attName => $attValue)
					{
						$field->setAttribute($attName,$attValue);
					}
				}
				if (isset($data['Config']) && is_array($data['Config']))
				{
					foreach($data['Config'] as $key => $value)
					{
						$field->setConfig($key,$value);
					}
				}
				if ( (isset($data['Required'])) && ($data['Required']) )
				{
					$validator->addRequiredField($FieldName);
					$field->addExtraClass('required');
				}
				if (isset($data['Group']))
				{
					if (!isset(${$data['Group']}))
					{
						${$data['Group']} = Forms\FieldGroup::create($data['Group']);
						$fields->push(${$data['Group']});
						${$data['Group']}->FieldCount = 0;
						$fieldGroups[] = ${$data['Group']};
					}
					$field->setRightTitle(((isset($data['Label'])) ? $data['Label'] : Forms\FormField::name_to_label($FieldName)));

					${$data['Group']}->push($field);
					${$data['Group']}->FieldCount++;
				}
				else

				{
					$fields->push($field);
				}

				// File field
				if ( ($data['FieldType'] == 'FileField') && (isset($data['AllowedExtensions'])) && (is_array($data['AllowedExtensions'])) )
				{
					$field->getValidator()->setAllowedExtensions($data['AllowedExtensions']);
					$field->setDescription('('.implode(', ',$data['AllowedExtensions']).')');
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
				$submitText = isset($config['submitText']) ? $config['submitText'] : $submitText;
				if ( (isset($config['HoneyPot'])) && ($honeyPotField = $config['HoneyPot']) )
				{
					$fields->push( Forms\TextField::create($honeyPotField)->addExtraClass('hpf') );
				}
			}

			$actions = Forms\FieldList::create(
				Forms\FormAction::create('SubmitForm', $submitText)
			);

			$form = Forms\Form::create($this, 'RenderForm', $fields, $actions, $validator);
			$form->setAttribute('autocomplete','off');
			if ($defaults = $this->request->getSession()->get("FormInfo.Form_RenderForm.data"))
			{
				$form->loadDataFrom($defaults);
			}
			$this->request->getSession()->clear("FormInfo.Form_RenderForm.data");
			$this->extend('updateForm',$form);
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
			$this->request->getSession()->set("FormInfo.Form_RenderForm.data", $data);
			$this->request->getSession()->set("FormError", "Error, please enable javascript to use this form.");
			return $this->redirectBack();
		}

		// if honeypot is used, redirect back
		if ( (isset($form_config['HoneyPot'])) && ($honeyPotField = $form_config['HoneyPot']) && ($data[$honeyPotField]) )
		{
			$this->request->getSession()->set("FormInfo.Form_RenderForm.data", $data);
			$this->request->getSession()->set("FormError", "Error, your submission has been detected as spam.");
			return $this->redirectBack();
		}

		$submission_class = $this->getSubmissionClass();
		$submission = $submission_class::create();
		$form->saveInto($submission);
		$submission->PageID = $this->ID;
		$submission->write();

		// send email to this address if specified
		if ( ( (isset($form_config['sendToAll'])) && ($form_config['sendToAll']) ) || ($this->SendToAll) )
		{
			$EmailFormTo = $this->FormRecipients()->toArray();
		}
		elseif ($recipTitle = $data['Recipient'])
		{
			$EmailFormTo = $this->FormRecipients()->filter(array("Title" => $recipTitle));
		}

		// Email to site Admin
		if( $EmailFormTo )
		{
            $emailSubject = $this->owner->getRecipientEmailSubject($data);
            $this->extend('updateRecipientEmailSubject', $emailSubject, $data);
			foreach($EmailFormTo as $email)
			{
				FormUtilities::SendSSEmail($this,$email->Email,$data,$submission,$this->FromEmail, $emailSubject);
			}
		}

		if(($as = $this->AutoResponderSubject) && ($ab = $this->AutoResponder))
		{
			FormUtilities::SendAutoResponder($as,$this->dbObject('AutoResponder')->forTemplate(),$data['Email'],$this->AutoResponderFromEmail,$submission,$data,$this->AutoResponderIncludeSubmission);
		}

		$this->onAfterSubmit($submission);
		$this->extend('onAfterSubmit',$submission);

		if ( (isset($form_config['PageAfterSubmit'])) && ($form_config['PageAfterSubmit']) )
		{
			$page = $this->ClassName."_".$form_config['PageAfterSubmit'];
			return $this->customise(array('Submission' => $submission))->renderWith(array($page,'Page'));
		}
		return $this->redirect($this->Link('thanks'));
	}

    public function getRecipientEmailSubject($data)
    {
        return $this->Title.' Form Submission';
    }

	public function onAfterSubmit($submission=null)
	{
		return $submission;
	}

	public function RecipientFieldConfig()
	{
		$recips = $this->FindRecipients();
		if (count($recips))
		{
			$form_config = $this->FormConfig();
			if ( (count($recips) == 1) || ($form_config['sendToAll']) || ($this->SendToAll) )
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
