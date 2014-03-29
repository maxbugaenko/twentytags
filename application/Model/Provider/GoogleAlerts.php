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
class Model_Provider_GoogleAlerts extends Model_Provider_Provider {

    public function parseAlerts() {
        foreach ($this->sources as $source) {
            echo "parsing feed from: {$source->source}";
            $feedXML = simplexml_load_file($source->source);
            foreach($feedXML->entry as $k => $data){
                $title = strip_tags($data->title);
                echo "\n\n";
                $title = $title."\n";
                $link = $link = urldecode(substr($data->link->attributes()->href, strpos($data->link->attributes()->href, "q=")+2));
                $hash = hash("md5", $link);
                $body = strip_tags($data->content);
                echo "link: " . $link ."\n";
                echo "hash: ".$hash."\n";
                echo "content: " . $body ."\n";
                $hostData = parse_url($link);
                print_r($hostData);
                if (strpos($link, "youtube.com")) {
                    $webSource = "youtube";
                    $alertType = "video";
                    $params = explode("&", $hostData["query"]);
                    list($v, $videoId) = explode("=", $params[0]);
                    echo "video ID: ".$videoId."\n";
                    echo "thumb link: ".$imageLink = "http://img.youtube.com/vi/".$videoId."/0.jpg";
                    $imageFilename = Model_Static_Functions::saveImageFromUrl(ALERT_IMAGES_PATH, $imageLink);
                    echo "source: ".$webSource."\n";
                } else {
                    $webSource = $hostData["host"];
                    $alertType = "text";
                    $imageLink = Model_Static_Functions::getOgImage($link);
                    echo "image: ".$imageLink."\n";
                    if ($imageLink) {
                        $imageFilename = Model_Static_Functions::saveImageFromUrl(ALERT_IMAGES_PATH, $imageLink);
                    } else {
                        $imageFilename = "";
                    }
                    echo "image filename: ".$imageFilename."\n";
                    echo "source: ".$webSource."\n";
                }
                try {
                    Model_Alert::findByHash($hash);
                    echo "\nAlert with such hash (by link) already exists\n";
                } catch (Model_Alert_NoSuchHashException $e) {
                    $alert = new Model_Alert();
                    $alert->entity = $this->entity;
                    $alert->type = $alertType;
                    $alert->title = $title;
                    $alert->source = $webSource;
                    $alert->link = $link;
                    $alert->body = $body;
                    $alert->picture = $imageFilename;
                    $alert->hash = $hash;
                    $alert->save();
                }
            }
        }
    }

}
