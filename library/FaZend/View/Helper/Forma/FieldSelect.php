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
 * @version $Id: FieldSelect.php 2085 2010-07-09 10:11:44Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see FaZend_View_Helper_Forma_Field
 */
require_once 'FaZend/View/Helper/Forma/Field.php';

/**
 * Select field
 *
 * @package Model_Form
 */
class FaZend_View_Helper_Forma_FieldSelect extends FaZend_View_Helper_Forma_Field
{

    /**
     * Callback to use for options parsing
     *
     * @var FaZend_Callback
     * @see _setMask()
     */
    protected $_mask = null;

    /**
     * Callback to use for ID parsing
     *
     * @var FaZend_Callback
     * @see _setIdMask()
     */
    protected $_idMask = null;
    
    /**
     * Shall we use values or IDs?
     *
     * @var boolean
     * @see _setUseValues()
     */
    protected $_useValues = false;

    /**
     * List of options
     *
     * @var array
     * @see _setOptions()
     */
    protected $_options;
    
    /**
     * Private constructor
     *
     * @return void
     */
    protected function __construct(FaZend_View_Helper_Forma $helper)
    {
        parent::__construct($helper);
        $this->_setConverter(array($this, 'valueConverter'));
    }

    /**
     * Create and return form element
     *
     * @param string Name of the element
     * @return Zend_Form_Element
     */
    protected function _getFormElement($name)
    {
        return new Zend_Form_Element_Select($name);
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

        // if ID mask is provided we change the list of
        // options, in order to use new IDs
        if (!is_null($this->_idMask)) {
            $opts = array();
            foreach ($this->_options as $id=>$option) {
                $opts[$this->_idMask->call($option, $id)] = $option;
            }
            $this->_options = $opts;
        }
        
        // if mask is provided, we change the values of options to show
        // this list we will show in SELECT
        $multiOptions = array();
        foreach ($this->_options as $id=>$option) {
            if (!is_null($this->_mask)) {
                $multiOptions[$id] = $this->_mask->call($option, $id);
            } else {
                $multiOptions[$id] = strval($option);
            }
        }
        $element->setMultiOptions($multiOptions);
    }
    
    /**
     * Convert value
     *
     * @param mixed Current value
     * @return mixed New value
     */
    public function valueConverter($value) 
    {
        if ($this->_useValues) {
            if (!array_key_exists($value, $this->_options)) {
                FaZend_Exception::raise(
                    'FaZend_View_Helper_Forma_FieldSelect_InvalidOptionException',
                    "Option '{$value}' is out of range, it was not provided in the list of options"
                );
            }
            return $this->_options[$value];
        }
        return $value;
    }

    /**
     * Set list of options
     *
     * @param array Associative array of options
     * @param boolean Shall we sort the options?
     * @return void
     */
    protected function _setOptions($options, $sort = false)
    {
        $this->_options = $options;
        if ($sort) {
            asort($this->_options);
        }
    }

    /**
     * Set mask callback
     *
     * <code>
     * <?=$this->forma()
     *   ->addField('select', 'user')
     *     ->fieldOptions(Model_User::retrieveAll()) // we get a list of Model_User objects
     *     ->fieldMask('sprintf("(%s): %s", ${a1}->email, ${a1}->name)')
     * </code>
     *
     * In the example above, every element in the list of options will
     * be parsed through the given MASK before using in SELECT. You can
     * use any CALLBACK you wish. Just one parameter will be sent there --
     * the object.
     *
     * @param FaZend_Callback|mixed Callback to use as a mask for every option
     * @return void
     */
    protected function _setMask($callback)
    {
        $this->_mask = FaZend_Callback::factory($callback);
    }

    /**
     * Set mask callback for ID
     *
     * <code>
     * <?=$this->forma()
     *   ->addField('select', 'user')
     *     ->fieldOptions(Model_User::retrieveAll()) // we get a list of Model_User objects
     *     ->fieldMask('sprintf("(%s): %s", ${a1}->email, ${a1}->name)')
     *     ->fieldIdMask('strval(${a1})') // ID of the user
     *     ->fieldConverter('new Model_User(${a1})') // create user by ID selected
     * </code>
     *
     * @param FaZend_Callback|mixed Callback to use as a mask for every ID
     * @return void
     */
    protected function _setIdMask($callback)
    {
        $this->_idMask = FaZend_Callback::factory($callback);
    }
    
    /**
     * Shall we use values (TRUE) or IDs (FALSE)?
     *
     * @return void
     */
    protected function _setUseValues($useValues = true) 
    {
        $this->_useValues = $useValues;
    }

}
