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
 * @version $Id: Flyweight.php 2065 2010-06-17 10:03:32Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * Flyweight Factory
 *
 * You can use it for storage of simple objects, that don't need to be 
 * created every time from scratch. Example:
 *
 * <code>
 * $object = FaZend_Flyweight::factory('Model_User', $this, 'me@example.com');
 * </code>
 *
 * The instance of class Model_User won't be created again if it
 * already exists in the factory. The instance will be returned.
 *
 * @package Model
 * @see FaZend_Db_Table_ActiveRow::save()
 * @todo we should migrate it to Zend_Cache
 */
class FaZend_Flyweight
{

    /**
     * Prefix used for internal IDs, you should NOT start your names with this prefix
     */
    const ID_PREFIX = 'fz__';

    /**
     * Storage of objects
     *
     * @var array
     */
    static protected $_storage = array();

    /**
     * Clean entire factory
     *
     * @return void
     */
    public static function clean() 
    {
        self::$_storage = array();
    }

    /**
     * Instantiate and return an object
     *
     * @param string Name of the class to create
     * @param mixed Any amount of params to be passed to the constructor
     * @return mixed
     */
    public static function factory($class /*, many params... */)
    {
        $args = func_get_args();
        array_shift($args); // pop out the first argument
        
        // unique object ID in the storage
        $id = self::_makeId(array_merge(array($class), $args));
        
        // create an object, if it's not in the storage yet
        if (!array_key_exists($id, self::$_storage)) {
            self::$_storage[$id] = self::_makeObject($class, $args);
        }
        return self::$_storage[$id];
    }
    
    /**
     * Create or return an object by ID
     *
     * @param string Name of the class to create
     * @param string ID to use with this class
     * @param mixed Any amount of params to be passed to the constructor
     * @return mixed
     * @throws FaZend_Flyweight_InvalidIdException
     */
    public static function factoryById($class, $id /*, ...*/) 
    {
        if (strpos($id, self::ID_PREFIX) === 0) {
            FaZend_Exception::raise(
                'FaZend_Flyweight_InvalidIdException', 
                "Object ID '{$id}' is not allowed, it's reserved for FaZend"
            );
        }

        $args = func_get_args();
        array_shift($args); // pop out the first argument
        array_shift($args); // pop out the second argument
        
        // unique object ID in the storage
        $id = self::_makeId(array($class)) . $id;
        
        // create an object, if it's not in the storage yet
        if (!array_key_exists($id, self::$_storage)) {
            self::$_storage[$id] = self::_makeObject($class, $args);
        }
        return self::$_storage[$id];
    }

    /**
     * Inject new object into the storage
     *
     * @param object
     * @return void
     */
    public static function inject($object /* many params... */) 
    {
        $args = func_get_args();
        array_shift($args); // pop out the first argument
        
        // unique object ID in the storage
        $id = self::_makeId(array_merge(array(get_class($object)), $args));
        self::$_storage[$id] = $object;
    }

    /**
     * Inject an object by ID
     *
     * @param string ID
     * @param mixed The object to inject
     * @return void
     * @throws FaZend_Flyweight_DuplicateInjectionException
     */
    public static function injectById($id, $object) 
    {
        if (array_key_exists($id, self::$_storage)) {
            FaZend_Exception::raise(
                'FaZend_Flyweight_DuplicateInjectionException', 
                "Object with id '{$id}' already exists in Flyweight factory"
            );
        }
        if (strpos($id, self::ID_PREFIX) === 0) {
            FaZend_Exception::raise(
                'FaZend_Flyweight_InvalidIdException', 
                "Object ID '{$id}' is not allowed, it's reserved for FaZend"
            );
        }
        self::$_storage[$id] = $object;
    }

    /**
     * Factory has such ID?
     *
     * @param string ID
     * @return boolean
     * @throws FaZend_Flyweight_InvalidIdException
     */
    public static function hasId($id) 
    {
        if (strpos($id, self::ID_PREFIX) === 0) {
            FaZend_Exception::raise(
                'FaZend_Flyweight_InvalidIdException', 
                "Object ID '{$id}' is not allowed, it's reserved for FaZend"
            );
        }
        return array_key_exists($id, self::$_storage);
    }

    /**
     * Get an object with this ID
     *
     * @param string ID
     * @return mixed
     * @throws FaZend_Flyweight_InvalidIdException
     */
    public static function getById($id) 
    {
        if (strpos($id, self::ID_PREFIX) === 0) {
            FaZend_Exception::raise(
                'FaZend_Flyweight_InvalidIdException', 
                "Object ID '{$id}' is not allowed, it's reserved for FaZend"
            );
        }
        if (!array_key_exists($id, self::$_storage)) {
            FaZend_Exception::raise(
                'FaZend_Flyweight_DuplicateInjectionException', 
                "Object with id '{$id}' not found in factory"
            );
        }
        return self::$_storage[$id];
    }

    /**
     * Remove the object from the storage
     *
     * @param object
     * @return void
     */
    public static function remove($object /* many params... */) 
    {
        $args = func_get_args();
        array_shift($args); // pop out the first argument
        
        // unique object ID in the storage
        $id = self::_makeId(array_merge(array(get_class($object)), $args));
        if (array_key_exists($id, self::$_storage)) {
            unset(self::$_storage[$id]);
        }
    }

    /**
     * Generate ID out of a list of params
     *
     * @param array List of args
     * @return string
     */
    protected static function _makeId(array $args)
    {
        $id = '';
        foreach ($args as $arg) {
            switch (true) {
                case is_scalar($arg):
                    // kill this SPECIAL symbol from scalar arguments
                    $arg = 'S:' . str_replace(',', '\,', $arg);
                    break;
                case is_array($arg):
                    $arg = 'A:' . self::_makeId($arg);
                    break;
                case is_null($arg):
                    $arg = 'NULL';
                    break;
                default:
                    $arg = 'O:' . spl_object_hash($arg);
                    break;
            }
            $id .= ($id ? ',' : false) . $arg;
        }
        return self::ID_PREFIX . $id;
    }
    
    /**
     * Create an object from class name and list of params for contructor
     *
     * @param string Name of the class
     * @param array List of arguments for constructor
     * @return mixed
     */
    protected static function _makeObject($class, array $args) 
    {
        // initialize validator with dynamic list of params
        $object = null;
        $call = '$object = new $class(';
        for ($i=0; $i<count($args); $i++) {
            $call .= ($i > 0 ? ', ' : false) . "\$args[{$i}]";
        }
        $call .= ');';
        eval($call);
        return $object;
    }
    
}
