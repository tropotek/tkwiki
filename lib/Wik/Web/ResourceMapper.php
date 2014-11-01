<?php

/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Maps a URI request path to the actual path of the resource or resource producer.
 *
 * @package Web
 */
class Wik_Web_ResourceMapper extends Com_Web_ResourceMapper
{
    
    /**
     *
     * @param Tk_Type_Path $baseDir The resource system base template directory.
     */
    function __construct(Tk_Type_Path $baseDir)
    {
        parent::__construct($baseDir);
    }
    
    /**
     * Gets the system resource path.
     * Paths ending in .html are mapped to .php if not found.
     *
     * @param Tk_Type_Url $requestUrl The requested url.
     * @return Tk_Type_Path The resource path/template or null if it cannot be found.
     * @TODO Add extension to have language directories as /sp/ instead of /language/sp/, also alias /en/
     */
    function getResourcePath($requestUrl = null)
    {
        
        if (preg_match('/page\//i', $requestUrl->getPath())) {
            $pos = strpos($requestUrl->getPath(), 'page/');
            $pageName = substr($requestUrl->getPath(), $pos + 5);
            Tk_Request::getInstance()->setParameter('pageName', $pageName);
            $requestUrl = Tk_Type_Url::createUrl('/index.html')->set('pageName', $pageName);
        } else if (substr($requestUrl->toString(), -1) == '/' || substr($requestUrl->toString(), -11) == '/index.html') {
            Tk_Request::getInstance()->setParameter('pageName', 'Home');
            $requestUrl = Tk_Type_Url::createUrl('/index.html')->set('pageName', 'Home');
        }
        return parent::getResourcePath($requestUrl);
    }

}