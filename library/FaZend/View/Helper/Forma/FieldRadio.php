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
 * @version $Id: FieldRadio.php 2188 2010-09-16 15:18:55Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see FaZend_View_Helper_Forma_Field
 */
require_once 'FaZend/View/Helper/Forma/Field.php';

/**
 * Checkbox
 *
 * @package Model_Form
 */
class FaZend_View_Helper_Forma_FieldRadio extends FaZend_View_Helper_Forma_Field
{

    /**
     * List of options.
     *
     * @var array
     * @see _setOptions()
     */
    protected $_options;
    
    /**
     * Separator.
     *
     * @var string
     * @see _setSeparator()
     */
    protected $_separator = '<br/>';
    
    /**
     * Create and return form element
     *
     * @param string Name of the element
     * @return Zend_Form_Element
     */
    protected function _getFormElement($name)
    {
        return new Zend_Form_Element_Radio($name, null, array(), array(), '<p/>');
    }

    /**
     * Configure form element.
     *
     * @param Zend_Form_Element The element to configure
     * @return void
     */
    protected function _configureFormElement(Zend_Form_Element $element)
    {
        parent::_configureFormElement($element);
        
        $element
            ->setMultiOptions($this->_options)
            ->setOptions(
                array(
                    'escape' => false,
                )
            )
            ->setSeparator($this->_separator);
    }
    
    /**
     * Set list of options.
     *
     * @param array Associative array of options
     * @return void
     */
    protected function _setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * Set separator.
     *
     * @param string Separator between options.
     * @return void
     */
    protected function _setSeparator($separator)
    {
        $this->_separator = $separator;
    }

}
