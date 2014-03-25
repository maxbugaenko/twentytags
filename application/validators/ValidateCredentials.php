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
 * Validate credentials
 */

class Validator_ValidateCredentials extends Zend_Validate_Abstract {  
	
	public function isValid($creds, $context = null) {  
	    $email = $creds['email'];
	    $password = $creds['password'];
	    try {
            $user = FaZend_User::findByEmail($email);
        } catch (Exception $e) {
            return false;
        }
            
        if (!$user->isGoodPassword($password))
            return false;
            
        return true;
	}



}
?>