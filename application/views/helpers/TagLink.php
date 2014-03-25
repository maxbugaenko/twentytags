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
class Helper_TagLink {


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
	public function tagLink(Model_Tag $tag, $url, $delurl, $class = "", $showRemoves) {
        $tagColor = "white";
        
	    $res = "<div class=\"divinline\" style=\"overflow:hidden;min-height:23px;vertical-align:top;padding-right:7px;margin:2px;background:".$tagColor."\">";
            if (Model_Tag::hasPicture($tag))
                $res .= "<div class=divinline style='margin-right:5px'><img class='small-shadow' style='width:25px;border:2px solid white;'  src='".$this->getView()->url(array('controller'=>'index', 'action'=>'tagpicture', 'id'=>$tag), 'default', true)."'/></div>";
            $res .= "<div class=divinline><a class=\"menu normal ".$class."\" href=\"".$url."\">".$tag->value."</a>";
            if ($showRemoves) {
                $res .= "<div class='divinline'><a style='position:relative;float:right;margin:5px;padding:1px;border:0px lightgray dashed;' href=\"".$delurl."\">x</a></div>";
            }
        $res .= "</div></div>";
	    return $res;
	}
}