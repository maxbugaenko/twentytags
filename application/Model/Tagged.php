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
class Model_Tagged extends FaZend_Db_Table_ActiveRow_tagged {

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author fatboy
	 **/
	public static function create(Model_Entity $entity, Model_Tag $tag) {
	    $cl = new Model_Tagged();
	    $cl->entity = $entity;
	    $cl->tag = $tag;
	    try {
	        $cl->save();
    	    return $cl;
        } catch (Exception $e) {
    	    FaZend_Exception::raise('Model_Tagged_CouldNotSave', 
    		"Can't tag entity '{$entity}' with '{$tag->value}'", 'Exception');
        }
	}


    /**
    * Retrieves props by ent
    * @param Model_Entity entity
    * @return selected array
    */
	public static function retrieveByEntityAndTag (Model_Entity $entity, Model_Tag $tag) {
	    try {
		    $res = self::retrieve()
    			->where('tagged.entity = ?', $entity)
    			->where('tagged.tag = ?', $tag)
    			->setRowClass('Model_Tagged')
    			->fetchRow();
    		return $res;
	    } catch (Model_Tagged_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_Tagged_NoSuchTaggedEntity', 
    		"No entity tagged with '{$tag->value}'", 'Exception');
	    }
    	
	}

    /**
     * undocumented function
     *
     * @return void
     * @author fatboy
     **/
    public static function clearByEntity(Model_Entity $entity){
	    self::retrieve()
			->where('tagged.entity = ?', $entity)
			->delete();
    }

    /**
    * Retrieves props by ent
    * @param Model_Entity entity
    * @return selected array
    */
	public static function getCountByEntity (Model_Entity $entity) {
	    $res = self::retrieve()
			->where('tagged.entity = ?', $entity)
			->setRowClass('Model_Tagged')
			->fetchAll();
		return count($res);
	}


}
