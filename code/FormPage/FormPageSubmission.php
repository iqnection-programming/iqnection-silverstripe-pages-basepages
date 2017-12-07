<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;

class FormPageSubmission extends DataObject
{
	private static $has_one = array(
		'FormPage' => FormPage::class
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		
		// see if we have any File or Image upload fields
		foreach($this->has_one() as $relName => $relObj)
		{
			if ($relObj == 'File' || $relObj == 'Image')
			{
				$fields->replaceField( $relName, Forms\LiteralField::create($relName,'<div class="field"><label class="left">'.Forms\FormField::name_to_label($relName).'</label><div class="middleColumn"><span class="readonly text"><a href="'.$this->{$relName}()->getAbsoluteURL().'" target="_blank">'.$this->{$relName}()->getFilename().'</a></span></div></div>'));
			}
		}
		$fields->removeByName('FormPageID');
		return $fields;
	}
	
	public function canDelete($member = null, $context = array()) { return true; }
	public function canEdit($member = null, $context = array())   { return true; }
	public function canView($member = null, $context = array())   { return true; }
}

