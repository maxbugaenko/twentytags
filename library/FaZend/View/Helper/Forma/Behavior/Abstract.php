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
 * @version $Id: Abstract.php 1757 2010-03-25 17:04:29Z yegor256@gmail.com $
 * @category FaZend
 */

require_once 'FaZend/View/Helper/Forma/Field.php';

/**
 * Abstract form behavior
 *
 * @package helpers
 */
abstract class FaZend_View_Helper_Forma_Behavior_Abstract
{
    
    /**
     * Arguments
     *
     * @var array
     * @see FaZend_View_Helper_Forma::_render()
     */
    protected $_args;
    
    /**
     * List of arguments passed to the method
     *
     * @var array
     * @see FaZend_View_Helper_Forma::_render()
     * @see setMethodArgs()
     */
    protected $_methodArgs = array();
    
    /**
     * Return of SUBMIT action
     *
     * @var mixed
     * @see FaZend_View_Helper_Forma::_render()
     * @see setReturn()
     */
    protected $_return;

    /**
     * Construct the class
     *
     * @param array List of arguments
     * @return void
     */
    public final function __construct(array $args)
    {
        $this->_args = $args;
    }
    
    /**
     * Set list of method args
     *
     * @return void
     */
    public function setMethodArgs(array $methodArgs) 
    {
        $this->_methodArgs = $methodArgs;
    }
    
    /**
     * Save SUBMIT action return
     *
     * @param mixed Return
     * @return void
     */
    public function setReturn($return) 
    {
        $this->_return = $return;
    }
    
    /**
     * Execute it
     *
     * @param string HTML to show (form or something else)
     * @param string Log of the form execution
     * @return void
     */
    abstract public function run(&$html, $log);

}
