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
class Helper_TagsetLink {


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
	public function tagsetLink(Model_Tagset $tagset, $pageviews) {
	    $url = $tagset->makeQuery();
	    $res = "<div class=\"tagset divinline bigger small-shadow gray".$class."\" style='overflow:hidden;background:#F8F8F8 '>";
            $res .= "<a class='ablack bigger' href=\"search?".$url."\">".$tagset->title."</a>";
            $res .= "<span class='big' style=\"margin-right:2px;margin-left:10px;padding-left:3px;padding-right:3px;border:1px lightgray dashed;color:#8A8A8A;\">".$pageviews."</span>";
        $res .= "</div>";
	    return $res;
	}
}