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
 * Cache Entities
 */
class GetAlerts extends FaZend_Cli_Abstract {
    /**
     * To be executed from command line
     * @return int exit code
     */
    public function execute() {
        $ga = new Model_GAlerts_GAlerts("gozman.mark2014@gmail.com", "ghbdtn67");
        print_r($ga);
        $list = $ga->getList();
        print_r($list);
    }
}