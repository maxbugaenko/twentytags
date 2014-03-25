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
 * @version $Id: orm.php 1916 2010-04-29 11:44:33Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Initialize ORM
 *
 * @uses Zend_Application_Resource_Base
 * @package Application
 * @subpackage Resource
 * @see application.ini
 */
class FaZend_Application_Resource_fz_orm extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * Initializes the resource
     *
     * @return void
     * @see Zend_Application_Resource_Resource::init()
     * @see FaZend_Db_Table_RowLoader
     * @see FaZend_Db_TableLoader
     */
    public function init() 
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader(
            new FaZend_Db_Table_RowLoader(), 
            'FaZend_Db_Table_ActiveRow_'
        );
        $autoloader->pushAutoloader(
            new FaZend_Db_TableLoader(), 
            'FaZend_Db_ActiveTable_'
        );

        // caches are mandatory for proper functioning of ORM
        $this->_bootstrap->bootstrap('fz_caches');
    }

}
