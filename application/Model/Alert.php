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
 * One entity record
 *
 * @package Model
 */
class Model_Alert extends FaZend_Db_Table_ActiveRow_alert {

    /**
     * Create one Alert record
     */
    public static function create ($entity, $type, $title, $source, $link, $body, $picture) {
        $alert = new Model_Alert();
        $alert->entity = $entity;
        $alert->type = $type;
        $alert->title = $title;
        $alert->source = $source;
        $alert->link = $link;
        $alert->body = $body;
        $filename = basename($picture);
        $pic = file_get_contents($picture);
        $res = file_put_contents("/code/twentytags/public/images/alerts/".$filename, $pic);
        $alert->picture = $filename;
        $alert->save();
        return $alert;
    }
    /**
     * Retrieves all entities stored in the database
     */
    public static function retrieveAll () {
        return self::retrieve()
            ->setRowClass('Model_Entity')
            ->fetchAll();
    }
    /**
     * Retrieves options by name
     */
    public static function retrieveByEntity (Model_Entity $entity) {
        return self::retrieve()
            ->where('alert.entity = ?', $entity)
            ->order('alert.added desc')
            ->setRowClass('Model_Alert')
            ->fetchAll();
    }

    /**
     * Retrieves alerts count by entity
     */
    public static function retrieveCountByEntity (Model_Entity $entity) {
        return count(self::retrieve()
            ->where('alert.entity = ?', $entity)
            ->setRowClass('Model_Alert')
            ->fetchAll());
    }

    /**
     * Retrieves alerts count by entity
     */
    public static function retrieveTodayCountByEntity (Model_Entity $entity) {
        return count(self::retrieve()
            ->where('alert.entity = ?', $entity)
            ->where('alert.added > ?', new Zend_Db_Expr('CURRENT_DATE()'))
            ->where("alert.added < ?", new Zend_Db_Expr("CURRENT_DATE() + INTERVAL 1 DAY"))
            ->setRowClass('Model_Alert')
            ->fetchAll());
    }
    /**
     * Retrieves distinct dates of all alerts
     * of a specific entity
     */
    public static function retrieveDistinctDatesByEntity(Model_Entity $entity) {
        return self::retrieve(false)
            ->from ("alert", array("dates" => new Zend_Db_Expr("distinct(date_format(added, '%Y-%m-%d'))")))
            ->where('alert.entity = ?', $entity)
            ->setRowClass('Model_Alert')
            ->fetchAll();
    }
    /**
     * Retrieves distinct dates of all alerts
     * of a specific entity
     */
    public static function retrieveByEntityAndDate(Model_Entity $entity, $date) {
        return self::retrieve()
            ->where('alert.added > ?', new Zend_Db_Expr('DATE("'.$date.'")'))
            ->where("alert.added < ?", new Zend_Db_Expr('DATE("'.$date.'") + INTERVAL 1 DAY'))
            ->where('alert.entity = ?', $entity)
            ->setRowClass('Model_Alert')
            ->fetchAll();
    }
    /**
     * Finds alert by hash
     */
    public static function findByHash ($hash) {
        try {
            $row = self::retrieve()
                ->where('alert.hash= ?', $hash)
                ->setRowClass('Model_Alert')
                ->fetchRow();
            return $row;
        } catch (Model_Alert_NotFoundException $e) {
            FaZend_Exception::raise('Model_Alert_NoSuchHashException',
                "No such hash", 'Exception');
        }
    }

}
