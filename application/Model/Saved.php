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
 * Entities saved by user
 *
 * @package Model
 */
class Model_Saved extends FaZend_Db_Table_ActiveRow_saved {
    /**
     * Retrieves saved entity by user
     */
    public static function retrieveByEntityAndUser(Model_Entity $entity, Model_User $user) {
        return self::retrieve()
            ->where('saved.user = ?', $user)
            ->where('saved.entity = ?', $entity)
            ->setRowClass('Model_Saved')
            ->fetchRow();
    }

}
