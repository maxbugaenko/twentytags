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
class Model_Entity extends FaZend_Db_Table_ActiveRow_entity {

	const ENTITY_PENDING = 0;
	const ENTITY_APPROVED = 1;
	const ENTITY_REJECTED = 2;
	/**
	 * Create one Entity record
	 *
	 * @param class Model_Class
	 * @param string title
	 * @param string name
	 * @return Model_Entity
	*/
	public static function create ($title, $description, $link, $picture) {
	    validate()
	        ->notEmpty($title, array(), "Enter title")
            ->notEmpty($description, array(), "Enter description")
            ->notEmpty($picture, array(), "Enter picture URL");
		$entity = new Model_Entity();
		$entity->title = $title;
        $entity->link = $link;
		$entity->description = $description;
        $filename = Model_Static_Functions::saveImageFromUrl(ENTITY_IMAGES_PATH, $picture);
		$entity->picture = $filename;
		$entity->save();
        Model_GAlerts_GAlertsManager::createAlert($title, "news");
        Model_GAlerts_GAlertsManager::createAlert($title, "videos");
        $feeds = Model_GAlerts_GAlertsManager::retrieveFeedsByTerm($title);
        foreach ($feeds as $feed => $url) {
            $source = new Model_Source();
            $source->entity = $entity;
            $source->provider = "google";
            $source->source = $url;
            $source->save();
        }
		return $entity;
	}

	/**
	 * Updates one Entity record
	 *
	 * @param class Model_Class
	 * @param string title
	 * @param string name
	 * @return Model_Entity
	*/
	public static function update ($title, $description, $entity) {
	    validate()
	        ->notEmpty($title, array(), 'Введите название')
            ->notEmpty($description, array(), 'Введите описание');

		$entity->title = $title;
		$entity->description = $description;
		$entity->save();
	}

	/**
	 * Updates entity's title
	 *
	 * @param entity Model_Entity
	 * @param string title
	 * @return Model_Entity
	*/
	public static function updatetitle (Model_Entity $entity, $title) {
	    validate()
	        ->notEmpty($title, array(), 'Введите название');

		$entity->title = $title;
		$entity->save();
	}

	/**
	 * Updates entity's title
	 *
	 * @param entity Model_Entity
	 * @param string title
	 * @return Model_Entity
	*/
	public static function updateoriginal (Model_Entity $entity, $original) {
	    validate()
	        ->notEmpty($original, array(), 'Введите название оригинала');

		$entity->original = $original;
		$entity->save();
	}

	/**
	 * Updates entity's title
	 *
	 * @param entity Model_Entity
	 * @param string title
	 * @return Model_Entity
	*/
	public static function updatedescription (Model_Entity $entity, $description) {
	    validate()
	        ->notEmpty($description, array(), 'Введите название');

		$entity->description = $description;
		$entity->save();
	}



	/**
	 * Add new favorite to profile
	 *
	 * @param class Model_Class
	 * @param string title
	 * @param string name
	 * @return Model_Entity
	*/
	public static function addnew ($title, Model_Class $class) {
	    validate()
	        ->notEmpty($title, _t('Enter title'));
	}



	/**
	 * Find entity record by it's name
	 *
	 * @param string Entity name
	 * @return Model_Entity
	*/
	public static function findByName ($text) {
	    try {
    		$row = self::retrieve()
    			->where('entity.title = ?', $text)
    			->orwhere ('entity.original = ?', $text)
    			->setRowClass('Model_Entity')
    			->fetchRow();
    		return $row;
    	} catch (Model_Entity_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_Entity_NoEntityWithSuchName', 
    		"Entity with name '{$text}' doesn't exist", 'Exception');
	    }
	}


	/**
	 * Find entity record by it's name
	 *
	 * @param string Entity name
	 * @return Model_Entity
	*/
	public static function getAd () {
	    try {
    		$row = self::retrieve()
                ->where('(entity.class = "'. Model_Class::findByClassName('Ad').'" or entity.class = "'. Model_Class::findByClassName('Page').'")')
    			->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
				->order(new Zend_Db_Expr('RAND()'))
    			->limit(1)
    			->setRowClass('Model_Entity')
    			->fetchRow();
    		return $row;
    	} catch (Model_Entity_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_Entity_NoAdFound',
    		"There is no ad", 'Exception');
        }
	}



	/**
	 * Retrieves options by name
	 * @param string name
	 *
	 * @return array Array of all options
	*/
	public static function retrieveOptionsByName ($name) {
		return self::retrieve(false)
			->from('entity', array('entity.id', 'entity.title'))
			->joinLeft('alias', 'alias.entity = entity.id', array())
			->where('entity.title like ?', '%'.$name.'%')
			->orWhere('alias.name like ?', '%'.$name.'%')
			->orWhere('soundex(entity.title) = soundex("'.$name.'")')
			->orWhere('soundex(alias.name) = soundex("'.$name.'")')
			->group('entity.id')
			->setRowClass('Model_Entity')
			->fetchAll();
	}

	/**
	 * Checks if specified entity is in favorite list
	 * (meaning that it's active)
	 *
	 * @param Model_Entity entity record
	 * @return boolean True/False
	*/
	public static function isFavorite (Model_Entity $entity) {
		try {
			$row = self::retrieve()
				->join('favorite', 'favorite.entity = entity.id', array())
				->where('favorite.user = ?', Model_User::me())
				->where('favorite.entity = ?', $entity)
				->setRowClass('Model_Entity')
				->fetchRow();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}



	/**
	 * Checks if specified entity is in favorite list
	 * (meaning that it's active)
	 *
	 * @param Model_Entity entity record
	 * @return boolean True/False
	*/
	public static function isSaved (Model_Entity $entity) {
		try {
			$row = self::retrieve()
				->join('saved', 'saved.entity = entity.id', array())
				->where('saved.user = ?', Model_User::me())
				->where('saved.entity = ?', $entity)
				->setRowClass('Model_Entity')
				->fetchRow();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Find entity record by mask passed from Javascript
	 * for logged user
	 * @param string Mask (a part or a full string which is a mask of entity title)
	 * @param Model_Class class to fetch rows by
	 * @return array Array of entity titles if found
	*/
	public static function retrieveByMaskNotLoggedLogged($mask, Model_Class $class = null) {
	    if ($class)
    		return self::retrieve(false)
    			->from('entity', 'title')
    			->joinLeft('favorite', 'favorite.entity = entity.id and favorite.user = '.Model_User::me().' and favorite.active = 1', array())
    			->joinLeft('hidden', 'hidden.entity = entity.id and hidden.user = '.Model_User::me(), array())
    			->where('entity.title LIKE ?', '%'.$mask.'%')
    			->where('entity.class = ?', $class)
    			->where('favorite.id is null')
    			->where('hidden.id is null')
    			->limit(5)
    			->setRowClass('Model_Entity')
    			->fetchAll();
    	else
    		return self::retrieve(false)
    			->from('entity', 'title')
    			->joinLeft('favorite', 'favorite.entity = entity.id and favorite.user = '.Model_User::me().' and favorite.active = 1', array())
    			->joinLeft('hidden', 'hidden.entity = entity.id and hidden.user = '.Model_User::me(), array())
    			->where('entity.title LIKE ?', '%'.$mask.'%')
    			->where('favorite.id is null')
    			->where('hidden.id is null')
    			->limit(5)
    			->setRowClass('Model_Entity')
    			->fetchAll();
	}

	/**
	 * Find entity record by mask passed from Javascript
	 * for not logged user
	 * @param string Mask (a part or a full string which is a mask of entity title)
	 * @param Model_Class class to fetch rows by
	 * @return array Array of entity titles if found
	*/
	public static function retrieveByMaskNotLoggedLoggedNotLogged($mask) {
		return self::retrieve(false)
			->from('entity', 'title')
			->where('entity.title LIKE ?', '%'.$mask.'%')
			->limit(5)
			->setRowClass('Model_Entity')
			->fetchAll();
	}


	/**
	 * Hide entity from search results
	 * @return void
	*/
	public function hide() {
		$hidden = new Model_Hidden();
		$hidden->entity = $this;
		$hidden->user = Model_User::me();
		try {
			$hidden->save();
		} catch (Exception $e) {
		}

	}


	/**
	 * Show entity in search results
	 *
	 * @return void
	*/
	public function show() {
		$hidden = Model_Hidden::findByEntity($this);
		$hidden->delete();
	}


	/**
	 * Retrieves advices based on specific entity for logged user
	 * @param Model_Entity entity
	 *
	 * @return array Array of entities to advise
	*/
	public static function retrieveAdvicesByEntityLogged (Model_Entity $entity) {
		return self::retrieve(false)
			->from('entity', array('entity.*',
				'rate' => new Zend_Db_Expr('sum(relatedusers.commonfavorites)')))
			->join('relatedentities', '(relatedentities.id = entity.id and relatedentities.rightId = '.$entity.')', array())
			->join('favorite', '(favorite.entity = entity.id and favorite.user != '.Model_User::me().' and favorite.active = 1)', array())
			->joinLeft(array('myFavorite' => 'favorite'), 'myFavorite.entity = entity.id  and myFavorite.user = '.Model_User::me(), array())
			->join('relatedusers', 'relatedusers.leftuser = favorite.user', array())
			->joinLeft('hidden', 'hidden.entity = entity.id', array())
			->where('hidden.id is null')
			->where('myFavorite.id IS NULL')
//			->group('entity.class')
			->order('rate DESC')
			->limit(6)
			->setRowClass('Model_Entity')
			->fetchAll();
	}


    	/**
    	 * Retrieves advices based on specific entity for logged user
    	 * @param Model_Entity entity
    	 *
    	 * @return array Array of entities to advise
    	*/
    	public static function retrieveTagged (Model_Entity $entity) {
    			$sql =
    			    "SELECT entity.id, count(DISTINCT similar.tag) as shared_tags FROM entity INNER JOIN ".
                    " ( tagged AS this_entity INNER JOIN tagged AS similar USING (tag))".
                    " ON similar.entity = entity.id".
                " WHERE this_entity.entity=".$entity.
                " AND entity.id != this_entity.entity".
                " AND entity.status = ".Model_Entity::ENTITY_APPROVED.
                " AND entity.class = ".Model_Class::findByClassName('Video').
                " GROUP BY entity.id".
                " ORDER BY shared_tags DESC LIMIT 10";

                $db = Zend_Db_Table::getDefaultAdapter();

                try {
                    $stmt = $db->query($sql);
                    $res = $stmt->fetchAll();

                    foreach ($res as $r) {
                        $advices[] = new Model_Entity((int)$r['id']);
                    }
                } catch(PDOException $e) {
                        echo $this->formatResult($e);
                }
                return $advices;

    	}

	/**
	 * Retrieves advices based on specific entity AND class for logged user
	 * @param Model_Entity entity
	 * @param Model_Entity entity that is excluded from results
	 *
	 * @return array Array of entities to advise
	*/
	public static function retrieveAdvicesByEntityAndClassLogged (Model_Entity $entity, Model_Entity $exclude) {
		return self::retrieve(false)
			->from('entity', array('entity.*',
				'rate' => new Zend_Db_Expr('sum(relatedusers.commonfavorites)')))
			->join('relatedentities', '(relatedentities.id = entity.id and relatedentities.rightId = '.$entity.')', array())
			->join('favorite', '(favorite.entity = entity.id and favorite.user != '.Model_User::me().' and favorite.active = 1)', array())
			->joinLeft(array('myFavorite' => 'favorite'), 'myFavorite.entity = entity.id  and myFavorite.user = '.Model_User::me(), array())
			->join('relatedusers', 'relatedusers.leftuser = favorite.user', array())
			->joinLeft('hidden', 'hidden.entity = entity.id', array())
			->where('entity.class = ?', $entity->class)
			->where('entity.id != ?', $exclude)
			->where('hidden.id is null')
			->where('myFavorite.id IS NULL')
			->group('entity.id')
			->order('rate DESC')
			->limit(7)
			->setRowClass('Model_Entity')
			->fetchAll();
	}




	/**
	 * Retrieves advices based on specific entity for not logged user
	 * @param Model_Entity entity
	 *
	 * @return array Array of entities to advise
	*/
	public static function retrieveAdvicesByEntityNotLogged (Model_Entity $entity) {
		return FaZend_Db_ActiveTable_relatedentities::retrieve(false)
			->from('relatedentities', array(
				'id'=>'entity.id',
				'title'=>'entity.title',
				'photo'=>'entity.photo',
				'class'=>'entity.class'))
			->join('entity', 'entity.id = relatedentities.rightId', array())
			->where('relatedentities.id = ?', $entity)
//			->group('class')
			->order('matches DESC')
			->limit(6)
			->setRowClass('Model_Entity')
			->fetchAll();
	}

	/**
	 * Retrieves 5 random entities
	 * on not logged user
	 *
	 * @return array Array of two movies
	*/
	public static function retrieveRandomNotLogged () {
		return self::retrieve()
			->group('class')
			->order('rand()')
			->limit(4)
			->setRowClass('Model_Entity')
			->fetchAll();
	}

	/**
	 * Retrieves 5 random entities
	 * on not logged user
	 *
	 * @return array Array of two movies
	*/
	public static function retrieveTop24 () {
		return self::retrieve()
		    ->join('top', 'top.entity = entity.id', array())
			->where('entity.status = ?', self::ENTITY_APPROVED)
			->order('top.views24 desc')
			->limit(50)
			->setRowClass('Model_Entity')
			->fetchAll();
	}

	/**
	 * Retrieves 5 random entities
	 * on not logged user
	 *
	 * @return array Array of two movies
	*/
	public static function retrieveBest () {
		return self::retrieve()
		    ->join('top', 'top.entity = entity.id', array())
			->where('entity.status = ?', self::ENTITY_APPROVED)
			->order('top.views24 desc')
			->limit(10)
			->setRowClass('Model_Entity')
			->fetchAll();
	}




	/**
	 * Retrieves my favorites for SEO
	 *
	 * @return array Array of entities for SEO
	*/
	public static function retrieveKeywordsForSEO (Model_User $user) {
		$keywords = null;
		$classes = Model_Class::retrieveAll();
		foreach($classes as $class):
			$entities = Model_Favorite::retrieveUserEntitiesByClass($user, $class);
			if ($entities)
				$hasFavorites = true;
			$classentities[] = array($class->getName() => $entities);
		endforeach;

		foreach ($classentities as $rec => $class) {
			foreach ($class as $name => $entities) {
				foreach ($entities as $entity) {
					$keywords[] = $entity;
				}
			}
		}
		if (count($keywords) > 0)
			return $keywords;
		else
			return null;
	}


	/**
	 * Retrieves all entities stored in the database
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveAll () {
		return self::retrieve()
            ->order('entity.id desc')
			->setRowClass('Model_Entity')
			->fetchAll();
	}


	/**
	 * Retrieves all entities stored in the database
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveByStatus ($status = 1) {
		return self::retrieve()
		    ->where('entity.status = ?', $status)
		    ->order('entity.created desc')
			->setRowClass('Model_Entity')
			->fetchAll();
	}

    /**
     * undocumented function
     *
     * @return void
     * @author fatboy
     **/
    public static function isOnline(Model_Entity $entity){
        if ($entity->status == Model_Entity::ENTITY_APPROVED && $entity->status != Model_Entity::ENTITY_PENDING) {
            return true;
        }
        return false;
    }


    /**
     * Retrieves entities that
     * have to be cached for search
     *
     * @return void
     * @author fatboy
     **/
    public static function retrieveExpired($limit = 100){
		return self::retrieve()
		    ->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
            ->where('entity.cached < ?', new Zend_Db_Expr('NOW() - INTERVAL 1 DAY'))
            ->order('entity.id desc')
		    ->limit($limit)
			->setRowClass('Model_Entity')
			->fetchAll();
    }

    /**
     * Retrieves entities that
     * have to be updated
     *
     * @return void
     * @author fatboy
     **/
    public static function retrieveEntitiesToUpdate($limit){
        return self::retrieve()
            ->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
            ->where('entity.updated < ?', new Zend_Db_Expr('NOW() - INTERVAL 1 HOUR'))
            ->orWhere('entity.updated is NULL')
            ->order('entity.id desc')
            ->limit($limit)
            ->setRowClass('Model_Entity')
            ->fetchAll();
    }


	public static function retrieveByKeyword($limit = 100) {
        $keyword = Model_Search_Query::getKeyword();
    	return self::retrieve(false)
    	    ->from('entity', array('entity.*', 'score' => new Zend_Db_Expr('MATCH (entitycache.title,entitycache.original,entitycache.tags) AGAINST ("'.$keyword.'")')))
    		->join('entitycache', 'entity.id = entitycache.entity', array())
    		->where (new Zend_Db_Expr('MATCH (entitycache.title,entitycache.original,entitycache.tags) AGAINST ("'.$keyword.'")'))
    		->where('entity.class = ?', Model_Class::findByClassName('Video'))
    		->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
    		// ->having('score > 1')
    		->order('score desc')
			->limit($limit)
    		->setRowClass('Model_Entity')
    		->fetchAll();
	}


	public static function retrieveInactiveByKeyword($keyword) {
		$ret = self::retrieve()
			->where('entity.title like "%'.$keyword.'%" or entity.original like "%'.$keyword.'%"')
			->setRowClass('Model_Entity')
			->fetchAll();
		return $ret;
	}

	    /**
	     * undocumented function
	     *
	     * @return void
	     * @author fatboy
	     **/
	    public static function retrieveBySearchQuery($limit = 10000) {
			$req = self::retrieve(false)
    			->from('entity', array('entity.*',
    			                    'tagMatches' => new Zend_Db_Expr('count(*)')));

            $req->join ('tagged', 'tagged.entity = entity.id', array())
                ->join ('tag', 'tag.id = tagged.tag', array());

            foreach (Model_Search_Query::retrieveTags() as $tag) {
                $req->orwhere('tag.value = ?', $tag->value);
            }


            $keyword = Model_Search_Query::getKeyword();
            if ($keyword) {
                $req->where('entity.title like "%'.$keyword.'%" OR  entity.original like "%'.$keyword.'%" OR entity.description like "%'.$keyword.'%"');
            }

            $req->where('entity.status = ?', Model_Entity::ENTITY_APPROVED);
            $req->where('(entity.class = '. Model_Class::findByClassName('Video').' or entity.class = '.Model_Class::findByClassName('TV').')');


            $req->having('tagMatches = '.count(Model_Search_Query::retrieveTags()));

            return $req->setRowClass('Model_Entity')
                ->group('entity.id')
		    	->order(new Zend_Db_Expr('entity.modified desc, entity.id desc'))
                ->limit($limit)
                ->fetchAll();
	    }



    /**
     * Retrieves random favorite entities
     * for the specific user
     *
     * @param Model_User user
     * @return void
     * @author fatboy
     **/
    public static function retrieveSavedByUser(Model_User $user){
        return self::retrieve(false)
            ->from('entity', array('entity.*'))
			->join('saved', 'saved.entity = entity.id', array())
			->where('saved.user = ?', $user)
			->group('entity.id')
			->order('saved.id desc')
			->limit(35)
			->setRowClass('Model_Entity')
			->fetchAll();
    }




    /**
    * Retrieves favorite entities by specific user
    * @param Model_User user
    *
    * @return selected array
    */
	public static function retrieveEntitiesByUser (Model_User $user) {
		return self::retrieve(false)
		    ->from('entity', array('entity.*', 'opinion.text', 'favorite.*'))
			->join('favorite', 'favorite.entity = entity.id and favorite.active = 1 and favorite.user = '.$user, array())
			->joinleft('opinion', 'opinion.entity = favorite.entity and opinion.user = '.$user, array())
			->order('created desc')
			->setRowClass('Model_Entity')
			->fetchAll();
	}

    /**
    * Retrieves ent count by specific user
    * @param Model_User user
    *
    * @return selected array
    */
	public static function getCountByUser (Model_User $user) {
		return count(self::retrieve()
			->join('favorite', 'favorite.entity = entity.id and favorite.active = 1', array())
			->where('favorite.user = ?', $user)
			->setRowClass('Model_Entity')
			->fetchAll());
	}


    /**
    * Retrieves ent count by specific user
    * @param Model_User user
    *
    * @return selected array
    */
	public static function getCntByUser (Model_User $user) {
		return count(self::retrieve()
			->where('entity.user = ?', $user)
			->setRowClass('Model_Entity')
			->fetchAll());
	}

    /**
     * Finds user by nickname
     *
     * @param string nickname
     * @return Model_User
    */
	public static function findByTitle($title) {
	    try {
		    $row = self::retrieve()
			    ->where('entity.title = ?', $title)
			    ->setRowClass('Model_Entity')
			    ->fetchRow();
    	    return $row;
		} catch (Model_Entity_NotFoundException $e) {
    	    FaZend_Exception::raise('Model_Entity_NotFound',
    		"Entity with title '{$title}' doesn't exist", 'Exception');
	    }
	}

	/**
	 * Retrieves all entities stored in the database
	 * for creating the XML sitemap
	 *
	 * @return array Array of all entities
	*/
	public static function retrieveTop () {
		return self::retrieve()
			->from('entity', array('entity.*', 'cnt' => new Zend_Db_Expr('count(*)')))
		    ->join ('pageview', 'pageview.entity = entity.id', array())
		    ->group('entity.id')
		    ->order('cnt desc')
		    ->limit(20)
			->setRowClass('Model_Entity')
			->fetchAll();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author fatboy
	 **/
	public static function tagEntity(Model_Entity $entity, $tag){
	    $tags = explode(',', $tag);
	    foreach ($tags as $onetag) {
    	    try {
    	        $t = Model_Tag::add(trim($onetag));
    	        try {
                    Model_Tagged::create($entity, $t);
                } catch (Model_Tagged_CouldNotSave $e) {
        	        FaZend_Exception::raise('Model_Tagged_CouldNotSave', "Невозможно сохранить тэг '{$tag}'. Скорее всего такой уже сохранен", 'Exception');
                }
    	    } catch (Model_Tag_CouldNotCreate $e) {
    	        FaZend_Exception::raise('Model_Tag_CouldNotTag', 
        		"Can't tag entity with tag value '{$tag}'", 'Exception');
        	}
        }
	}
	
	/**
	 * Hide entity from search results
	 * @return void
	*/              
	public function tag(Model_Tag $tag) {
	    try {
	        $tagged = Model_Tagged::retrieveByEntityAndTag($this, $tag->value);
	    } catch (Model_Tagged_NoSuchTaggedEntity $ex) {
	        
	    }
	}

    /**
     * undocumented function
     *
     * @return void
     * @author fatboy
     **/
    public static function retrieveByTag(Model_Tag $tag) {
        $ret = self::retrieve(false)
            ->from('entity', array('entity.*',
                'tagMatches' => new Zend_Db_Expr('count(*)')))
            ->join ('tagged', 'tagged.entity = entity.id', array())
            ->join ('tag', 'tag.id = tagged.tag', array())
            ->where('tag.id = ?', $tag)
            ->where('entity.status = ?', Model_Entity::ENTITY_APPROVED)
            ->having('tagMatches = 1')
            ->setRowClass('Model_Entity')
            ->group('entity.id')
            ->order('entity.id desc')
            ->fetchAll();
        return $ret;
    }

    /**
     * Retrieves alerts count by entity
     */
    public static function retrieveTodayUpdatedEntityByUser (Model_User $user) {
        return self::retrieve(false)
            ->join ('saved', 'saved.entity = entity.id', array())
            ->join ('alert', 'alert.entity = saved.entity', array())
            ->where('alert.added > ?', new Zend_Db_Expr('CURRENT_DATE()'))
            ->where("alert.added < ?", new Zend_Db_Expr("CURRENT_DATE() + INTERVAL 1 DAY"))
            ->where('saved.user = ?', $user)
            ->setRowClass('Model_Entity')
            ->fetchAll();
    }



}
