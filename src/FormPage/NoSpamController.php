<?php

namespace IQnection\FormPage;

use IQnection\FormUtilities\FormUtilities;
use SilverStripe\CMS\Controllers\ContentController;

class NoSpamController extends ContentController
{
	private static $allowed_actions = [
		'generate_code'
	];
	
	public function index()
	{
		return $this->httpError(404);
	}
	
	public function generate_code()
	{
		if ($code = $this->request->requestVar('code'))
		{
			if (FormUtilities::referSecurityCheck())
			{
				print FormUtilities::generateCode(trim($code))."|".trim($this->request->requestVar('id'));
				die();
			}
		}
		return $this->httpError(404);
	}
}


