<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Maps a URI request path to the actual path of the resource or resource producer.
 *
 * @package Com
 */
class Com_Web_ResourceMapper extends Tk_Object
{
    
    /**
     * @var Tk_Type_Path
     */
    protected $baseDir = null;
    
    
    /**
     * __construct
     *
     * @param Tk_Type_Path $baseDir The resources/templates base directory.
     */
    function __construct(Tk_Type_Path $baseDir)
    {
        $this->baseDir = $baseDir;
    }
    
    /**
     * Gets the system resource/template path.
     * Paths ending in .php/.htm are mapped to .html
     *
     * @param Tk_Type_Url $requestUrl The requested url. If null Tk_Request::requestUri() is used
     * @return Tk_Type_Path
     */
    function getResourcePath($requestUrl = null)
    {
    	if (!$requestUrl) {
    		$requestUrl = Tk_Request::requestUri();
    	}
        $urlPath = urldecode($requestUrl->getPath());
        
        if (substr($urlPath, -4) == '.htm') {
            $urlPath = substr($urlPath, 0, -4) . '.html';
        } elseif (substr($urlPath, -4) == '.php') {
            $urlPath = substr($urlPath, 0, -4) . '.html';
        } elseif (substr($urlPath, -1) == '/') {
            $urlPath = substr($urlPath, 0, -1);
        }
        if (strlen(Tk_Type_Url::$pathPrefix) > 1) {
            if (substr($urlPath, 0, strlen(Tk_Type_Url::$pathPrefix)) == Tk_Type_Url::$pathPrefix) {
                $urlPath = substr($urlPath, strlen(Tk_Type_Url::$pathPrefix));
            }
        }
        
        $resourcePath = new Tk_Type_Path($this->baseDir->getPath() . $urlPath);
        if ($resourcePath->getExtension() != 'html') {
            $resourcePath = $resourcePath->append('/index.html');
        }
        
        return $resourcePath;
    }

}