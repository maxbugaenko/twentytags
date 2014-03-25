<?php
/**
 * FaZend Framework
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt. It is also available 
 * through the world-wide-web at this URL: http://www.fazend.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@fazend.com so we can send you a copy immediately.
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id: Fazend.php 2099 2010-07-29 10:03:01Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Resource for initializing FaZend framework
 *
 * @uses Zend_Application_Resource_Base
 * @package Application
 * @subpackage Resource
 */
class FaZend_Application_Resource_Fazend extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * Initializes the resource
     *
     * @return void
     * @see Zend_Application_Resource_Resource::init()
     */
    public function init() 
    {
        $options = $this->getOptions();
        $name = $options['name'];
        if (!$name) {
            FaZend_Exception::raise(
                'FaZend_Application_Resource_Fazend_Exception',
                "[Fazend.name] should be defined in your app.ini file"
            );
        }
        FaZend_Revision::setName($name);

        /** 
         * We should start caches as soon as possible, since
         * this resource will create a file for INCLUDE operations
         * and it has to know the name of FaZend revision.
         */
        $this->_bootstrap->bootstrap('fz_caches');

        // translation is mandatory, if it exists in the project
        $this->_bootstrap->bootstrap('fz_translate');
    }

}
