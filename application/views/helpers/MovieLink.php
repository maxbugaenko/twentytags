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
class Helper_MovieLink {


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
     * Dynamcally generates movie link
	 *
	 * @param Model_File specific file
	 * @param string path path to file
	 * @return string full URL
	*/
	public function movieLink(Model_File $file, $path, $isLog = false) {

		$secret = "x7WMArrgphSTENoB";
		$protectedPath = $path;
		$ipLimitation = false;      
		$hexTime = dechex(time());
		
		$realfilename = $file->filename;
		if ($isLog) {
			$cf = substr($realfilename, 0, -4);
			$realfilename = $cf.".log";
		}
			
		$ff = urlencode($realfilename);
		$filename = str_replace("+", "%20", $ff);

//		if ($ipLimitation) {
//		  $token = md5($secret . "/" . $realfilename . $hexTime . $_SERVER['REMOTE_ADDR']);
//		} else {
//		  $token = md5($secret . "/". $realfilename. $hexTime);
//		}
		
//		$url = $protectedPath . $token. "/" . $hexTime . "/" . $filename;
//		$url = $protectedPath . $token. "/" . $hexTime . "/" . $filename;
		
		return "http://".$file->server->alias.$path.$filename;
	}
}