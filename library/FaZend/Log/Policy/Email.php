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
 * @version $Id: Email.php 2117 2010-08-25 13:53:40Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see FaZend_Log_Policy_Abstract
 */
require_once 'FaZend/Log/Policy/Abstract.php';

/**
 * Send long file by email
 *
 * @package Log
 * @see logg()
 */
class FaZend_Log_Policy_Email extends FaZend_Log_Policy_Abstract
{
    
    /**
     * Bytes in one unit
     */
    const UNIT_SIZE = 1024;
    
    /**
     * Suffix of the unit
     */
    const UNIT_SUFFIX = 'Kb';

    /**
     * List of available options
     *
     * @var array
     */
    protected $_options = array(
        // The email address to use for sending of log files. If you don't
        // set this parameter the policy will throw an exception during
        // bootstrap
        'toEmail' => null, 
        
        // The name of the email, it's an optional param
        'toName' => 'admin',
        
        // The length of log file which is allowed on the server. If the file
        // is longer than this specified length (in Kb), it will be sent
        // by email.
        'length' => 50,
        
        // Maximum duration in days to keep the file on the server, no matter
        // how long it is
        'age' => 5, 
        
        // The length of the log file, which could be sent by email. If the file
        // is longer, we WON'T send it, but will add a log line telling about
        // this critical situation.
        'maxLengthToSend' => 2048, 
        
        // Maximum length of file to be embedded inside email content, in Kb.
        // If the file is bigger, we will attach it to the email as MIME part
        // named as pathinfo() of the original log file
        'maxContentLength' => 300, 
    );

    /**
     * Run the policy
     *
     * @return void
     * @throws FaZend_Log_Policy_Email_Exception
     */
    protected function _run()
    {
        /**
         * If the email is not set
         */
        if (empty($this->_options['toEmail'])) {
            FaZend_Exception::raise(
                'FaZend_Log_Policy_Email_Exception',
                "[toEmail] is not configured in INI"
            );
        }

        // maybe the file is absent?
        if (!file_exists($this->_file)) {
            return;
        }
        
        $file = realpath($this->_file);

        /**
         * Here we find all reasons why this file should be sent
         * by email to admin
         */
        $reasons = array();
        if (@filesize($file) > $this->_options['length'] * self::UNIT_SIZE) {
            $reasons[] = _t(
                'Log file is %0.2f%s in length (over allowed %d%s)',
                filesize($file) / self::UNIT_SIZE,
                self::UNIT_SUFFIX,
                intval($this->_options['length']),
                self::UNIT_SUFFIX
            );
        }
        /**
         * @todo I don't know how to implement it properly. Now we end up
         * with an endless cycle, removing the file again and again and sending
         * empty emails to admin
         */
        // if (@filectime($file) > time() - $this->_options['age'] * 24*60*60) {
        //     $reasons[] = _t(
        //         'Log file %s is old enough - %ddays (older than %d)',
        //         $file,
        //         (@filectime($file) - time()) / (24*60*60),
        //         intval($this->_options['age'])
        //     );
        // }

        // if there are no reasons to truncate the file - exit
        if (!$reasons) {
            return;
        }

        /**
         * If the log file is too big..
         */
        if (@filesize($file) > $this->_options['maxLengthToSend'] * self::UNIT_SIZE) {
            logg(
                'File %s is too big (%d%s) to be sent by email (max %d%s allowed)',
                $file,
                @filesize($file) / self::UNIT_SIZE,
                self::UNIT_SUFFIX,
                $this->_options['maxLengthToSend'],
                self::UNIT_SUFFIX
            );
            return;
        }

        /**
         * Email the content to the admin
         */
        $sender = FaZend_Email::create('fazendForwardLog.tmpl')
            ->set('toEmail', $this->_options['toEmail'])
            ->set('toName', $this->_options['toName'])
            ->set(
                'subject', 
                _t('Latest php.error_log from %s', FaZend_Revision::getName())
            )
            ->set('file', $file)
            ->set('reasons', $reasons);

        /**
         * If the file is TOO big, we should send it as attachment, not
         * as email body
         */
        $content = @file_get_contents($file);
        if ($content === false) {
            FaZend_Exception::raise(
                'FaZend_Log_Policy_Email_Exception',
                "Failed to get content from '{$file}'"
            );
        }
        if (@filesize($file) > $this->_options['maxContentLength'] * self::UNIT_SIZE) {
            $sender->set('log', _t('See the attached file'));
            $mime = new Zend_Mime_Part($content);
            $mime->filename = pathinfo($file, PATHINFO_BASENAME);
            $mime->encoding = Zend_Mime::ENCODING_BASE64;
            $mime->type = Zend_Mime::TYPE_OCTETSTREAM;
            $mime->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $sender->attach($mime);
        } else {
            // get the content of the file
            $sender->set('log', $content);
        }
        // send email
        $sender->send();
        
        // truncate the original file
        $this->_truncate($file);
        
        // protocol this operation
        logg(
            'Log file %s was truncated (%0.2f%s) and sent by email to %s [%s]',
            $file,
            strlen($content) / self::UNIT_SIZE,
            self::UNIT_SUFFIX,
            $this->_options['toEmail'],
            implode(', ', $reasons)
        );
    }

}
