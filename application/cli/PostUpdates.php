<?php
/**
 *
 * Copyright (c) 2009, twentytags.com
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of twentytags.com. located at
 * www.twentytags.com. If you received this code occasionally and without intent to use
 * it, please report this incident to the author by email: privacy@twentytags.com
 *
 * @author Max Bugaenko <max.bugaenko@gmail.com>
 * @copyright Copyright (c) twentytags.com, 2014
 * @version $Id$
 */

/**
 * Cache Entities
 */
require_once('Model/Facebook/facebook.php');
class PostUpdates extends FaZend_Cli_Abstract {
    /**
     * Cron that updates alerts for each entity
     * from all available sources
     * @return int exit code
     */
    public function execute() {
        $users = Model_User::retrieveAll();
        foreach ($users as $user) {
            $entities = Model_Entity::retrieveTodayUpdatedEntityByUser($user);
            $count = count($entities);
            $app_id = "706201236078444";
            $app_secret = "fabe992657dcffefd59099502cdf0ce9";
            $config = array(
                'appId' => $app_id,
                'secret' => $app_secret
            );
            $app_access_token = $app_id . '|' . $app_secret;
            $facebook = new Facebook($config);
            $response = $facebook->api( '/100000002697570/notifications', 'POST', array(
                'template' => 'You have received a new message.',
                'href' => '/following',
                'access_token' => $app_access_token
            ) );
            print_r($response);
       }
    }
}