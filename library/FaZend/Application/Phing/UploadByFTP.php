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
 * @version $Id: UploadByFTP.php 2291 2011-02-15 10:13:56Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see Task
 */
require_once 'phing/Task.php';

/**
 * This is Phing Task for uploading all files to FTP
 *
 * @see http://phing.info/docs/guide/current/chapters/ExtendingPhing.html#WritingTasks
 * @package Application
 * @subpackage Phing
 */
class UploadByFTP extends Task
{

    /**
     * These directories/files won't be uploaded
     */
    protected static $_forbidden = array(
        '.svn',
    );

    /**
     * Name of the destination server.
     * @var string
     */
    private $_server;

    /**
     * FTP user name.
     * @var string
     */
    private $_userName;

    /**
     * FTP password.
     * @var string
     */
    private $_password;

    /**
     * Destination director in FTP server.
     * @var string
     */
    private $_destDir;

    /**
     * Local directory with the sources to be uploaded.
     * @var string
     */
    private $_srcDir;

    /**
     * FTP handler.
     * @var integer
     */
    private $_ftp;

    /**
     * When we started?
     * @var float
     */
    private $_start;

    /**
     * Internal counter of files processed.
     * @var integer
     */
    private $_filesProcessed = 0;

    /**
     * Initiator (when the build.xml sees the task).
     * @return void
     */
    public function init()
    {
    }

    /**
     * Initalizer.
     * @param $fileName string
     * @return void
     */
    public function setserver($server)
    {
        $this->_server = $server;
    }

    /**
     * Initalizer.
     * @param $fileName string
     * @return void
     */
    public function setusername($userName)
    {
        $this->_userName = $userName;
    }

    /**
     * Initalizer.
     * @param $fileName string
     * @return void
     */
    public function setpassword($password)
    {
        $this->_password = $password;
    }

    /**
     * Initalizer.
     * @param $fileName string
     * @return void
     */
    public function setdestdir($destDir)
    {
        $this->_destDir = $destDir;
    }

    /**
     * Initalizer.
     * @param $fileName string
     * @return void
     */
    public function setsrcdir($srcDir)
    {
        $this->_srcDir = $srcDir;
    }

    /**
     * Executes.
     * @return void
     */
    public function main()
    {
        $this->_start = microtime(true);

        $this->_protocol(
            "FTP params received\n\tserver: {$this->_server}\n\tlogin: {$this->_userName}\n\tpassword: " .
            preg_replace('/./', '*', $this->_password) .
            "\n\tsrcDir: '{$this->_srcDir}'\n\tdestDir: '{$this->_destDir}'"
        );

        if (!$this->_server) {
            $this->_protocol("Server is not specified, the deployment won't happen");
            return;
        }

        $this->_ftp = @ftp_connect($this->_server);
        if ($this->_ftp === false) {
            $this->_failure("Failed to connect to FTP '{$this->_server}'");
        }
        $this->_protocol("Connected successfully to '{$this->_server}'");

        if (@ftp_login($this->_ftp, $this->_userName, $this->_password) === false) {
            $this->_failure("Failed to login to FTP '{$this->_server}'");
        }
        $this->_protocol("Logged in successfully to FTP as '{$this->_userName}'");

        // let's try to play with PASV
        $this->_setPassiveMode(true);
        if (@ftp_nlist($this->_ftp, '.') === false) {
            $this->_protocol("NLIST returns FALSE, we should try to change PASV");
            $this->_setPassiveMode(false);
            if (@ftp_nlist($this->_ftp, '.') === false) {
                $this->_failure("NLIST doesn't work, neither in normal nor in passive mode");
            }
        }

        if (@ftp_chdir($this->_ftp, $this->_destDir) === false) {
            $this->_failure("Failed to go to '{$this->_destDir}'");
        }
        $this->_protocol('Current directory in FTP: ' . ftp_pwd($this->_ftp));

        $currentDir = getcwd();
        $uploaded = $this->_uploadFiles($this->_srcDir);
        chdir($currentDir);
        $this->_protocol(
            sprintf(
                'Uploaded %d files out of %d total',
                $uploaded,
                $this->_filesProcessed
            )
        );

        if (@ftp_close($this->_ftp) === false) {
            $this->_failure("Failed to close connection to FTP '{$this->_server}");
        }
        $this->_protocol('Disconnected from FTP');

        // kill temp file
        if (isset($this->_tempFileName)) {
            unlink($this->_tempFileName);
        }
    }

    /**
     * Upload files, recursively.
     * @param string Path to upload (local path)
     * @return void
     */
    protected function _uploadFiles($path)
    {
        $dir = @scandir($path);
        if ($dir === false) {
            $this->_failure('Failed to open dir: ' . $path);
        }

        $ftpList = @ftp_nlist($this->_ftp, '.');
        if ($ftpList === false) {
            $this->_failure('Failed to NLIST at ' . @ftp_pwd($this->_ftp));
        }

        // delete obsolete elements from FTP server
        foreach ($ftpList as $ftpEntry) {
            if (!in_array($ftpEntry, $dir)) {
                $this->_notify();
                if (@ftp_delete($this->_ftp, $ftpEntry) !== false) {
                    continue;
                }
                // maybe it's a directory?
                // I can't delete directories recursively yet...
                //$this->_failure ("Failed to delete FTP file '$ftpEntry' in ".ftp_pwd ($this->_ftp));
            }
        }

        // calculate the amount of uploaded files
        $uploaded = 0;
        foreach ($dir as $entry) {
            // don't upload directories or forbidden files
            if (($entry[0] == '.') || in_array($entry, self::$_forbidden)) {
                continue;
            }
            $fileName = $path . '/' . $entry;
            $this->_notify();

            if (is_dir($fileName)) {
                // this directory doesn't exist yet on the server, we should create it
                if (@ftp_chdir($this->_ftp, $entry) === false) {
                    if (@ftp_mkdir($this->_ftp, $entry) === false) {
                        $this->_failure("Failed to MKDIR '{$entry}' in " . ftp_pwd($this->_ftp));
                    }
                    if (@ftp_chdir($this->_ftp, $entry) === false) {
                        $this->_failure("Failed to CHDIR to '{$entry}' in " . ftp_pwd($this->_ftp));
                    }
                    $this->_protocol("Created directory '{$entry}'");
                }

                $uploaded += $this->_uploadFiles($fileName);

                if (@ftp_cdup($this->_ftp) === false) {
                    $this->_failure("Failed to CDUP from '{$entry}' in " . ftp_pwd($this->_ftp));
                }
            } else {
                // this file already exists?
                if (in_array($entry, $ftpList)) {
                    $lastModified = @ftp_mdtm($this->_ftp, $entry);
                    if ($lastModified === -1) {
                        $this->_failure("Failed to get file modification time from ftp_mdtm('{$entry}')");
                    }

                    // if the server version is younger than the local - we skip this file
                    // only if the sizes are similar
                    if ($lastModified > filemtime($fileName)) {
                        $currentSize = @ftp_size($this->_ftp, $entry);
                        if ($currentSize === -1) {
                            $this->_failure("Failed to get size from ftp_size('{$entry}')");
                        }

                        // if the files are of the same size, don't upload again
                        if ($currentSize == filesize($fileName)) {
                            continue;
                        }
                    }

                }

                if (@ftp_put($this->_ftp, $entry, $fileName, FTP_BINARY) === false) {
                    $this->_failure(
                        sprintf(
                            "Failed to upload '%s' (%d bytes)",
                            $fileName,
                            filesize($fileName)
                        )
                    );
                }
                $uploaded++;
                $this->_protocol(
                    sprintf(
                        "Uploaded '%s' (%d bytes)",
                        substr($fileName, strlen($this->_srcDir) + 1),
                        filesize($fileName)
                    )
                );
            }
        }
        return $uploaded;
    }

    /**
     * Raise an exception and protocol the failure.
     * @param string The message to show in log
     * @return void
     * @throws BuildException
     */
    protected function _failure($text)
    {
        $this->_protocol($text);
        throw new BuildException($text);
    }

    /**
     * Protocol an action.
     * @param string The message to show in log
     * @return void
     */
    protected function _protocol($text)
    {
        $delta = microtime(true) - $this->_start;
        $this->Log(
            sprintf(
                '[%d:%02d] %s',
                $delta / 60,
                round($delta % 60),
                $text
            )
        );
    }

    /**
     * Set passive mode.
     * @param boolean
     * @return void
     */
    protected function _setPassiveMode($on = true)
    {
        $mnemo = ($on ? 'ON' : 'OFF');
        if (@ftp_pasv($this->_ftp, $on) === false) {
            $this->_failure("Failed to change PASV mode to '{$mnemo}'");
        }
        $this->_protocol("PASV mode turned '{$mnemo}'");
    }

    /**
     * Notify a log protocol about the next file processed.
     * @return void
     */
    protected function _notify()
    {
        $this->_filesProcessed += 1;
        if (($this->_filesProcessed % 100) == 0) {
            $this->_protocol("{$this->_filesProcessed} files/dirs processed");
        }
    }

}
