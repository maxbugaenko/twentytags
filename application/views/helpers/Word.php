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
class Helper_Word {


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
     * Translates the give word 
     * using russian array data source
	 *
	 * @return boolean true/false 
	*/
	public function word($word) {

	    
        // $ruTranslate = new Zend_Translate('array', 
        //       realpath(APPLICATION_PATH . '/../languages/'.$lang.'.php'), $lang);

        $lang = Zend_Registry::get('Zend_Locale')->getLanguage();
        $ruTranslate = new Zend_Translate('array', realpath(APPLICATION_PATH . '/../languages/ru.php'), $lang);
        return $ruTranslate->translate($word);     
	}

}


