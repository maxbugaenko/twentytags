<?php
/**
 *
 * Copyright (c) 2009, twentytags.com
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of scraber.com. located at
 * www.scraber.com. If you received this code occacionally and without intent to use
 * it, please report this incident to the author by email: privacy@scraber
 *
 * @author Max Bugaenko <max.bugaenko@gmail.com>
 * @copyright Copyright (c) twentytags.com 2014
 * @version $Id$
 *
 */

/**
 * Google Alerts parser extended from Provider
 */
require_once APPLICATION_PATH . '/Model/GoogleApi/Google/Client.php';
require_once APPLICATION_PATH . '/Model/GoogleApi/Google/Service/YouTube.php';

class Model_Provider_YoutubeAlerts extends Model_Provider_Provider {

    public function parseAlerts() {
        echo "parsing videos for: {$this->entity->title}";
        $DEVELOPER_KEY = 'AIzaSyByZ6aJGhhdMYhqU94pEi32VRnxJaPgceo';
        $client = new Google_Client();
        $client->setDeveloperKey($DEVELOPER_KEY);
        $youtube = new Google_Service_YouTube($client);
        try {
            $searchResponse = $youtube->search->listSearch('id,snippet', array(
                'q' => $this->entity->title,
                'maxResults' => 10,
                'order' => "date",
                'regionCode' => "US"
            ));
            foreach ($searchResponse['items'] as $searchResult) {
                echo $title = $searchResult["snippet"]["title"];
                echo $link = "https://www.youtube.com/watch?v=".$searchResult["id"]["videoId"];
                $body = $searchResult["snippet"]["description"];
                echo $imageLink = $searchResult["snippet"]["thumbnails"]["high"]["url"];
                if ($imageLink) {
                    $imageFilename = Model_Static_Functions::saveImageFromUrl(ALERT_IMAGES_PATH, $imageLink);
                } else {
                    $imageFilename = "";
                }
                $hash = hash("md5", $link);
                try {
                    Model_Alert::findByHash($hash);
                    echo "Alert with such hash (by link) already exists\n";
                } catch (Model_Alert_NoSuchHashException $e) {
                    $alert = new Model_Alert();
                    $alert->entity = $this->entity;
                    $alert->type = "video";
                    $alert->title = $title;
                    $alert->source = "youtube";
                    $alert->link = $link;
                    $alert->body = $body;
                    $alert->picture = $imageFilename;
                    $alert->hash = $hash;
                    $alert->save();
                }
            }
        } catch (Google_ServiceException $e) {
            echo "Google exception {$e->getMessage()}";
        } catch (Google_Exception $e) {
            echo "Google exception {$e->getMessage()}";
        }
    }

}
