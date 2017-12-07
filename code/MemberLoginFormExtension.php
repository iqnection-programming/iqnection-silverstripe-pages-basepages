<?php

use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

class MemberLoginFormExtension extends MemberLoginForm 
{
	public function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) 
	{
		parent::__construct($controller, $name, $fields, $actions, $checkCurrentUser);
		$this->fields->renameField('Email', 'Username');
	}
}
