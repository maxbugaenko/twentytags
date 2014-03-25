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
 * One source record
 *
 * @package Model
 */
class Model_Source extends FaZend_Db_Table_ActiveRow_source {

    /**
     * Retrieves alerts count by entity
     */
    public static function retrieveByProviderAndEntity ($provider, Model_Entity $entity) {
        $row = self::retrieve()
            ->where('source.provider = ?', $provider)
            ->where('source.entity = ?', $entity)
            ->setRowClass('Model_Entity')
            ->fetchAll();
        return $row;
    }

}
