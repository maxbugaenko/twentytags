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
 * Index Controller. Controller that manages actions and operations
 */
class IndexController extends FaZend_Controller_Action {

    /**
     * reset the layout
     * @return void
     * @author fatboy
     **/
    public function preDispatch(){
        $this->_helper->layout->setLayout('layout');
        if (Model_User::isLoggedIn()) {
            $this->view->user = Model_User::me();
        } else {
            $this->view->user = null;
        }
    }

    /**
     * Lists all available entities
     * @return void
     */
    public function feedAction() {
        if (!Model_User::isLoggedIn()) {
            $this->redirect("browse");
        }
        $this->view->alerts = Model_Alert::retrieveByUser(Model_User::me(), 0);
        $this->view->mainTag = "News feed";
        if (count($this->view->alerts) == 0) {
            $this->redirect("emptyfeed");
        }
        //FaZend_Paginator::addPaginator($alerts, $this->view, $this->_getParamOrFalse('page'));
        //$this->view->paginator->setItemCountPerPage(10);
    }

    public function morealertsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $offset = $this->getRequest()->getParam("offset");
        $ent = $this->getRequest()->getParam("entity");
        if ($ent) {
            $entity = new Model_Entity((int)$ent);
            $alerts = Model_Alert::retrieveByEntity($entity, $offset);
            $mode = "entity";
        } else {
            $alerts = Model_Alert::retrieveByUser(Model_User::me(), $offset);
        }
        if (count($alerts) > 0) {
            echo $this->view->partial("partials/morealerts.phtml", array("alerts" => $alerts, "mode" => $mode));
        } else {
            echo "FINISH";
        }
    }

    public function moretagsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $offset = $this->getRequest()->getParam("offset");
        $tagParam = $this->getRequest()->getParam("tag");
        if ($tagParam) {
            $tag = new Model_Tag((int)$tagParam);
            if ($tag->exists()) {
                $entities = Model_Entity::retrieveByTag($tag, $offset);
            }
        } else {
            $entities = Model_Entity::retrieveAll($offset);
        }
        if (count($entities) > 0) {
            echo $this->view->partial("partials/moretags.phtml", array("entities" => $entities));
        } else {
            echo "FINISH";
        }
    }


    /**
	* Lists all available entities
	* @return void
	*/
	public function indexAction() {
        $route = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();
        $keyword = $this->getRequest()->getParam("key");
        $this->view->isLanding = $this->getRequest()->getParam("landing");
        if ($route == "following") {
            if (!Model_User::isLoggedIn()) {
                $this->redirect("browse");
            }
            $this->view->mainTag = Model_User::me()->name;
            $this->view->entities = Model_Entity::retrieveSavedByUser(Model_User::me());
            if (count($this->view->entities) == 0) {
                $this->redirect("empty");
            }
        } elseif ($keyword) {
            $this->view->entities = Model_Entity::retrieveByKeyword($keyword, 20);
            if (count($this->view->entities) == 0) {
                $this->view->message = true;
                $entity = new Model_Entity();
                $entity->title = $keyword;
                $entity->description = "none";
                $entity->status = 0;
                $entity->save();
                Model_Static_Functions::cacheEntity($entity);
                $this->view->entities = Model_Entity::retrieveByKeyword($keyword, 20);
            }
        } else {
            $tag = new Model_Tag((int)$this->getRequest()->getParam('id'));
            if ($tag->exists()) {
                $this->view->mainTag = $tag->value;
                $this->view->tag = $tag;
                $this->view->entities = Model_Entity::retrieveByTag($tag, 0);
            } else {
                $this->view->mainTag = "What's hot";
                $this->view->entities = Model_Entity::retrieveAll(0);
            }
        }
	}
    /**
     * Empty profile page
     * @return void
     */
    public function emptyAction() {
        if (!Model_User::isLoggedIn()) {
            $this->redirect("browse");
        }
    }

    /**
     * Empty profile page
     * @return void
     */
    public function emptyfeedAction() {
        if (!Model_User::isLoggedIn()) {
            $this->redirect("browse");
        }
    }

    /**
     * Empty profile page
     * @return void
     */
    public function settingsAction() {
    }
    /**
     * Logs out facebook user
     * @return void
     */
    public function logoutAction() {
        Model_User::me()->logOut();
    }

    /**
     * Logs in and register facebook user
     * @return void
     */
    public function loginAction() {
        require_once('Model/Facebook/facebook.php');
        $config = array(
            'appId' => '706201236078444',
            'secret' => 'fabe992657dcffefd59099502cdf0ce9',
            'allowSignedRequest' => false,
            'cookie' => true,
            'code' => $_GET["code"]
        );
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;
        $facebook = new Facebook($config);
        $user_id = $facebook->getUser();
        if($user_id) {
            try {
                $userProfile = $facebook->api('/me','GET');
                $nickname = $userProfile["id"];
                $name = $userProfile["name"];
                $email = $userProfile["id"]."@twentytags.com";
                $password = "twentytags";
                try {
                    $user = Model_User::findByNickname($nickname);
                } catch (Model_User_NicknameNotFound $e) {
                    $user = new Model_User();
                    $user->nickname = $nickname;
                    $user->email = $email;
                    $user->name = $name;
                    $user->password = $password;
                    $user->save();
                }
                $user->logIn();
                $follow = new Model_Entity((int)$this->getRequest()->getParam("follow"));
                if ($follow->exists()) {
                    $saved = new Model_Saved();
                    $saved->user = $user;
                    $saved->entity = $follow;
                    $saved->save();
                    $this->redirect("following");
                }
                $redirect = $this->getRequest()->getParam("redirect");
                if ($redirect) {
                    $this->redirect($redirect);
                }
            } catch(FacebookApiException $e) {
                echo "error";
                exit;
            }
        } else {
            echo "no user id";
            exit;
        }
    }


    /**
     * Logs out facebook user
     * @return void
     */
    public function entityAction() {
        $entity = new Model_Entity((int)$this->getRequest()->getParam("id"));
        $this->view->entity = $entity;
        $this->view->alerts = Model_Alert::retrieveByEntity($entity, 0);
    }

    /**
     * Add tag page
     * @return void
     */
    public function addtagAction() {
        if (!Model_User::isLoggedIn()) {
            $this->redirect("browse");
        }
    }

    /**
     * Add tag page
     * @return void
     */
    public function addtagsignupAction() {
        $this->view->tag = new Model_Entity((int)$this->getRequest()->getParam("tag"));
    }

    /**
     * Add tag page
     * @return void
     */
    public function registerAction() {
        $this->view->mode = $this->getRequest()->getParam("mode");
    }


    /**
     * Add tag page
     * @return void
     */
    public function addusertagAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $user = Model_User::me();
        $tag = $this->getRequest()->getParam("tag");
        if ($tag != "") {
            $entity = new Model_Entity();
            $entity->title = $tag;
            $entity->picture = "question.png";
            $entity->description = "Under moderation";
            $entity->status = 0;
            $entity->save();
            $saved = new Model_Saved();
            $saved->user = Model_User::me();
            $saved->entity = $entity;
            $saved->save();
            echo "OK";
        } else {
           echo "Введите какой нибудь тег";
        }
    }

    /**
     * Add tag page
     * @return void
     */
    public function registeruserAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $nickname = $this->getRequest()->getParam("nickname");
        $password = $this->getRequest()->getParam("password");
        $tag = $this->getRequest()->getParam("tag");
        $mode = $this->getRequest()->getParam("mode");
        if ($nickname != "" && $password != "") {
            try {
                $user = Model_User::findByNickname($nickname);
                if ($user->password  == $password) {
                    $user->logIn();
                    if ($tag) {
                        $saved = new Model_Saved();
                        $saved->user = $user;
                        $saved->entity = $tag;
                        $saved->save();
                        echo "TAG_ADDED";
                        exit;
                    }
                    if ($mode == "addtag") {
                        echo "ADDTAG";
                        exit;
                    }
                    echo "LOGGED_IN";
                    exit;
                } else {
                    echo "Неверный пароль или такой никнейм уже существует";
                    exit;
                }
            } catch (Model_User_NicknameNotFound $ex) {
                $user = new Model_User();
                $user->nickname = $nickname;
                $user->email = $nickname."@twentytags.com";
                $user->name = $nickname;
                $user->password = $password;
                try {
                    $user->save();
                    $user->logIn();
                    if ($tag) {
                        $saved = new Model_Saved();
                        $saved->user = $user;
                        $saved->entity = $tag;
                        $saved->save();
                        echo "TAG_ADDED";
                        exit;
                    }
                    if ($mode == "addtag") {
                        echo "ADDTAG";
                        exit;
                    }
                    echo "LOGGED_IN";
                } catch (Exception $ex) {
                    echo "Произошла ошибка";
                }
            }
        } else {
            echo "Введите никнейм и пароль";
        }
    }

    /**
     * Logs out facebook user
     * @return void
     */
    public function followAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $user = Model_User::me();
        $entity = $this->getRequest()->getParam("entity");
        try {
            Model_Saved::findByUserAndEntity($user, $entity);
        } catch (Model_Saved_NoSuchSaved $e) {
            $saved = new Model_Saved();
            $saved->user = $user;
            $saved->entity = $entity;
            $saved->save();
        }
        echo "OK";
    }

    /**
     * Turns notifications on/off
     * @return void
     */
    public function turnAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $mode = $this->getRequest()->getParam("mode");
        $user = Model_User::me();
        $user->notification = $mode;
        $user->save();
        $this->redirect("settings");
    }

    /**
     * Logs out facebook user
     * @return void
     */
    public function unfollowAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $user = Model_User::me();
        $entity = $this->getRequest()->getParam("entity");
        $saved = Model_Saved::retrieveByEntityAndUser($entity, $user);
        $saved->delete();
        echo "OK";
    }

    /**
     * Logs out facebook user
     * @return void
     */
    public function sitemapAction() {
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;
        $root = $dom->createElement('urlset');
        $root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $dom->appendChild($root);

        foreach(Model_Entity::retrieveWithAlerts() as $entity) {
            $url = $dom->createElement('url');
            $url->appendChild(
                $dom->createElement('loc', WEBSITE_URL . $this->view->url(array("id"=>$entity, Model_Static_Functions::dashString($entity->title)=>""), "entity", false))
            );
            $root->appendChild($url);
            unset($url);
        }
        $this->_returnXML($dom->saveXML());
    }

}
