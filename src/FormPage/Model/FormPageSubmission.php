<?php

namespace IQnection\FormPage\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Assets;
use SilverStripe\Core\Injector\Injectable;

class FormPageSubmission extends DataObject
{
	private static $table_name = 'FormPageSubmission';
	
	private static $has_one = array(
		'Page' => \Page::class
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName('LinkTracking');
		$fields->removeByName('FileTracking');
		// see if we have any File or Image upload fields
		foreach($this->hasOne() as $relName => $relObjType)
		{
			if (Injectable::singleton($relObjType) instanceof Assets\File)
			{
				$fields->replaceField( $relName, Forms\LiteralField::create($relName,'<div class="form-group field">
					<label class="form__field-label">'.Forms\FormField::name_to_label($relName).'</label>
					<div class="form__field-holder">
						<span class="readonly text">
							<a href="'.$this->{$relName}()->getAbsoluteURL().'" target="_blank">'.$this->{$relName}()->getFilename().'</a>
						</span>
					</div>
					</div>'));
			}
		}
		$fields->removeByName('PageID');
		return $fields;
	}
	
	public function canCreate($member = null, $context = array()) { return false; }
	public function canDelete($member = null, $context = array()) { return true; }
	public function canEdit($member = null, $context = array())   { return true; }
	public function canView($member = null, $context = array())   { return true; }
}

