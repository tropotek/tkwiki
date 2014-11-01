<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An abstract representation of file and directory pathnames.
 *
 * @package Tk
 */
class Tk_Type_Path extends Tk_Object
{
    /**
     * A prefix to append to path.
     *
     * Useful for when working in a dev enviroment, where the application is
     * not installed in the docroot of the domain.
     * This should be set to the $config->getSiteFileRoot() path if used.
     *
     * @var string
     */
    static $pathPrefix = '';
    
    /**
     * The full path of a file or directory
     * @var string
     */
    private $pathname = '';
    
    /**
     * Create a Path object
     *
     * @param string $pathname
     */
    function __construct($pathname)
    {
        if (substr($pathname, -1) == '/') {
            $pathname = substr($pathname, 0, -1);
        }
        if (substr($pathname, 0, 1) != '/' && !preg_match('/^[A-Za-z]:/', $pathname) && substr($pathname, 0, 2) != '\\\\') {
            $pathname = '/' . $pathname;
        }
        $pathname = str_replace('//', '/', $pathname);
        $this->pathname = $pathname;
    }
    
    /**
     * Create a path
     *
     * @return Tk_Type_Path
     */
    static function create($pathname)
    {
        return new self($pathname);
    }
    
    /**
     * Create a path
     *
     * @return Tk_Type_Path
     * @deprecated Use ::create()
     */
    static function createPath($pathname)
    {
        return self::create($pathname);
    }
    
    /**
     * Return a path object with the full path of a relative
     * file/directory from the site root.
     *
     * @param Tk_Type_Path $path
     * return Tk_Type_Path
     */
    static function createFromRalative($path)
    {
        if (is_string($path)) {
            return new Tk_Type_Path(self::$pathPrefix . $path);
        }
        return new Tk_Type_Path(self::$pathPrefix . $path->toString());
    }
    
    /**
     * Clean a filename
     *
     * @param string $file
     * @return string
     */
    static function cleanFilename($file)
    {
        $file = str_replace(' ', '_', $file);
        return preg_replace("/[^a-z0-9_\\-\\.\\/]/i", '_', $file);
    }
    
    /**
     * Get the bytes from a string like 40M, 10T, 100K
     *
     * @param string $str
     * @return integer
     */
    static function string2Bytes($str)
    {
        $sUnit = substr($str, -1);
        $iSize = (int)substr($str, 0, -1);
        switch (strtoupper($sUnit)) {
            case 'Y' :
                $iSize *= 1024; // Yotta
            case 'Z' :
                $iSize *= 1024; // Zetta
            case 'E' :
                $iSize *= 1024; // Exa
            case 'P' :
                $iSize *= 1024; // Peta
            case 'T' :
                $iSize *= 1024; // Tera
            case 'G' :
                $iSize *= 1024; // Giga
            case 'M' :
                $iSize *= 1024; // Mega
            case 'K' :
                $iSize *= 1024; // kilo
        }
        ;
        return $iSize;
    }
    
    /**
     * Convert a value from bytes to a human readable value
     *
     * @param integer $bytes
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    static function bytes2String($bytes)
    {
        $tags = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $index = 0;
        while ($bytes > 999 && isset($tags[$index + 1])) {
            $bytes /= 1024;
            $index++;
        }
        $rounder = 1;
        if ($bytes < 10) {
            $rounder *= 10;
        }
        if ($bytes < 100) {
            $rounder *= 10;
        }
        $bytes *= $rounder;
        settype($bytes, 'integer');
        $bytes /= $rounder;
        
        return $bytes . ' ' . $tags[$index];
    }
    
    /**
     * The trouble is the sum of the byte sizes of the files in your directories
     * is not equal to the amount of disk space consumed, as andudi points out.
     * A 1-byte file occupies 4096 bytes of disk space if the block size is 4096.
     * Couldn't understand why andudi did $s["blksize"]*$s["blocks"]/8.
     * Could only be because $s["blocks"] counts the number of 512-byte disk
     * blocks not the number of $s["blksize"] blocks, so it may as well
     * just be $s["blocks"]*512. Furthermore none of the dirspace suggestions allow
     * for the fact that directories are also files and that they also consume disk
     * space. The following code dskspace addresses all these issues and can also
     * be used to return the disk space consumed by a single non-directory file.
     * It will return much larger numbers than you would have been seeing with
     * any of the other suggestions but I think they are much more realistic
     *
     * @param string $dir
     * @return integer
     */
    static function diskSpace($dir)
    {
        if (is_dir($dir)) {
            $s = stat($dir);
        }
        //$space = $s["blocks"] * 512;  // Does not work value $s["blocks"] = -1 allways
        if (!isset($s['size'])) {
            return 0;
        }
        $space = $s["size"];
        if (is_dir($dir) && is_readable($dir)) {
            $dh = opendir($dir);
            while (($file = readdir($dh)) !== false) {
                if ($file != "." and $file != "..") {
                    $space += self::diskSpace($dir . "/" . $file);
                }
            }
            closedir($dh);
        }
        return $space;
    }
    
    /**
     * Returns file extension for this pathname.
     *
     * A the last period ('.') in the pathname is used to delimit the file
     * extension. If the pathname does not have a file extension an empty string is returned.
     *
     * @return string
     */
    static function getFileExtension($file)
    {
        $file = basename($file);
        if (substr($file, -6) == 'tar.gz') {
            return 'tar.gz';
        }
        $pos = strrpos($file, '.');
        if ($pos) {
            return strtolower(substr($file, $pos + 1));
        }
        return '';
    }
    
    /**
     * This function returns the maxumim download size allowed in bytes
     * To Change this modify the php.ini file or use:
     * <code>
     *   ini_set('post_max_size');
     *   ini_set('upload_max_filesize')
     * </code>
     *
     * @return integer
     */
    static function getMaxUploadSize()
    {
        $maxPost = self::string2Bytes(ini_get('post_max_size'));
        $maxUpload = self::string2Bytes(ini_get('upload_max_filesize'));
        if ($maxPost < $maxUpload) {
            return $maxPost;
        }
        return $maxUpload;
    }
    
    
    
    /**
     * Checks whether the file or directory denoted by this pathname exists.
     *
     * @return boolean
     */
    function exists()
    {
        return file_exists($this->pathname);
    }
    
    /**
     * Returns file extension for this pathname.
     *
     * @return string
     */
    function getExtension()
    {
        return self::getFileExtension($this->pathname);
    }
    
    /**
     * Returns the pathname.
     *
     * @return string
     */
    function getPath()
    {
        return $this->pathname;
    }
    
    /**
     * Returns the size of the file in bytes.
     *
     * If pathname does not exist or is not a file, 0 is returned.
     *
     * @return integer
     */
    function getSize()
    {
        if ($this->isFile()) {
            return filesize($this->pathname);
        }
        return 0;
    }
    
    /**
     * Checks whether this pathname is a directory.
     *
     * @return boolean
     */
    function isDir()
    {
        return is_dir($this->pathname);
    }
    
    /**
     * Checks whether this pathname is a regular file.
     *
     * @return boolean
     */
    function isFile()
    {
        return is_file($this->pathname);
    }
    
    /**
     * Checks whether this pathname is writable.
     *
     * @return boolean
     */
    function isWritable()
    {
        return is_writable($this->pathname);
    }
    
    /**
     * Checks whether this pathname is readable.
     *
     * @return boolean
     */
    function isReadable()
    {
        return is_readable($this->pathname);
    }
    
    /**
     * return the dirname of the path
     *
     * @return Tk_Type_Path
     */
    function getDirname()
    {
        return new Tk_Type_Path(dirname($this->pathname));
    }
    
    /**
     * Return the base name of the path
     *
     * @return Tk_Type_Path
     */
    function getBasename()
    {
        return basename($this->pathname);
    }
    
    /**
     * Prepend a path to the main path
     *
     * @param string $pathname
     * @return Tk_Type_Path
     */
    function prepend($pathname)
    {
        if (substr($pathname, -1) == '/') {
            $pathname = substr($pathname, 0, -1);
        }
        if (substr($pathname, 0, 1) != '/' && !preg_match('/^[A-Za-z]:/', $pathname)) {
            $pathname = '/' . $pathname;
        }
        return new Tk_Type_Path($pathname . $this->toString());
    }
    
    /**
     * Append a path to the main path
     *
     * @param string $pathname
     * @return Tk_Type_Path
     */
    function append($pathname)
    {
        if (substr($pathname, -1) == '/') {
            $pathname = substr($pathname, 0, -1);
        }
        if (substr($pathname, 0, 1) != '/' && !preg_match('/^[A-Za-z]:/', $pathname)) {
            $pathname = '/' . $pathname;
        }
        return new Tk_Type_Path($this->toString() . $pathname);
    }
    
    /**
     * Get the pathname without the $pathPrefix prepended.
     * If $pathPrefix is null then the entire path is returned.
     * This is the path relative to the site root.
     *
     * @todo: update and test with reg
     * @return string
     */
    function getRalativeString()
    {
        if (strstr($this->pathname, self::$pathPrefix) !== false) {
            return str_replace(self::$pathPrefix, '', $this->pathname);
        }
        return $this->pathname;
    }
    
    /**
     * Recursivly delete all files and directories from the given path
     *
     * @param string $pathStr
     * @return boolean
     */
    static function rmdir($pathStr)
    {
        if ($pathStr instanceof self) {
            $pathStr = $pathStr->pathname;
        }
        if (is_file($pathStr)) {
            if (is_writable($pathStr)) {
                if (@unlink($pathStr)) {
                    return true;
                }
            }
            return false;
        }
        if (is_dir($pathStr)) {
            if (is_writeable($pathStr)) {
                foreach (new DirectoryIterator($pathStr) as $_res) {
                    if ($_res->isDot()) {
                        unset($_res);
                        continue;
                    }
                    if ($_res->isFile()) {
                        self::rmdir($_res->getPathName());
                    } elseif ($_res->isDir()) {
                        self::rmdir($_res->getRealPath());
                    }
                    unset($_res);
                }
                if (@rmdir($pathStr)) {
                    return true;
                }
            }
            return false;
        }
    }
    
    /**
     * Get a string representation of this object
     *
     * @return string
     */
    function toString()
    {
        return $this->pathname;
    }

}