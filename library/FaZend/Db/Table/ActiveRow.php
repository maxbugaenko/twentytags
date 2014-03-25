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
 * @version $Id: ActiveRow.php 2026 2010-06-09 15:33:33Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see Zend_Db_Table_Row
 */
require_once 'Zend/Db/Table/Row.php';

/**
 * One row, ActiveRow pattern
 *
 * Usage example:
 *
 * <code>
 * $row = new FaZend_Db_Table_ActiveRow_product(13); // ROW in "product" SQL table
 * echo $row->owner->email; // get the email of the product owner
 * </code>
 *
 * @see http://code.google.com/p/fazend/wiki/FaZend_Db_Table_ActiveRow
 * @see http://framework.zend.com/manual/en/zend.loader.autoloader.html
 * @package Db
 */
abstract class FaZend_Db_Table_ActiveRow extends Zend_Db_Table_Row
{
    
    /**
     * Name of column in DB that contains an integer number of the row
     */
    const ID_COLUMN = 'id';

    /**
     * List of all tables in the db schema
     *
     * Locally cached in order to avoid 'SHOW TABLES' request to the
     * database performed by listTables()
     *
     * @return string[]
     */
    private static $_allTables;

    /**
     * Mapping of certain table columns to PHP classes
     *
     * It's an associative array, where keys are regular expressions
     * and values are class names.
     *
     * @var string
     * @see addMapping()
     * @see FaZend_Callback
     */
    protected static $_mapping = array();

    /**
     * Time when the latest call has been made
     *
     * @var int
     */
    protected static $_latestCallTime = null;

    /**
     * Internal holder of ROW id, until the data array is filled
     *
     * @return int|string
     */
    private $_preliminaryKey;
    
    /**
     * Ignore NULL values and treat them as normal values
     *
     * By default when NULL value is received from the DB, it is 
     * NEVER converted to any type, but returned as NULL in PHP. When
     * this property is set to TRUE, such behavior will be changed.
     * NULL values will be treated as regular data, and type casting
     * will be performed.
     *
     * @var boolean
     * @see __get()
     * @see setIgnoreNull()
     */
    protected $_ignoreNull = false;

    /**
     * List of cached properties, in order to avoid duplicate calculation
     *
     * @var mixed[]
     * @see __get()
     */
    protected $_cachedProperties = array();

    /**
     * Add new mapping
     *
     * @param string Regular expression to match, e.g. "/user\.project/"
     * @param callback
     * @return void
     * @see self::$_mapping
     */
    public static function addMapping($regex, $callback) 
    {
        self::$_mapping[$regex] = FaZend_Callback::factory($callback);
    }

    /**
     * Create new row or load the existing one
     *
     * @param integer|false ID of the row to retrieve, otherwise creates NEW row
     * @return void
     */
    public function __construct($id = false)
    {
        /**
         * To make sure that the table class is loaded
         */
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->autoload($this->_tableClass);

        // if the ID provided - find this row and save it
        if (is_integer($id)) {
            // create normal row
            parent::__construct();

            /**
             * save the id into the class
             * we don't load any data from DB at this stage, just remembering
             * the ID of the row
             * later data will be loaded, but later, in __get() method
             */
            $this->_preliminaryKey = $id;

            // inject this object into flyweight
            FaZend_Flyweight::inject($this, $id);
            return;
        }
        
        if ($id !== false) {    
            // $id is NOT equal to FALSE
            // if it's not an array - that it's a mistake for sure
            if (!is_array($id)) {
                FaZend_Exception::raise(
                    'FaZend_Db_Table_InvalidConstructor', 
                    sprintf(
                        '%s::new(%s: %s) has incorrect param type (neither INT nor ARRAY)',
                        get_class($this),
                        $id,
                        gettype($id)
                    )
                );
            }

            // otherwise just pass through to the default constructor
            parent::__construct($id);
            return;
        }
        
        // $id is empty (equals to FALSE) and it means that we should
        // create a NEW object, from scratch
        parent::__construct();

        // get information from the table
        $info = $this->_table->info();

        // and create internal data array with empty values
        // for all columns
        $this->_data = array_fill_keys($info['cols'], null);
    }
    
    /**
     * Ignore NULL values, and treat them as regular data
     *
     * @param boolean Shall we ignore NULL values?
     * @return $this
     */
    public function setIgnoreNull($ignoreNull = true) 
    {
        $this->_ignoreNull = $ignoreNull;
        return $this;
    }
    
    /**
     * Refreshes properties from the database.
     *
     * @return void
     * @see Zend_Db_Table_Row::refresh()
     */
    public function refresh()
    {
        $this->_cachedProperties = array();
        return parent::refresh();
    }

    /**
     * Save the object
     *
     * @return void
     * @throws FaZend_Db_Table_SaveException
     */
    public function save()
    {
        try {
            parent::save();
        } catch (Exception $e) {
            FaZend_Exception::raise(
                'FaZend_Db_Table_SaveException',
                sprintf(
                    'Failed to save instance of %s: %s',
                    get_class($this),
                    $e->getMessage()
                ),
                get_class($e)
            );
        }
        // inject this object into flyweight
        FaZend_Flyweight::inject($this, intval(strval($this)));
    }

    /**
     * Return object by the field
     *
     * @param string Name of the column
     * @param string Name of the class to be used for instantiating of this row
     * @return mixed
     * @deprecated This method will be REMOVED soon! Use class mapping instead!
     */
    public function getObject($name, $class)
    {
        // make sure the class has live data from DB
        $this->loadLiveData();
        $value = parent::__get($name);
        
        // maybe toArray() already produced object
        if (!is_scalar($value)) {
            $value = intval((string)$value);
        }

        return new $class(is_numeric($value) ? intval($value) : $value);
    }

    /**
     * Show the ROW as a string 
     *
     * @return string ID of the row, as string
     */
    public function __toString()
    {
        try {
            return strval($this->__get('__id'));
        } catch (Exception $e) {
            FaZend_Log::err(get_class($e) . ': ' . $e->getMessage());
        }
        return '0';
    }

    /**
     * Get list of columns in this ROW
     *
     * @return string[]
     */
    public function getColumnNames()
    {
        return array_keys($this->_data);
    }

    /**
     * Delete the row
     *
     * @return mixed
     * @throws FaZend_Db_Table_DeleteException
     */
    public function delete()
    {
        // make sure the class has live data from DB
        $this->loadLiveData();

        // remove the object from the flyweight factory
        FaZend_Flyweight::inject($this, intval(strval($this)));

        try {
            return parent::delete();
        } catch (Exception $e) {
            FaZend_Exception::raise(
                'FaZend_Db_Table_DeleteException',
                sprintf(
                    'Failed to delete instance of %s: %s',
                    get_class($this),
                    $e->getMessage()
                ),
                get_class($e)
            );
        }
        return 0;
    }

    /**
     * Row actually exists in the database?
     *
     * @return boolean
     */
    public function exists()
    {
        try {
            // make sure the class has live data from DB
            $this->loadLiveData();
        } catch (FaZend_Db_Table_NotFoundException $e) {
            assert($e instanceof Exception); // for ZCA
            return false;
        }
        return true;
    }

    /**
     * Create and array from the row
     *
     * This method overrides Zend_Db_Table_Row::toArray() because of ORM
     * concept we are using. With normal Zend Row you will get a plain
     * array with values. With FaZend you will get an array with plain
     * values AND objects. 
     *
     * @return array
     */
    public function toArray() 
    {
        $array = parent::toArray();
        foreach (array_keys($array) as $key) {
            if ($key == self::ID_COLUMN) {
                continue;
            }
            $array[$key] = $this->$key;
        }
        return $array;
    }

    /**
     * This ROW still contains data retrieved from DB?
     *
     * The row is clean when the data received from DB were not changed yet, and
     * the row doesn't require save(). The row is NOT clean when some changes
     * were made in memory and we should call save() in order to populate
     * such changes to the DB
     *
     * @return void
     */
    public function isClean() 
    {
        // the object just created, it is clean of course
        // even if it's not loaded yet (primaryKey is set means that the object
        // is not loaded yet, see loadLiveData()
        if (isset($this->_preliminaryKey)) {
            return true;
        }
        
        // if data and cleanData are not the same - it means that the object
        // is modified in memory
        if ($this->_data != $this->_cleanData) {
            return false;
        }
        
        // maybe some fields are modified
        return empty($this->_modifiedFields);
    }

    /**
     * Before any call we have to be sure that live data are available
     *
     * @param string Name of the method being called
     * @param array List of parameters passed
     * @return mixed
     */
    public function __call($method, array $args)
    {
        // make sure the class has live data from DB
        $this->loadLiveData();
        return parent::__call($method, $args);
    }

    /**
     * Find sub-objects by ID 
     *
     * @param string Name of the property to get
     * @return FaZend_Db_Table_Row|mixed
     */
    public function __get($name)
    {
        // you should not access ID field directly!
        if (strcasecmp($name, self::ID_COLUMN) === 0) {
            trigger_error(
                'ID should not be directly accesses in ' . get_class($this), 
                E_USER_WARNING
            );
        }

        // system field
        if (strcasecmp($name, '__id') === 0) {
            $name = self::ID_COLUMN;
        }

        // if we already have it locally, immediately return
        if (array_key_exists($name, $this->_cachedProperties)) {
            return $this->_cachedProperties[$name];
        }
        
        // if we are interested in just ID and data are not loaded yet
        // we just return the ID, that's it
        if ($name === self::ID_COLUMN && isset($this->_preliminaryKey)) {
            return $this->_cachedProperties[$name] = intval($this->_preliminaryKey);
        }

        // make sure the class has live data from DB
        $this->loadLiveData();

        // get raw value from Zend_Db_Table_Row
        $value = parent::__get($name);
        
        // maybe we're getting the value for the second time, 
        // and it was already calculated before and stored
        // in toArray()
        if (!is_scalar($value) && !is_null($value)) {
            return $this->_cachedProperties[$name] = $value;
        }
        
        // NULL received. We shall either return it as NULL
        // or ignore and continue to type casting
        if (is_null($value) && !$this->_ignoreNull) {
            return $this->_cachedProperties[$name] = null;
        }

        // We're trying to find a class of explicit mapping/casting
        // and convert $value to this class
        $mask = $this->_table->info(Zend_Db_Table::NAME) . '.' . $name;
        foreach (self::$_mapping as $regex=>$callback) {
            if (!preg_match($regex, $mask)) {
                continue;
            }
            return $this->_cachedProperties[$name] = $callback->call($value);
        }
                
        // We are trying to understand what is the class to be
        // used for this column, if it is necessary
        if (is_numeric($value) && $this->_isForeignKey(false, $name)) {
            // try to find this class and LOAD it if possible
            $rowClass = 'Model_' . ucfirst($name);
            if (!class_exists($rowClass)) {
                $rowClass = 'FaZend_Db_Table_ActiveRow_' . $name;
            }
        }

        // Here we do the type casting, if it is required, implementing
        // the core mechanism of ORM. Flyweight is used in order to avoid
        // instantiating of multiple PHP objects for the same row in the
        // database
        if (isset($rowClass)) {
            // return new $rowClass(is_numeric($value) ? intval($value) : $value);
            $value = FaZend_Flyweight::factory(
                $rowClass, 
                is_numeric($value) ? intval($value) : $value
            );
            
            // If the latest call has been completed later than a second ago
            if (self::$_latestCallTime < microtime(true) - 1) {
                // we should ping the DB here to avoid lost connections
                // @see http://framework.zend.com/issues/browse/ZF-9072
                // this code in Mysqli leads to:
                //Mysqli prepare error: This command is not supported in the prepared statement protocol yet
                // Zend_Db_Table::getDefaultAdapter()->query('--');
                self::$_latestCallTime = microtime(true);
            }
        }

        return $this->_cachedProperties[$name] = $value;
    }

    /**
     * Set sub-objects by ID 
     *
     * @param string Name of the property
     * @param mixed Value of the property to set
     * @return void
     */
    public function __set($name, $value)
    {
        // clean internal cache
        $this->_cachedProperties = array();
        
        // make sure the class has live data from DB
        $this->loadLiveData();

        if ($value instanceof Zend_Db_Table_Row) {
            $value = intval(strval($value));
        }

        return parent::__set($name, $value);
    }

    /**
     * Load real data into the row
     *
     * The method is called only when the live data are really necessary
     * in the row. For as long as possible we should wait and NOT load
     * the data. Since every load of the live data means new SQL query.
     *
     * @return void
     */
    public function loadLiveData()
    {
        // if the class data are not loaded yet, it's a good moment to do it
        if (!isset($this->_preliminaryKey)) {
            return;
        }

        // find data to fill the internal variables
        $rowset = $this->_table->find($this->_preliminaryKey);

        // if we failed to find anything with the given ID
        if (!count($rowset)) {
            // if the name of this class starts with 'FaZend' it means
            // that this row was received from retrieve() method from the table
            // without explicit notification of the RowClass. In such a case
            // we can't create an exception with class FaZend_Db_Table_tablename_NotFoundException
            // because we will end up in new table automatic creation by the table loader
            FaZend_Exception::raise(
                preg_match('/^FaZend_/', get_class($this)) ? 
                'FaZend_Db_Table_NotFoundException' : 
                get_class($this) . '_NotFoundException', // exception class name
                get_class($this) . " not found (ID: {$this->_preliminaryKey})", // description of the exception
                'FaZend_Db_Table_NotFoundException'  // parent class of the exception
            );
        }

        // if we found something  fill the data inside this class
        // and stop on it
        $this->_data = $this->_cleanData = $rowset->current()->toArray();

        // kill this variable, since we have LIVE data in the class already
        unset($this->_preliminaryKey);

        // clean internal cache
        $this->_cachedProperties = array();
    }

    /**
     * Does this column may be a link to subtable?
     *
     * @param string Name of the table, temporarily NOT used
     * @param string Name of the column
     * @return boolean
     */
    protected function _isForeignKey($table, $column)
    {
        // if the array of ALL tables in the db is NOT already defined
        // we should grab it from the DB by SQL request
        if (!isset(self::$_allTables)) {
            self::$_allTables = Zend_Db_Table_Abstract::getDefaultAdapter()->listTables();
        }

        // whether this table is in the DB or not?
        return in_array($column, self::$_allTables);
    }

}
