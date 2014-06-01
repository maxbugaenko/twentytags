<?php
/**
 *
 * Copyright (c) 2009, scraber.com
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of scraber.com. located at
 * www.scraber.com. If you received this code occacionally and without intent to use
 * it, please report this incident to the author by email: privacy@scraber.com
 *
 * @author Max Bugaenko <max@technoparkcorp.com>
 * @copyright Copyright (c) scraber.com, 2009
 * @version $Id$
 *
 */

/**
 * Cache Entities
 */
class CacheEntity extends FaZend_Cli_Abstract {
    /**
     * To be executed from command line
     *
     * @return int exit code
     */
    public function execute() {
        Model_Static_Functions::checkZombie("CacheEntity.flag");
        $ents = Model_Entity::retrieveExpired(50);
        echo "Caching entities (".count($ents).")\n";
        if (count($ents) > 0) {
            foreach ($ents as $entity) {
                echo "caching ".$entity->title."\n";
                Model_Static_Functions::cacheEntity($entity);
                $entity->cached = new Zend_Db_Expr('NOW()');
                $tit = $entity->title;
                $entity->title = Model_Static_Functions::deleteSymbols($tit);
                $entity->save();
            }
        } else {
            echo "nothing to cache\n";
        }
        Model_Static_Functions::killZombie("CacheEntity.flag");
    }
}