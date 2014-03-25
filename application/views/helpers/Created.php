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
 * @version $Id: DateInterval.php 1747 2010-03-17 19:17:38Z david256@gmail.com $
 * @category FaZend
 */

/**
 * Show interval between now and the given date
 *
 * @package View
 * @subpackage Helper
 */
class Helper_Created
{

    /**
     * Show interval between now and the given date in the past(!)
     *
     * @param Zend_Date Date/time 
     * @return string
     * @throws FaZend_View_Helper_DateInterval_InvalidDateException
     */
    public function created2(Zend_Date $time)
    {
        if (!($time instanceof Zend_Date)) {
            FaZend_Exception::raise(
                'FaZend_View_Helper_DateInterval_InvalidDateException',
                'Only instance of Zend_Date is accepted for dateInterval()'
            );
        }
        
		$rnow = Zend_Date::now();
		
//		$rnow = time();
		// echo "gmtime". time()."<br/>";	
		// echo "req time".$_SERVER['REQUEST_TIME']."<br/>";
		// echo "<br/>1111".date('l jS  F Y h:i:s A');
		if ($rnow->isEarlier($time)) {
			$diff = $time->getTimestamp() - $rnow->getTimestamp();
			$sign = '-';
		} else {
			$diff = $rnow->getTimestamp() - $time->getTimestamp();
			$sign = '';
		}
		echo "<br/><br/>";
		echo "создано: ".$time."<br/>";
		echo "сейчас: ".$rnow."<br/>";
		
		
        $hoursDifference = abs($diff / (60 * 60));    
		echo $sign.$hoursDifference;
 		exit;

        switch (true) {
            // more than 24months days - we show years
            case $hoursDifference > 24 * 30 * 24:
                $years = $hoursDifference / (24 * 30 * 12);
                return _t(
                    '%s%syears',
                    $sign,
                    self::_mod($years)
                );

            // more than 60 days - we show months
            case $hoursDifference > 24 * 60:
                return _t(
                    '%s%smonths',
                    $sign,
                    self::_mod($hoursDifference / (24 * 30))
                );

            // more than 14days - we show weeks
            case $hoursDifference > 24 * 14:
                return _t(
                    '%s%sweeks',
                    $sign,
                    self::_mod($hoursDifference / (24 * 7))
                );

            // more than 2 days we show days    
            case $hoursDifference > 48:
                $hours = round(fmod($hoursDifference, 24));
                return _t(
                    '%s%sdays%s',
                    $sign,
                    round($hoursDifference / 24)
                ) . 
                ($hours ? 
                    '&nbsp;' . _t(
                        '%dhrs',
                        $hours
                    ) : 
                false);

            // more than 5 hours - we should hours    
            case $hoursDifference > 5:
                return _t(
                    '%s%shrs',
                    $sign,
                    round($hoursDifference)
                );    

            // more than 1 hour - we should hour+min    
            case $hoursDifference >= 1:
                $minutes = round(fmod($hoursDifference, 1) * 60);
                return _t(
                    '%s%shrs',
                    $sign,
                    floor($hoursDifference)
                ) . ($minutes ? 
                    '&nbsp;' . _t(
                        '%dmin',
                        $minutes
                    ) : 
                false);

            // otherwise just minutes    
            default:
                return _t(
                    '%s%smin',
                    $sign,
                    round($hoursDifference * 60)
                );
        }
    }

    /**
     * To convert "A/B" into "C + 1/2 or 1/4 or 3/4"
     *
     * @param float Value, with float point, e.g. 13.79
     * @return string String, e.g. "13 3/4"
     */
    protected static function _mod($a)
    {
        $str = floor($a);
        
        $mod = $a - $str;

        switch (true) {
            case ($mod > 0.75):
                $str .= '&frac34;';
                break;

            case ($mod > 0.5):
                $str .= '&frac12;';
                break;

            case ($mod > 0.25):
                $str .= '&frac14;';
                break;
        }    

        return $str;
    }

    public function created(Zend_Date $t) {
        if (!($t instanceof Zend_Date)) {
            FaZend_Exception::raise(
                'FaZend_View_Helper_DateInterval_InvalidDateException',
                'Only instance of Zend_Date is accepted for dateInterval()'
            );
        }
        
		$rnow = Zend_Date::now();
		
//		$rnow = time();
		// echo "gmtime". time()."<br/>";	
		// echo "req time".$_SERVER['REQUEST_TIME']."<br/>";
		// echo "<br/>1111".date('l jS  F Y h:i:s A');
		if ($t->isToday()) {
			$created = _t('today');
		} elseif ($t->isYesterday()) {
			$created = _t('yesterday');
		} else {
			Zend_Date::setOptions(array('format_type' => 'php'));
			$created = $t->toString('F j');
		}
		return $created;
	}


}
