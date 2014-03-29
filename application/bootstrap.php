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

define("ENTITY_IMAGES_PATH", realpath(dirname(__FILE__))."/../public/images/entities/");
define("ALERT_IMAGES_PATH", realpath(dirname(__FILE__))."/../public/images/alerts/");
/**
 * Bootstraper
 *
 * @package application
 */
class Bootstrap extends FaZend_Application_Bootstrap_Bootstrap { 

    /**
     * Initialize it.
     *
     * This method has to be the first in bootstrap!
     *
     * @return void
     */
    protected function _initAll()
    {
        $this->bootstrap('fz_logger');
        $this->bootstrap('fz_starter');
    }

    /**
     * Initialize translator
     *
     * @return void
     **/
    protected function _initTranslator() {
        $suffixes = array(
            true => '<span style="color:#268af0">*</span>', 
            false => '');
        FaZend_View_Helper_Forma::setLabelSuffixes($suffixes);        
    }
}

