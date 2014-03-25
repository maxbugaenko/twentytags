<?php

/**
 *
 * Copyright (c) 2009, xtube.fm
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of xtube.fm. located at
 * www.xtube.fm. If you received this code occacionally and without intent to use
 * it, please report this incident to the author by email: privacy@scraber
 *
 * @author David Akimov <david@xtube.fm>
 * @copyright Copyright (c) xtube.fm, 2009
 * @version $Id$
 *
 */

/**
 * Email Validator
 */

class Validator_ValidateEmail extends Zend_Validate_Abstract {  
	
	const DUPLICATE_EMAIL = 'duplicateEmail';  

	protected $_messageTemplates = array(  
		self::DUPLICATE_EMAIL => 'Такой имейл уже зарегистрирован'
	);

	/**
	 * Checks if email exists in the database
	 * @param String email
	 *
	 * @return boolean true or false
	*/
	private function isExisted ($email) {
		try {
			Model_User::findByEmail($email);
			$this->_error(self::DUPLICATE_EMAIL);
			return true;
		} catch (Exception $e) {
			return false;	
		}
	}

	/**
	 * 
	 *
	 *
	*/
	public function isValid($value, $context = null) {  
		if (Model_User::isLoggedIn()) {
			if (Model_User::getCurrentUser()->email != $value) {
				if ($this->isExisted($value))
					return false;
				else
					return true;
			} else {
				return true;
			}
		} else {
			if ($this->isExisted($value))
				return false;
			else 
				return true;
		}
	}
}
?>