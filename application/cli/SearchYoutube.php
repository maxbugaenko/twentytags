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
class SearchYoutube extends FaZend_Cli_Abstract {
    /**
     * To be executed from command line
     * @return int exit code
     */
    public function execute() {
        require_once APPLICATION_PATH . '/Model/GoogleApi/Google/Client.php';
        require_once APPLICATION_PATH . '/Model/GoogleApi/Google/Service/YouTube.php';
        $DEVELOPER_KEY = 'AIzaSyByZ6aJGhhdMYhqU94pEi32VRnxJaPgceo';
        $client = new Google_Client();
        $client->setDeveloperKey($DEVELOPER_KEY);
        $youtube = new Google_Service_YouTube($client);
        try {
            // Call the search.list method to retrieve results matching the specified
            // query term.
            $searchResponse = $youtube->search->listSearch('id,snippet', array(
                'q' => $this->_get("keyword"),
                'maxResults' => 10,
                'order' => "date"
            ));
//            print_r($searchResponse['items']);
//            exit;
            foreach ($searchResponse['items'] as $searchResult) {
                echo $searchResult["snippet"]["title"]."\n";
                echo "https://www.youtube.com/watch?v=".$searchResult["id"]["videoId"]."\n";
                echo $searchResult["snippet"]["publishedAt"]."\n\n";
            }
        } catch (Google_ServiceException $e) {
            $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
            $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        }
    }
}