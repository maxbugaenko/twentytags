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
class AddAlerts extends FaZend_Cli_Abstract {
    /**
     * Cron that updates alerts for each entity
     * from all available sources
     * @return int exit code
     */
    public function execute() {
        Model_Static_Functions::checkZombie("AddAlerts.flag");
        $entities = Model_Entity::retrieveEntitiesToAdd(20);
        if (count($entities) == 0) {
            echo "Nothing to update\n";
            Model_Static_Functions::killZombie("AddAlerts.flag");
            exit;
        }
        foreach ($entities as $entity) {
            Model_GAlerts_GAlertsManager::createAlert($entity->title, "news");
            Model_GAlerts_GAlertsManager::createAlert($entity->title, "videos");
            $feeds = Model_GAlerts_GAlertsManager::retrieveFeedsByTerm($entity->title);
            foreach ($feeds as $feed => $url) {
                $source = new Model_Source();
                $source->entity = $entity;
                $source->provider = "google";
                $source->source = $url;
                $source->save();
            }
            $entity->status = 1;
            $entity->save();
            exit;
        }
        Model_Static_Functions::killZombie("AddAlerts.flag");
    }
}