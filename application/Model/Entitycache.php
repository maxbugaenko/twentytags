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
 * it, please report this incident to the author by email: privacy@scraber
 *
 * @author Max Bugaenko <max@technoparkcorp.com>
 * @copyright Copyright (c) scraber.com, 2009
 * @version $Id$
 *
 */

/**
 * One entity record
 *
 * @package Model
 */
class Model_Entitycache extends FaZend_Db_Table_ActiveRow_entitycache {

    /**
     * Find entity record by it's name
     *
     * @param string Entity name
     * @return Model_Entity
     */
    public static function findByEntity ($entity) {
        try {
            $row = self::retrieve()
                ->where('entitycache.entity = ?', $entity)
                ->setRowClass('Model_Entitycache')
                ->fetchRow();
            return $row;
        } catch (Exception $e) {
            FaZend_Exception::raise('Model_Entitycache_EntityNotFound',
                "Entity with id '{$entity}' doesn't exist", 'Exception');
        }
    }


}