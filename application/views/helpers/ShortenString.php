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
class Helper_ShortenString {


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
	* Display for your information block
	*
	* @return string
	*/
	public function shortenString($line, $length = 25, $start = 0) {
		if (strlen($line) > $length):
			$shortened = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$start.'}'.
	                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$length.'}).*#s',
	                       '$1',$line);
			$result = $shortened." ...";
		else:
			$result = $line;
		endif;
		return $result;
	}

}
