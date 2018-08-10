<?php

namespace IQnection\MemberLoginForm;

use SilverStripe\Security\MemberAuthenticator;

class MemberLoginFormExtension extends MemberAuthenticator\MemberLoginForm 
{
	public function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) 
	{
		parent::__construct($controller, $name, $fields, $actions, $checkCurrentUser);
		$this->fields->renameField('Email', 'Username');
	}
}
