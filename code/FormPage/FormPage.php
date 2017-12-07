<?php

use SilverStripe\Forms;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use SilverStripe\Core\Config\Config;

class FormPage extends Page
{
	private static $icon = "iq-basepages/images/icons/icon-form-file.gif";
	
	private static $db = array(
		"GAT_Activate" => "Boolean",
		"GAT_Category" => "Varchar(255)",
		"GAT_Label" => "Varchar(255)",
		"ThankYouText" => "HTMLText",
		"SendToAll" => 'Boolean'
	);
	
	private static $has_many = [
		"FormRecipients" => FormRecipient::class
	];
	
	public function CanCreate($member = null, $context = array()) { return (!preg_match('/\\FormPage$/',get_class($this))); }
	
	public function getCMSFields()
	{	
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.FormControls', Forms\CheckboxField::create('SendToAll','Send Submissions to All Recipients') );
		$fields->addFieldToTab('Root.FormControls', Forms\GridField\GridField::create(
			'FormRecipients',
			'Form Recipients',
			$this->FormRecipients(),
			Forms\GridField\GridFieldConfig_RecordEditor::create()->addComponent(
				new GridFieldSortableRows('SortOrder')
			)
		));

		$fields->addFieldToTab('Root.FormControls', Forms\HTMLEditor\HTMLEditorField::create('ThankYouText','Text after form submission'));

		$exportBtn = new Forms\GridField\GridFieldExportButton();
		$submission_class = \SilverStripe\Core\ClassInfo::shortName(get_class($this))."Submissions";
		$export_fields = Config::inst()->get($submission_class, 'export_fields', Config::UNINHERITED);
		if (!empty($export_fields))
		{
			$exportBtn->setExportColumns($export_fields);
		}
		$fields->addFieldToTab('Root.FormSubmissions', Forms\GridField\GridField::create(
			$submission_class,
			'Form Submissions',
			$this->{$submission_class}(),
			Forms\GridField\GridFieldConfig_RecordViewer::create()->addComponents(
				$exportBtn
			)
		));
		
		return $fields;
	}
	
	public function getSettingsFields()
	{
		$fields = parent::getSettingsFields();
		$fields->addFieldToTab('Root.GoogleFormTracking', Forms\CheckboxField::create('GAT_Activate','Activate Form Tracking') );
		$fields->addFieldToTab('Root.GoogleFormTracking', Forms\TextField::create('GAT_Category','Category') );
		$fields->addFieldToTab('Root.GoogleFormTracking', Forms\TextField::create('GAT_Label','Label') );
		$fields->addFieldToTab('Root.GoogleFormTracking', Forms\ReadonlyField::create('GAT_Value','Value','1') );
		$fields->addFieldToTab('Root.GoogleFormTracking', Forms\ReadonlyField::create('GAT_Action','Action','submit') );
		return $fields;
	}
}	

