<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Increment a a field of a HitInterface Object.
 *
 * @package Tk
 * @deprecated
 * @todo Move to another lib
 */
class Tk_Util_HitManager
{
    
    /**
     * Call the increment method only once per session
     *
     * @param Tk_Util_HitInterface $item
     * @param string $method
     * @param boolean $ignoreSession If true then a hit count is forced reguardless
     */
    static function processHit($item, $method = 'addHit', $ignoreSession = false)
    {
        if (!$item) {
            return;
        }
        if (!method_exists($item, $method)) {
            throw new Tk_Exception('Method does not exist - ' . $method);
        }
        $hitRecord = Tk_Session::get('Tk_HitRecord');
        if ($hitRecord == null) {
            $hitRecord = array();
        }
        $key = md5(get_class($item) . $method . ':' . $item->getId());
        if (!array_key_exists($key, $hitRecord) || $ignoreSession) {
            $hitRecord[$key] = true;
            $item->$method();
            $item->save();
        }
        Tk_Session::set('Tk_HitRecord', $hitRecord);
    }

}