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
 * @version $Id: String.php 1916 2010-04-29 11:44:33Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * Callback, which converts to String
 *
 * @package Callback
 */
class FaZend_Callback_String extends FaZend_Callback
{

    /**
     * Returns a short name of the callback
     *
     * @return string
     */
    public function getTitle()
    {
        return '(string)';
    }
    
    /**
     * Returns an array of inputs expected by this callback
     *
     * @return string[]
     */
    public function getInputs()
    {
        return array('string');
    }
    
    /**
     * Calculate and return
     *
     * @param array Array of params
     * @return mixed
     * @throws FaZend_Callback_String_InvalidArguments
     */
    protected function _call(array $args)
    {
        if (count($args) != 1) {
            FaZend_Exception::raise(
                'FaZend_Callback_String_InvalidArguments', 
                "Exactly one argument is required"
            );
        }
        return strval(array_shift($args));
    }

}
