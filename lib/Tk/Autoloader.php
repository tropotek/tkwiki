<?php
/*
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Tropotek
 */

include_once (dirname(__FILE__) . "/Functions/php.php");
include_once (dirname(__FILE__) . "/Exception.php");

/**
 * The main class loader.
 * Uses loosely coupled loaders in order to operate
 *
 * @package Tk
 */
class Tk_AutoLoader
{
    /**
     * Contains any attached service loaders
     * @var Dk_Loader[]
     */
    protected static $loaderList = array();
    
    
    static $externalClassPath = array();
    
    static $lookups = 0;

    
    /**
     * Add an external class and path
     *
     * @param string $className
     * @param string $fullPath
     */
    static function addClass($className, $fullPath)
    {
        self::$externalClassPath[$className] = $fullPath;
    }
    
    /**
     * delete an external class
     *
     * @param string $className
     */
    static function deleteClass($className)
    {
        if (isset(self::$externalClassPath[$className])) {
            unset(self::$externalClassPath[$className]);
        }
    }
    
    /**
     * Get the external class array
     *
     * @return array
     */
    static function getClassList()
    {
        return self::$externalClassPath;
    }
    
    
    /**
     * Attach a new type of loader
     *
     * @param Tk_Loader $loader
     * @param string key
     */
    static function add(Tk_Loader $loader, $key)
    {
        self::$loaderList[$key] = $loader;
    }
    
    /**
     * Remove a loader that's been added
     *
     * @param string $key
     * @return boolean
     */
    static function delete($key)
    {
        if (self::isActiveLoader($key)) {
            unset(self::$loaderList[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * Check if a loader is currently loaded
     *
     * @param string $key
     * @return boolean
     */
    static function exists($key)
    {
        return array_key_exists($key, self::$loaderList);
    }
    
    /**
     * Load in the required service by asking all service loaders
     *
     * @param string class
     */
    static function load($class)
    {
        // Increment lookup counter
        self::$lookups++;
        
        
        
        foreach (self::$loaderList as $obj) {
            $path = $obj->getPath($class);
            if ($path) {
                require_once $path;
                if (class_exists($class))
                    return;
            }
        }
        
//        foreach (self::$loaderList as $obj) {
//            if ($obj->canLocate($class)) {
//                require_once $obj->getPath($class);
//                if (class_exists($class))
//                    return;
//            }
//        }
    }
    
    /**
     * Return the total of class lookups processed
     * 
     * @return integer
     */
    static function getLookupCount()
    {
        return self::$lookups;
    }
    
}

/**
 * Defines the methods any actual loaders must implement
 *
 * @package Dk
 */
interface Tk_LoaderInterface
{
    
    /**
     * Inform of whether or not the given class can be found
     *
     * @param string $class
     * @return boolean
     */
    public function canLocate($class);
    
    /**
     * Get the path to the class
     *
     * @param string $class
     * @return string
     */
    public function getPath($class);

}

/**
 *
 * @package Dk
 */
class Tk_Loader implements Tk_LoaderInterface
{
    protected $basePath = '.';
   
    public function __construct($basePath = '.')
    {
        $this->basePath = (string)$basePath;
    }
   
    public function canLocate($class)
    {
        $path = $this->getPath($class);
        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }
   
    public function getPath($className)
    {
        $file = str_replace('_', '/', $className) . '.php';
        if (@is_file($this->basePath . '/' . $file)) {
            return $this->basePath . '/' . $file;
        }
        $autoloadAliases = Tk_AutoLoader::getClassList();
        if (array_key_exists($className, $autoloadAliases)) {
            return $autoloadAliases[$className];
        }
        
        $arr = explode('_', $className);
        $file1 = implode('/', $arr). '/' . $arr[count($arr)-1] . '.php';
        if (@is_file($this->basePath . '/' . $file1)) {
            return $this->basePath . '/' . $file1;
        }
        Tk::log('Class not found: `' . $className . '` (' . $this->basePath . '/' . $file . ')', Tk::LOG_ALERT);
    }
}


/*
 * Register our autoloader class method
 */
spl_autoload_register(array('Tk_AutoLoader', 'load'));

/*
 * Add The Dk autoloader to the stack
 */
Tk_AutoLoader::add(new Tk_Loader($libPath), 'Tk');



