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
 * @version $Id: email.php 2143 2010-09-01 07:36:28Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Resource for initializing FaZend_Email
 *
 * @uses Zend_Application_Resource_Base
 * @package Application
 * @subpackage Resource
 * @see FaZend_Email
 */
class FaZend_Application_Resource_fz_email extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * Initializes the resource
     *
     * @return void
     * @see Zend_Application_Resource_Resource::init()
     */
    public function init()
    {
        /**
         * @see FaZend_Email
         */
        require_once 'FaZend/Email.php';
        // configure default email message
        $email = new FaZend_Email();

        // make sure view is initialized
        $this->_bootstrap->bootstrap('fz_view');
        $email->setView($this->_bootstrap->getResource('view'));
        $charset = 'utf-8';
        foreach ($this->getOptions() as $option=>$value) {
            switch (strtolower($option)) {
                case 'send':
                    $email->setIsSending((bool)$value);
                    break;
                    
                case 'encoding':
                    $charset = $value;
                    break;
                    
                case 'folders':
                    $email->setFolders($value);
                    break;
                    
                case 'folder':
                    $email->setFolders(array($value));
                    break;

                case 'notifier':
                    $email->set('fromEmail', $value['email']);
                    $email->set('fromName', $value['name']);
                    break;
                    
                case 'manager':
                    $email->set('toEmail', $value['email']);
                    $email->set('toName', $value['name']);
                    break;
                    
                case 'transport':
                    $this->_setDefaultTransport($value);
                    break;
                    
                default:
                    // ignore this options since it's unknown
            }
        }
        
        $email->setMailer(new Zend_Mail($charset));
        FaZend_Email::setDefaultEmail($email);
    }

    /**
     * Compute transport and return it
     *
     * @param array List of options
     * @return void
     * @see init()
     * @throws FaZend_Application_Resource_Fazend_Email_TransportUnknownException
     */
    protected function _setDefaultTransport($option) 
    {
        switch ($option['name']) {
            case 'Zend_Mail_Transport_Smtp':
                Zend_Mail::setDefaultTransport(
                    new Zend_Mail_Transport_Smtp($option['host'], $option['params'])
                );
                break;
                
            case 'Zend_Mail_Transport_Sendmail':
                Zend_Mail::setDefaultTransport(
                    new Zend_Mail_Transport_Sendmail()
                );
                break;
                
            default:
                FaZend_Exception::raise(
                    'FaZend_Application_Resource_Fazend_Email_TransportUnknownException', 
                    "Transport '{$option['name']}' is unknown"
                );
        }
    }
    
}
