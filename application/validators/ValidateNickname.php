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
 * Nickname Validator
 */

class Validator_ValidateNickname extends Zend_Validate_Abstract {  
	
	const DUPLICATE_NICKNAME = 'duplicateNickname';  

	protected $_messageTemplates = array(  
		self::DUPLICATE_NICKNAME => 'Such nickname has already been registered'
	);


	/**
	 * Checks if nickname exists in the database
	 * @param String email
	 *
	 * @return boolean true or false
	*/
	private function isExisted ($nickname) {
		try {
			Model_User::findByNickname($nickname);
			$this->_error(self::DUPLICATE_NICKNAME);
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
			if (Model_User::getCurrentUser()->nickname != $value) {
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