<?php

namespace IQnection\FormPage;

use SilverStripe\Forms;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;

class FormPage extends \Page
{
	private static $table_name = 'FormPage';
	
	private static $icon = "iqnection-pages/basepages:images/icons/icon-form-file.gif";
	
	private static $db = array(
		"GAT_Activate" => "Boolean",
		"GAT_Category" => "Varchar(255)",
		"GAT_Label" => "Varchar(255)",
		"ThankYouText" => "HTMLText",
		"FromEmail" => 'Varchar(255)',
		"SendToAll" => 'Boolean'
	);
	
	private static $has_many = [
		"FormRecipients" => \IQnection\FormPage\Model\FormRecipient::class
	];
	
	private static $defaults = [
		'ThankYouText' => '<p>Thank you for your message. Someone will get back to you shortly.</p>'
	];
	
	public function CanCreate($member = null, $context = array()) { return (get_class($this) != 'FormPage'); }
	
	public function getCMSFields()
	{	
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.FormControls', Forms\EmailField::create('FromEmail','Notification From Email') );
		$pageController = Injector::inst()->create($this->getControllerName());
		$formConfig = $pageController->FormConfig();
		if (!isset($formConfig['sendToAll']))
		{
			$fields->addFieldToTab('Root.FormControls', Forms\CheckboxField::create('SendToAll','Send Submissions to All Recipients') );
		}
		elseif ($formConfig['sendToAll'])
		{
			$fields->addFieldToTab('Root.FormControls', Forms\ReadonlyField::create('SendToAll_message','')
				->setValue('Submissions will be sent to all Recipients') );
		}
		$fields->addFieldToTab('Root.FormControls', Forms\GridField\GridField::create(
			'FormRecipients',
			'Form Recipients',
			$this->FormRecipients(),
			Forms\GridField\GridFieldConfig_RecordEditor::create()->addComponent(
				new GridFieldSortableRows('SortOrder')
			)
		));

		$fields->addFieldToTab('Root.FormControls', Forms\HTMLEditor\HTMLEditorField::create('ThankYouText','Text after form submission')->addExtraClass('stacked'));

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
	
	public function getSubmissionClass()
	{
		foreach($this->hasMany(true) as $hasMany)
		{
			if (preg_match('/Submission/',$hasMany))
			{
				return $hasMany;
			}
		}
	}
}	

