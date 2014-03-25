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
abstract class Model_Provider_Provider  {

    /*
     * Source variable
     */
    protected  $source;

    /*
     * Entity to work with
     */
    protected $entity;

    /*
     * Constructs provider class
     */
    public function __construct($provider, Model_Entity $entity) {
        echo "\n\nUpdating {$entity->title} from {$provider} ... \n";
        try {
            $this->sources = Model_Source::retrieveByProviderAndEntity($provider, $entity);
            $this->entity = $entity;
            $this->parseAlerts();
        } catch (Model_Source_NoSuchSourceException $e) {
            echo "  -- No such source '{$provider}' for entity '{$entity->title}'\n";
        }
    }

    /*
     * Parse specific source and store alerts data into
     * the database. This functions should be implemented
     * in each provider class
     */
    abstract public function parseAlerts();

}
