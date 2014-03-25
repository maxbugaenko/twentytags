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
class Model_Static_Functions {

    /*
     * Retrieves og:image url from the
     * given URL
     */
    public static function getOgImage($url) {
        libxml_use_internal_errors(true);
        $c = file_get_contents($url);
        $d = new DomDocument();
        $d->loadHTML($c);
        $xp = new domxpath($d);
        foreach ($xp->query("//meta[@property='og:image']") as $el) {
            return $el->getAttribute("content");
        }
    }

    /*
     * retrieves image from given URL
     * and saves it to the images folder
     */
    public static function saveImageFromUrl($path, $link) {
        $imageLink = str_replace("https", "http", $link);
        $pic = file_get_contents($imageLink);
        $filename = time().rand(1, 5000);
        file_put_contents($path.$filename, $pic);
        $imageType = exif_imagetype($path.$filename);
        switch ($imageType) {
            case 1: $extension = ".gif"; break;
            case 2: $extension = ".jpg"; break;
            case 3: $extension = ".png"; break;
        }
        $finalFilename = $filename.$extension;
        exec("mv ".$path.$filename." ".$path.$finalFilename);
        return $finalFilename;
    }
}
?>