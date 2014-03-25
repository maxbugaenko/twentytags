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
 * @version $Id: Exec.php 1930 2010-05-14 06:16:10Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * One shell task
 *
 * @package Exec
 */
class FaZend_Exec extends FaZend_StdObject
{

    const LOG_SUFFIX = 'log';
    const PID_SUFFIX = 'pid';
    const DATA_SUFFIX = 'data';

    const WIN_FAKE_PID = 9999;

    /**
     * Static cache of running PID's
     *
     * @var int[]
     */
    protected static $_running = array();

    /**
     * Shall we document our operations in log?
     *
     * @var boolean
     */
    protected static $_isVerbose = false;

    /**
     * Unit testing is running now?
     *
     * @var boolean
     */
    protected static $_isTesting = false;

    /**
     * Shell command
     *
     * @var string
     */
    protected $_cmd;

    /**
     * Directory to work 
     *
     * @var string
     */
    protected $_dir;

    /**
     * Unique name of it
     *
     * @var string
     */
    protected $_name;
    
    /**
     * Shall we show details in log?
     *
     * @var boolean
     */
    protected $_detailed;
    
    /**
     * This operation is cycled and should be executed again and again?
     *
     * @var boolean
     */
    protected $_cycled;

    /**
     * Construct it
     *
     * @param string Name of the task, unique!
     * @param string Shell cmd
     * @return void
     */
    protected function __construct($name, $cmd = null)
    {
        $this->_name = $name;
        $this->_cycled = true;
        $this->_detailed = false;
        
        // load configuation data, if they exist
        $dataFile = self::_fileName(self::_uniqueId($this->_name), self::DATA_SUFFIX);
        if (file_exists($dataFile)) {
            $this->_unserialize(@file_get_contents($dataFile));
        }

        if (!is_null($cmd))
            $this->_cmd = $cmd;
    }

    /**
     * Shall we document our operations in log?
     *
     * @param boolean Shall we?
     * @return void
     */
    public static function setIsVerbose($isVerbose = true) 
    {
        self::$_isVerbose = $isVerbose;
    }

    /**
     * Shall we disable all real-life calls?
     *
     * @param boolean Shall we?
     * @return void
     */
    public static function setIsTesting($isTesting = true) 
    {
        self::$_isTesting = $isTesting;
    }

    /**
     * Create new task
     *
     * @param string Name of the task, unique!
     * @param string Command to execute
     * @return FaZend_Exec
     */
    public static function factory($name, $cmd = null)
    {
        return new FaZend_Exec($name, $cmd);
    }
    
    /**
     * Synchronous call, gateway to shell_exec()
     *
     * @param string Command to execute
     * @param string|null Directory to run it in
     * @param string|null The result you expect to get, will be returned in testing mode
     * @return string
     * @throws FaZend_Exec_ChdirFailureException
     */
    public static function exec($cmd, $dir = null, $expectedResult = null) 
    {
        if (!is_null($dir) && !self::$_isTesting) {
            $cwd = getcwd();
            if (false === @chdir($dir)) {
                FaZend_Exception::raise(
                    'FaZend_Exec_ChdirFailureException', 
                    "Can't change directory to '{$dir}'"
                );
            }
        }

        if (!self::$_isTesting) {
            $result = shell_exec($cmd);
        } else {
            $result = $expectedResult;
        }
        
        // protocol this operation in LOG if required
        if (self::$_isVerbose) {
            logg(
                "exec: '%s'%s, result (%d bytes): '%s'",
                $cmd,
                !is_null($dir) ? " in ('{$dir}')" : false,
                strlen($result),
                cutLongLine($result)
            );
        }

        if (!is_null($dir) && !self::$_isTesting) {
            if (false === @chdir($cwd)) {
                FaZend_Exception::raise(
                    'FaZend_Exec_ChdirFailureException', 
                    "Can't change directory back to '{$cwd}'"
                );
            }
        }
        
        return $result;
    }

    /**
     * Get output of currently running task, if it's running
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->output();
    }

    /**
     * Duration in seconds
     *
     * @return int Seconds
     */
    public function getDuration()
    {
        if (!$this->isRunning())
            return false;

        $pidFile = self::_fileName(self::_uniqueId($this->_name), self::PID_SUFFIX);

        if (!file_exists($pidFile))
            return false;

        return time() - @filemtime($pidFile);
    }

    /**
     * Get process id
     *
     * @return int
     */
    public function getPid()
    {
        if (!$this->isRunning())
            return false;

        return self::_pid(self::_uniqueId($this->_name));
    }

    /**
     * Is it still running?
     *
     * @return boolean
     */
    public function isRunning()
    {
        return self::_isRunning(self::_uniqueId($this->_name), $this->_cycled);
    }

    /**
     * Execute it and return log
     *
     * @return string
     */
    public function execute()
    {
        // if the task IS running now - just return it's output
        if ($this->isRunning()) {
            return $this->output();
        }
            
        $id = self::_uniqueId($this->_name);
            
        // maybe it was executed already and we should NOT re-run it again
        if (!$this->_cycled && (self::_output($id) !== false)) {
            return $this->output();
        }
                                
        // serialize and save all local variables
        $dataFile = self::_fileName($id, self::DATA_SUFFIX);
        if (!@file_put_contents($dataFile, $this->_serialize())) {
            FaZend_Exception::raise(
                'FaZend_Exec_DataSaveFailure', 
                "Failed to save local data before execution: '{$dataFile}'"
            );
        }

        // execute the task
        self::_execute($id, $this->_cmd, $this->_dir);

        // return output of the task
        return $this->output();
    }

    /**
     * Get output
     *
     * @return string|false False if the task was never executed yet
     */
    public function output()
    {
        // calculate unique ID of the task
        $id = self::_uniqueId($this->_name);
        
        // get an output of the task
        $output = self::_output($id);
        
        // if it was never executed before
        if ($output === false)
            return false;
        
        // if we don't need to return full details of the task - just return output
        if (!$this->_detailed)
            return $output;

        $files = '';
        foreach (array(self::PID_SUFFIX, self::DATA_SUFFIX, self::LOG_SUFFIX) as $suffix) {
            $fileName = self::_fileName($id, $suffix);
            $files .= 
                "{$suffix}: '{$fileName}': " . 
                (file_exists($fileName) ? filesize($fileName) . 'bytes, ' . 
                Zend_Date::now()->set(filemtime($fileName)) : 'no file') .
                "\n";
        }
        
        return 
        "Name '{$this->_name}', ID: '{$id}'\n" .
        "Process ID: " . self::_pid($id) . "\n" .
        "Cmd: {$this->_cmd}\n" . 
        $files . "\n" .
        
        ($output !== false ? $output : 'NO OUTPUT');
    }

    /**
     * Stop it
     *
     * @return void
     */
    public function stop()
    {
        if (!$this->isRunning())
            return;

        self::_stop(self::_uniqueId($this->_name));
    }
    
    /**
     * Stop the task and clean all files
     *
     * @return void
     */
    public function clean()
    {
        $this->stop();
        self::_clean(self::_uniqueId($this->_name));
    }
    
    /**
     * Set log to be detailed
     *
     * @param boolean Show details?
     * @return $this
     */
    public function setDetailed($detailed = true)
    {
        $this->_detailed = $detailed;
        return $this;
    }

    /**
     * This operation should be cycled?
     *
     * @param boolean Cycled or not?
     * @return $this
     */
    public function setCycled($cycled = true) 
    {
        $this->_cycled = $cycled;
        return $this;
    }

    /**
     * Set directory to work in
     *
     * @param string Directory name (absolute)
     * @return $this
     */
    public function setDir($dir) 
    {
        $this->_dir = $dir;
        return $this;
    }

    /**
     * Calculate unique ID of the task by its name
     *
     * @param string Name of the task, unique!
     * @return string
     */
    protected static function _uniqueId($name)
    {
        return FaZend_Revision::getName() . '-' . preg_replace('/[^\w\d]/', '-', $name);
    }

    /**
     * Static file name in temp dir, with suffix
     *
     * @param string ID of the task
     * @param string|null Suffix to add
     * @return string
     */
    protected static function _fileName($id, $suffix = false)
    {
        return TEMP_PATH . '/' . $id . ($suffix ? '.' . $suffix : false);
    }

    /**
     * Is it running now?
     *
     * @param string ID of the task
     * @param boolean Is it cycled?
     * @return boolean
     */
    protected static function _isRunning($id, $cycled = true)
    {
        if (isset(self::$_running[$id]))
            return true;
        
        // PID file for this particular task (by ID)
        $pidFile = self::_fileName($id, self::PID_SUFFIX);

        // if no file - there is no process
        if (!file_exists($pidFile)) {
            self::_clean($id);
            return false;
        }

        // read process ID from file
        $pid = intval(file_get_contents($pidFile));

        // if the file is corrupted
        if ($pid === 0) {
            self::_clean($id);
            return false;
        }

        // if the process is NOT found by this ID
        if (shell_exec('ps -p ' . $pid . ' | grep ' . $pid) == '') {
            // we shall remove only PID file and work with log
            // next time we will remove log as well
            if ($cycled)
                self::_clean($id, true);
            else
                return false;
        }

        // remember PID for this task in a static array
        return self::$_running[$id] = $pid;
    }

    /**
     * Clean files
     *
     * @param string ID of the task
     * @param boolean Clear only PID file?
     * @return boolean
     */
    protected static function _clean($id, $pidOnly = false)
    {
        if (!$pidOnly) {
            @unlink(self::_fileName($id, self::LOG_SUFFIX));
            @unlink(self::_fileName($id, self::DATA_SUFFIX));
        }

        @unlink(self::_fileName($id, self::PID_SUFFIX));
    }
        
    /**
     * Get output log, from log file for the currently running task
     *
     * @param string ID of the task
     * @return false|string Output of the EXEC or false
     */
    protected static function _output($id)
    {
        return @file_get_contents(self::_fileName($id, self::LOG_SUFFIX));
    }

    /**
     * Get process ID
     *
     * @param string ID of the task
     * @return void
     */
    protected static function _pid($id)
    {
        if (!self::_isRunning($id))
            return false;

        return self::$_running[$id];
    }

    /**
     * Execute the command
     *
     * @param string ID of the task
     * @param string shell command
     * @return void
     */
    protected static function _execute($id, $cmd, $dir = null)
    {
        if (self::$_isTesting) {
            logg("FaZend_Exec skipped: $cmd");
            return;
        }
        
        // execute the command and quit, saving the PID
        // @see: http://stackoverflow.com/questions/222414/asynchronous-shell-exec-in-php
        if (!is_null($dir)) {
            $current = getcwd();
            chdir($dir);
        }

        $pidFile = self::_fileName($id, self::PID_SUFFIX);

        if (!self::_isWindows()) {
            $shell = $cmd . ' >> ' . 
                escapeshellarg(self::_fileName($id, self::LOG_SUFFIX)) . ' 2>&1 & echo $! > ' . 
                escapeshellarg($pidFile);
        } else {
            $shell = $cmd . ' >> ' . escapeshellarg(self::_fileName($id, self::LOG_SUFFIX)) . ' 2>&1';
            file_put_contents($pidFile, (string)self::WIN_FAKE_PID);
        }

        // execute it!
        shell_exec($shell);
        if (self::$_isVerbose)
            logg("FaZend_Exec executed: {$shell}");

        if (!is_null($dir)) {
            chdir($current);
        }

        self::$_running[$id] = (int)@file_get_contents($pidFile);
    }

    /**
     * Stop the script
     *
     * @param string ID of the task
     * @return void
     */
    protected static function _stop($id)
    {
        if (self::_isWindows())
            return;

        // stop execution
        shell_exec('kill -9 ' . self::_pid($id));
    }

    /**
     * Is it windows OS?
     *
     * @return boolean
     */
    protected static function _isWindows()
    {
        return stristr(PHP_OS, 'win') !== false;
    }

}
