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
 * @version $Id: FieldSubmit.php 2185 2010-09-16 14:37:18Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see FaZend_View_Helper_Forma_Field
 */
require_once 'FaZend/View/Helper/Forma/Field.php';

/**
 * SUBMIT field
 *
 * @package Model_Form
 */
class FaZend_View_Helper_Forma_FieldSubmit extends FaZend_View_Helper_Forma_Field
{

    /**
     * Action to call when clicked
     *
     * @var FaZend_Callback
     */
    protected $_action;

    /**
     * Create and return form element
     *
     * @param string Name of the element
     * @return Zend_Form_Element
     */
    protected function _getFormElement($name)
    {
        return new Zend_Form_Element_Submit($name);
    }
    
    /**
     * Configure form element
     *
     * @param Zend_Form_Element The element to configure
     * @return void
     */
    protected function _configureFormElement(Zend_Form_Element $element)
    {
        parent::_configureFormElement($element);
        // $element->setAttrib('class', 'btn');

        if (isset($this->_value)) {
            $label = $this->_value;
        } else {
            $label = 'Submit';
        }

        $element->setLabel($label);
    }

    /**
     * Set action
     *
     * @param callback Validator of the field value
     * @return void
     * @see FaZend_Callback
     */
    protected function _setAction($callback)
    {
        $this->_action = FaZend_Callback::factory($callback);
    }

}
