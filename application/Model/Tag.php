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
 * One Link record
 * @package Model
 */
class Model_Tag  extends FaZend_Db_Table_ActiveRow_tag {

	/**
	 * Retrieves all entities stored in the database 
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveAll () {
		return self::retrieve()
		    ->order('tag.value desc')
		    ->limit(100)
			->setRowClass('Model_Tag')
			->fetchAll();
	}

	/**
	 * Retrieves all entities stored in the database 
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveDistinct () {
		return self::retrieve()
		    ->group('tag.name')
			->setRowClass('Model_Tag')
			->fetchAll();
	}


	/**
	 * Retrieves all entities stored in the database 
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveTagValues ($tagname) {
		return self::retrieve()
		    ->where('tag.name = ?', $tagname)		
			->setRowClass('Model_Tag')
			->fetchAll();
	}



	/**
	 * Retrieves all entities stored in the database 
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveByName ($name) {
		return self::retrieve()
		    ->where('tag.name = ?', $name)
			->setRowClass('Model_Tag')
			->fetchAll();
	}


	/**
	 * Retrieves all entities stored in the database 
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function getValueByName ($name) {
		$res = self::retrieve()
		    ->where('tag.name = ?', $name)
			->setRowClass('Model_Tag')
			->fetchRow();
		return $res->value;
	}



	/**
	 * Retrieves class by class name
	 * @return Model_Option class
	*/
	public static function findByTagName($tagname) {
	    try {
		    $row = self::retrieve()
			    ->where('tag.name = ?', $tagname)
			    ->setRowClass('Model_Tag')
			    ->fetchRow();
    	    return $row;
		} catch (Model_Tag_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_Tag_TagnameNotFound', 
    		"Tag '{$tagname}' doesn't exist", 'Exception');
	    }
	}


	/**
	 * Retrieves class by class name
	 * @return Model_Option class
	*/
	public static function getCount(Model_Tag $tag) {
	    $row = self::retrieve()
	        ->join('tagged', 'tagged.tag = tag.id', array())
	        ->join('entity', 'entity.id = tagged.entity', array())
		    ->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
		    ->where('tag.id = ?', $tag)
		    ->setRowClass('Model_Tag')
		    ->fetchAll();
		return count($row);
	}



	/**
	 * Retrieves class by class name
	 * @return Model_Option class
	*/
	public static function findByValue($value) {
	    try {
		    $row = self::retrieve()
			    ->where('tag.value = ?', trim($value))
			    ->setRowClass('Model_Tag')
			    ->fetchRow();
    	    return $row;
		} catch (Model_Tag_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_Tag_TagByValueNotFound', 
    		"Tag by '{$value}' doesn't exist", 'Exception');
	    }
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author fatboy
	 **/
	public static function add($value, $forceAdd = true) {
	    try {
	        $t = Model_Tag::findByValue(trim($value));
	        return $t;
        } catch (Model_Tag_TagByValueNotFound $e) {
            if ($forceAdd){
    	        $t = new Model_Tag();
    	        $t->value = trim($value);
    	        $t->original = "0";
    	        $t->save();
    	        return $t;
    	    } 
        }
	}
	
	
	/**
	 * Find entity record by mask passed from Javascript
	 * for not logged user
	 * @param string Mask (a part or a full string which is a mask of entity title)
	 * @param Model_Class class to fetch rows by
	 * @return array Array of entity titles if found
	*/
	public static function retrieveByMask($mask, $tagsOnly = false) {
	    if (!$tagsOnly) {
            $db = Zend_Db_Table::getDefaultAdapter();
        
             $tagssql = $db->select()
                 ->from('tag', array('title'=>'tag.value', 'original'=>'tag.value'))
                 ->where('tag.value LIKE ?', '%'.$mask.'%');

             $titlesql = $db->select()
                 ->from('entity', array('title'=>'entity.title', 'original'=>'entity.original'))
                 ->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
                 ->where('entity.title LIKE "%'.$mask.'%" or entity.original LIKE "%'.$mask.'%"');
             
             $db = Zend_Db_Table::getDefaultAdapter();
             $select = $db->select()->union(array($tagssql, $titlesql))->limit(3);
             $stmt = $db->query($select);
             $result = $stmt->fetchAll();
        } else {
            $result = self::retrieve()
                ->where('tag.value LIKE ?', '%'.$mask.'%')
                ->setRowClass('Model_Tag')
                ->limit(5)
                ->fetchAll();
        }
        return $result;
	}
	
	/**
    * Retrieves props by ent
    * @param Model_Tagged entity
    * @return selected array
    */
	public static function retrieveTaggedTagsByEntity (Model_Entity $entity) {
		return self::retrieve(false)
		    ->from('tag', array('tag.id', 'tag.value'))
		    ->join ('tagged', 'tagged.tag = tag.id', array())
			->where('tagged.entity = ?', $entity)
			->group('tag.id')
			->setRowClass('Model_Tag')
			->fetchAll();
	}
    
    /**
     * undocumented function
     *
     * @return void
     * @author fatboy
     **/
    public static function retrieveTop($top = 5){
        return self::retrieve(false) 
            ->from('tag', array('tag.id', 'tag.value', 'tag.picture', 'matches' => new Zend_Db_Expr('count(tag.id)')))
            ->join('tagged', 'tagged.tag=tag.id', array())
            ->join('entity', 'entity.id = tagged.entity and entity.status = 1', array())
            ->group('tag.id')
            ->order('matches desc')
            ->limit($top)
            ->setRowClass('Model_Tag')
            ->fetchAll();
        
    }
    
    
    /**
     * undocumented function
     *
     * @return void
     * @author fatboy
     **/
    public static function hasPicture(Model_Tag $tag){
        if ($tag->picture)
            return true;
        else 
            return false;
    }
    
	
}

