<?php
/**
 *
 * Copyright (c) 2009, twentytags.com
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of scraber.com. located at
 * www.scraber.com. If you received this code occacionally and without intent to use
 * it, please report this incident to the author by email: privacy@scraber
 *
 * @author Max Bugaenko <max.bugaenko@gmail.com>
 * @copyright Copyright (c) twentytags.com 2014
 * @version $Id$
 *
 */

/**
 * One user
 *
 * @package Model
 */
class Model_User extends FaZend_User {

    /**
     * Returns current user
     *
     * @return Model_User
     */
	public static function me () {
		return new Model_User((int)(string)self::getCurrentUser());
	}
    /**
     * This user is admin?
     *
     * @return boolean
     */
	public function isAdmin () {
		if ($this->admin == 1)
			return true;
		return false;
	}

    /**
     * Finds user by nickname
     *
     * @param string nickname
     * @return Model_User
    */
	public static function findByNickname($nickname) {
	    try {
		    $row = self::retrieve()
			    ->where('user.nickname = ?', $nickname)
			    ->setRowClass('Model_User')
			    ->fetchRow();
    	    return $row;
		} catch (Model_User_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_User_NicknameNotFound', 
    		"User with nickname '{$nickname}' doesn't exist", 'Exception');
	    }
	}

	/**
	 * Retrieves all users stored in the database 
	 * for creating the XML sitemap
	 *
	 * @return array Array of all users
	*/
	public static function retrieveAll () {
		return self::retrieve()
			->setRowClass('Model_User')
			->fetchAll();
	}


}
