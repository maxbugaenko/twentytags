<?php
/**
 * FaZend Framework
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt. It is also available
 * through the world-wide-web at this URL: http://www.fazend.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@fazend.com so we can send you a copy immediately.
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id: SqueezePNG.php 2239 2010-12-10 10:29:49Z yegor256@gmail.com $
 * @category FaZend
 */

/**
 * @see FaZend_View_Helper
 */
require_once 'FaZend/View/Helper.php';

/**
 * Compresses many PNG/GIF files into one big holder
 *
 * @see http://naneau.nl/2007/07/08/use-the-url-view-helper-please/
 * @package View
 * @subpackage Helper
 */
class FaZend_View_Helper_SqueezePNG extends FaZend_View_Helper
{

    /**
     * Location of squeeze PNG files
     */
    const SQUEEZE_FOLDER = '/views/squeeze/';

    /**
     * What to show on failure?
     */
    const FAILURE = 'border: 1px solid red;';

    /**
     * Name of the route
     * @see routes.ini
     */
    const ROUTE = 'fz__squeeze';

    /**
     * Current file to render (relative name)
     *
     * @var string
     */
    protected $_file;

    /**
     * Get path of temp PNG holder.
     *
     * @return string
     */
    public function getImagePath()
    {
        return TEMP_PATH . '/fazend-' . FaZend_Revision::getName()
            . '-' . FaZend_Revision::get() . '.png';
    }

    /**
     * Get map path
     *
     * @return string
     */
    public function getMapPath()
    {
        return $this->getImagePath().'.data';
    }

    /**
     * Load map
     *
     * @return array
     */
    public function loadMap()
    {
        $file = $this->getMapPath();
        if (!file_exists($file)) {
            return array();
        }
        $map = @unserialize(file_get_contents($file));
        if (!is_array($map)) {
            return array();
        }
        // checksum of the image file
        if ($map['md5'] != md5_file($this->getImagePath())) {
            return array();
        }
        // clean it out
        $this->_clean($map);
        return $map;
    }

    /**
     * Save map
     *
     * @param array Map
     * @param string PNG image (all images together)
     * @return void
     */
    public function saveMap(array $map, $png)
    {
        $file = $this->getMapPath();
        $pngFile = $this->getImagePath();

        file_put_contents($pngFile, $png);

        $map['md5'] = md5_file($pngFile);

        file_put_contents($file, serialize($map));
    }

    /**
     * Show the image
     *
     * @return string
     */
    public function squeezePNG($file = false)
    {
        if ($file) {
            $this->_file = $file;
        }
        return $this;
    }

    /**
     * Render the class
     *
     * @return string HTML
     */
    public function __toString()
    {
        return $this->_render();
    }

    /**
     * Start building the image holder from scratch
     *
     * @return string HTML
     */
    public function startOver()
    {
        if (file_exists($this->getMapPath())) {
            unlink($this->getMapPath());
        }
        if (file_exists($this->getImagePath())) {
            unlink($this->getImagePath());
        }
        return $this;
    }

    /**
     * Url of the holder image
     *
     * @return string
     */
    public function url()
    {
        return $this->getView()->url(
            array(
                'id' => (int)FaZend_Revision::get()
            ),
            self::ROUTE,
            true
        );
    }

    /**
     * Show the individual image
     *
     * @return string
     */
    protected function _render()
    {
        // build full name of the file
        $file = APPLICATION_PATH . self::SQUEEZE_FOLDER . $this->_file;

        // maybe it's a mistake?
        if (!is_file($file)) {
            return self::FAILURE;
        }
        // load full map with this new file inside
        $map = $this->_loadUpdatedMap($file);

        // maybe something was wrong?
        if (!isset($map['images'][$file])) {
            return self::FAILURE;
        }
        return sprintf(
            "background:url(" . $this->url() .
            ") no-repeat;width:%dpx;height:%dpx;background-position:%dpx %dpx;display:inline-block;",
            $map['images'][$file]['width'],
            $map['images'][$file]['height'],
            -$map['images'][$file]['x'],
            -$map['images'][$file]['y']
        );
    }

    /**
    * Load map and adds this file into it, if it's not there already
    *
    * @param string File name of a new image (or existing one)
    * @return array
    */
    protected function _loadUpdatedMap($file)
    {
        // load map from the file, as it is
        $map = $this->loadMap();

        // if the file is there already and it's up to date
        $isReady = isset($map['images'][$file])
            && file_exists($file)
            && (filemtime($file) == $map['images'][$file]['mtime']);
        if ($isReady) {
            return $map;
        }
        // clean the map and remove all incorrect elements
        $this->_clean($map);

        // add new file to the map
        $this->_addFile($map, $file);

        // get PNG content and save it
        $png = $this->_buildPNG($map);
        $this->saveMap($map, $png);

        // return it after all changes done
        return $map;
    }

    /**
    * Removes old and exprired files from the map
    *
    * @param array Map of files
    * @return void
    */
    protected function _clean(array &$map)
    {
        // if it's very fresh - prepare it
        if (!isset($map['images'])) {
            $map['images'] = array();
        }
        // delete obsolete elements from the map (lost images)
        foreach ($map['images'] as $id=>$img) {
            if (file_exists($id)) {
                continue;
            }
            unset($map['images'][$id]);
        }
    }

    /**
    * Add new file to the map
    *
    * @param array Map of files
    * @param string Full name of the file to add
    * @return array
    */
    protected function _addFile(array &$map, $file)
    {
        // add new image
        $png = imagecreatefrompng($file);
        $thisImage = array(
            'md5' => md5_file($file),
            'mtime' => filemtime($file)
        );
        $map['images'][$file] = $thisImage;
    }

    /**
    * Compress existing map
    *
    * @param array Map
    * @return array Metadata about images
    */
    protected function _compress(array &$map)
    {
        $metadata = $this->_loadMetadata($map);
        // start with top left
        $x = $y = $height = 0;

        foreach ($map['images'] as $id=>&$img) {
            $png = $metadata['images'][$id];

            $img['x'] = $x;
            $img['y'] = $y;
            $img['width'] = imagesx($png);
            $img['height'] = imagesy($png);

            $x += $img['width'];
            $height = max($height, $img['height']);
        }

        $metadata['width'] = $x;
        $metadata['height'] = $height;
        return $metadata;
    }

    /**
    * Load images into metadata array
    *
    * @param array Map
    * @return array Metadata and image copies
    */
    protected function _loadMetadata(array &$map)
    {
        $metadata = array();
        $metadata['images'] = array();

        // if the data provided are corrupted (hm...)
        if (!isset($map['images'])) {
            $map['images'] = array();
        }
        // load them all!
        foreach ($map['images'] as $id=>&$img) {
            $image = @imagecreatefrompng($id);
            if (!$image) {
                $image = @imagecreatefromgif($id);
                if (!$image) {
                    unset($map['images'][$id]);
                    continue;
                }
            }
            $metadata['images'][$id] = $image;
        }
        return $metadata;
    }

    /**
    * Build the holder PNG file
    *
    * @param array Map
    * @return string PNG
    */
    protected function _buildPNG(array &$map)
    {
        // compress the map to remove white spaces
        $metadata = $this->_compress($map);

        $holder = imagecreatetruecolor($metadata['width'], $metadata['height']);

        // see: http://www.php.net/manual/en/function.imagealphablending.php
        imagealphablending($holder, false);

        $white = imagecolorallocate($holder, 255, 255, 255);
        imagefill($holder, 0, 0, $white);

        // copy all images to the holder, in proper places
        foreach ($map['images'] as $id=>&$img) {
            // copy new image to the holder
            imagecopy($holder, $metadata['images'][$id], $img['x'], $img['y'], 0, 0, $img['width'], $img['height']);
        }

        ob_start();
        // see: http://www.php.net/manual/en/function.imagesavealpha.php
        imagesavealpha($holder, true);

        // see: http://www.php.net/manual/en/function.imagepng.php
        // no compression
        // output to stream (not file)
        imagepng($holder, null, 8, PNG_ALL_FILTERS);

        $pngContent = ob_get_contents();
        ob_end_clean();

        return $pngContent;
    }

}
