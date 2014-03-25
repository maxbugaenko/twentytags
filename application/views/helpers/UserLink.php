<?php
/**
 *
 * Copyright (c) 2009, partnerslab.com
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of partnerslab.com. located at
 * www.partnerslab.com. If you received this code occacionally and without intent to use
 * it, please report this incident to the author by email: privacy@scraber
 *
 * @author David Akimov <david@partnerslab.com>
 * @copyright Copyright (c) partnerslab.com, 2009
 * @version $Id$
 *
 */

/**
 * Displays for your information block
 *
 */
class Helper_UserLink {


	private $_view;

	/**
	 * Save view locally
	 *
	 * @return void
	*/
	public function setView(Zend_View_Interface $view) {
		$this->_view = $view;
	}           

	/**
	 * Get view saved locally
	 *
	 * @return Zend_View
	*/
	public function getView() {
		return $this->_view;
	}

	/**
     * Checks if i am currently viewing my profile
	 *
	 * @param Model_User specific user to display
	 * @param string class specific CSS class to display user link
	 * @return boolean true/false 
	*/
	public function userLink(Model_User $user, $class = "", $size = "20", $story = false) {
	    $add = "";
	    if (Model_User::isLoggedIn()) {
	        if ($user == Model_User::me())
	            $add = "(я)";
	    }
		if ($story)
	    	return "<a style='font-family:StoryBold,\"Trebuchet MS\";font-size:".$size."px;' class='".$class."' href='".$this->getView()->url(array('nickname'=>$this->getView()->escape($user->nickname), 'action'=>false), 'profile', true)."'>".$user->nickname."</a>";
		else 
	    	return "<a class='".$class."' href='".$this->getView()->url(array('nickname'=>$this->getView()->escape($user->nickname), 'action'=>false), 'profile', true)."'>".$user->name."</a>";
	}

}
