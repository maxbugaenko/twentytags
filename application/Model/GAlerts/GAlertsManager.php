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

class Model_GAlerts_GAlertsManager {

    public static function createAlert($keyword, $type) {
        $ga = new Model_GAlerts_GAlerts("gozman.mark2014@gmail.com", "ghbdtn67");
        $ga->create($keyword, "es", "happens", $type, "best", "feed");
        $list = $ga->getList();
    }
    public static function retrieveFeedsByTerm($term) {
        $ga = new Model_GAlerts_GAlerts("gozman.mark2014@gmail.com", "ghbdtn67");
        $list = $ga->getList();
        foreach ($list as $item) {
            if ($item["term"] == $term) {
                $feeds[] = $item["url"];
            }
        }
        return $feeds;
    }
}
?>