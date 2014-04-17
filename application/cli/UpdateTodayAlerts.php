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
class UpdateTodayAlerts extends FaZend_Cli_Abstract {
    /**
     * Cron that updates alerts for each entity
     * from all available sources
     * @return int exit code
     */
    public function execute() {
        Model_Static_Functions::checkZombie("UpdateTodayAlerts.flag");
        $entities = Model_Entity::retrieveEntitiesAlertsForTodayUpdate(20);
        if (count($entities) == 0) {
            echo "Nothing to update\n";
            Model_Static_Functions::killZombie("UpdateTodayAlerts.flag");
            exit;
        }
        foreach ($entities as $entity) {
            echo $entity->title." has ";
            $alertCount = Model_Alert::retrieveTodayCountByEntity($entity);
            echo $alertCount." alerts\n";
            $entity->today = $alertCount;
            $entity->todayupdated = new Zend_Db_Expr("NOW()");
            $entity->save();
        }
        Model_Static_Functions::killZombie("UpdateTodayAlerts.flag");
    }
}