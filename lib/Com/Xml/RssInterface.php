<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * An interface to Allow objects to feed Rss information
 *
 * @package Com
 */
interface Com_Xml_RssInterface
{
    /**
     * Get the Title string
     *
     * @return string
     */
    function getRssTitle();
    
    /**
     * Get the Description string
     *
     * @return string
     */
    function getRssDescr();
    
    /**
     * Get the item view url
     *
     * @return Tk_Type_Url
     */
    function getRssLink();

}