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
require_once FAZEND_APP_PATH . "/controllers/AdmController.php";

/**
 * Admin Controller. Contoller that manages ADMINISTRATOR actions 
 */
class AdmController extends Fazend_Controller_Action {
    /**
     * reset the layout
     * @return void
     * @author fatboy
     **/
    public function preDispatch(){
        $this->_helper->layout->setLayout('admin');
    }

    /*
     * Admin index page
     * */
    public function indexAction() {
	}

    /*
     * Entity administration
     * */
    public function entityAction() {
        $iterator = Model_Entity::retrieveAll();
        $this->view->tags = Model_Tag::retrieveAll();
        FaZend_Paginator::addPaginator($iterator, $this->view, $this->_getParamOrFalse('page'));
        $this->view->paginator->setItemCountPerPage(100);
    }

    /*
     * Entity administration
     * */
    public function moderationAction() {
        $iterator = Model_Entity::retrieveForModeration();
        FaZend_Paginator::addPaginator($iterator, $this->view, $this->_getParamOrFalse('page'));
        $this->view->paginator->setItemCountPerPage(100);
    }


    /*
     * Alert administration
     * */
    public function alertAction() {
        $iterator = Model_Alert::retrieveAll();
        FaZend_Paginator::addPaginator($iterator, $this->view, $this->_getParamOrFalse('page'));
        $this->view->paginator->setItemCountPerPage(10);
    }

    /**
     * Tag entity
     * @return void
     */
    public function tagitAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $entity = new Model_Entity((int)$this->getRequest()->getParam('entity'));
        $tag = new Model_Tag((int)$this->getRequest()->getParam('tag'));
        Model_Tagged::create($entity, $tag);
        echo "OK";
    }
    /**
     * Update table action. Made for editables back-end
     * @return void
     * @author fatboy
     **/
    public function updateAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $class = "Model_".$this->getRequest()->getParam('table');
        $id = new $class((int)$this->getRequest()->getParam('id'));
        $column = $this->getRequest()->getParam('column');
        $data = $this->getRequest()->getPost();
        $id->{$column} = $data['value'];
        $id->save();
        echo $data['value'];
    }
}