<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This object contains helper functions to manipulate images
 * using the GD lib.
 *
 * @package Tk
 * @deprecated
 * @todo Create new Image lib abstracted to use multiple php image modules
 */
class Tk_Util_GdImage extends Tk_Object
{
    
    static $currentMem = '16M';
    
    
    /**
     * Use this to set the memory allocation for image resizing
     * Dont forget to call memory usage after use.
     *
     * @param string $filename
     * @return boolean Return true if memory resize successful
     */
    static function memAlloc( $filename )
    {
        self::$currentMem = ini_get('memory_limit');
        $imageInfo = getimagesize($filename);
        if (!isset($imageInfo['bits']) || !isset($imageInfo['channels'])) {
            ini_set( 'memory_limit', '128M' );
            return true;
        }
        $MB = 1048576;  // number of bytes in 1M
        $K64 = 65536;    // number of bytes in 64K
        $TWEAKFACTOR = 3.5;  // Or whatever works for you
        $memoryNeeded = round( ( $imageInfo[0] * $imageInfo[1]
                                               * $imageInfo['bits']
                                               * $imageInfo['channels'] / 8
                                 + $K64
                               ) * $TWEAKFACTOR
                             );
        
        $memoryLimit = intval(ini_get('memory_limit')) * $MB;
        if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > $memoryLimit)
        {
            $newLimit = $memoryLimit + ceil((memory_get_usage() + $memoryNeeded - $memoryLimit) / $MB);
            ini_set( 'memory_limit', $newLimit . 'M' );
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Reset the memory allocation back to the default value
     *
     */
    static function memReset()
    {
        ini_set('memory_limit', self::$currentMem);
    }
    
    
    
    
    /**
     * Return a GD image from a source file
     *
     * @param Tk_Type_Path $file
     * @return resource A GD image resource
     * @throws Tk_ExceptionRuntime On unsuported file type
     * @throws Tk_ExceptionIllegalArgument
     */
    static function createImagefromFile(Tk_Type_Path $file)
    {
        if (!$file->exists()) {
            throw new Tk_ExceptionRuntime("Source image does not exsit: `{$file->getPath()}`");
        }
        
        self::memAlloc($file);
        $image = null;
        switch (strtolower($file->getExtension())) {
            case 'jpeg' :
            case 'jpg' :
                $image = imagecreatefromjpeg($file->getPath());
                break;
            case 'gif' :
                $image = imagecreatefromgif($file->getPath());
                break;
            case 'png' :
                $image = imagecreatefrompng($file->getPath());
                break;
            default :
                throw new Tk_ExceptionIllegalArgument("File type unsupported: `{$file->getExtension()}`");
        }
        return $image;
    }
    
    /**
     * Save an image to a destination
     *
     * @param resource $image An GD image resource
     * @param Tk_Type_Path $dest The destination file location
     * @return boolean Returns false on failure
     */
    static function saveImage($image, Tk_Type_Path $dest)
    {
        self::memReset();
        switch ($dest->getExtension()) {
            case 'jpeg' :
            case 'jpg' :
                if (!imagejpeg($image, $dest->getPath())) {
                    return false;
                }
                break;
            case 'gif' :
                if (!imagegif($image, $dest->getPath())) {
                    return false;
                }
                break;
            case 'png' :
                if (!imagepng($image, $dest->getPath())) {
                    return false;
                }
                break;
        }
        return true;
    }
    
    /**
     * Save an image to a destination
     *
     * @param resource $image An GD image resource
     * @return boolean Returns false on failure
     */
    static function streamImage($image, $type)
    {
        self::memReset();
        switch (str_replace('.', '', $type)) {
            case 'jpeg' :
            case 'jpg' :
                header('Content-Type: image/jpeg');
                imagejpeg($image);
                break;
            case 'gif' :
                header('Content-Type: image/gif');
                imagegif($image);
                break;
            case 'png' :
                header('Content-Type: image/png');
                imagepng($image);
                break;
        }
        imagedestroy($image);
        exit;
    }
    
    /**
     * Place a colored border on an image
     *
     * @param Tk_Type_Path $file
     * @param integer $pixels Default 3
     * @param Tk_Type_Color $color If null Default will be White ('FFFFFF')
     * @throws Tk_ExceptionIllegalArgument On unsuported file type
     */
    static function addBorder(Tk_Type_Path $file, $pixels = 3, Tk_Type_Color $color = null)
    {
        if ($color == null) {
            $color = new Tk_Type_Color('FFFFFF');
        }
        
        $image = self::createImagefromFile($file);
        
        $width = imagesx($image) - 1;
        $height = imagesy($image) - 1;
        $lineColor = imagecolorallocate($image, $color->getRed(), $color->getGreen(), $color->getBlue());
        for($i = 0; $i < $pixels; $i++) {
            imageline($image, 0, 0 + $i, $width, 0 + $i, $lineColor); // Top
            imageline($image, 0, $height - $i, $width, $height - $i, $lineColor); // Bottom
            imageline($image, 0 + $i, 0, 0 + $i, $height, $lineColor); // left
            imageline($image, $width - $i, 0, $width - $i, $height, $lineColor); // right
        }
        
        self::saveImage($image, $file);
        imagedestroy($image);
        return true;
    
    }
    
    
    static function propScaleSimple(Tk_Type_Path $src, Tk_Type_Path $dest, $width, $height)
    {
        if (!$src->isFile()) {
            return false;
        }
        $width = (int)$width;
        $height = (int)$height;
        
        if ($dest->isDir()) {
            throw new Tk_ExceptionRuntime('GdImage: Destination file is a directory.');
        }
        if (!$dest->getDirname()->isWritable()) {
            $dir = $dest->getDirname();
            throw new Tk_ExceptionRuntime("GdImage: Destination directory not writable: `$dir`");
        }
        if ($width < 1 || $height < 1) {
            throw new Tk_ExceptionRuntime('GdImage: Dimensions have invalid values');
        }
        
        
        $srcImg = self::createImagefromFile($src);
        
        // Resample
        $srcw = imagesx($srcImg);
        $srch = imagesy($srcImg);
        if ($srcw < $srch) {
            //$height = $maxd;
            $width = floor($srcw * $height / $srch);
        } else {
            //$width = $maxd;
            $height = floor($srch * $width / $srcw);
        }
        if ($width > $srcw && $height > $srch) {
            $width = $srcw;
            $height = $srch;
        }  //if image is actually smaller than you want, leave small (remove this line to resize anyway)
        
        
        $thumb = imagecreatetruecolor($width, $height);
        if ($height < 100) {
            imagecopyresized($thumb, $srcImg, 0, 0, 0, 0, $width, $height, imagesx($srcImg), imagesy($srcImg));
        } else {
            imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $width, $height, imagesx($srcImg), imagesy($srcImg));
        }
        
        self::saveImage($thumb, $dest);
        
        imagedestroy($thumb);
        imagedestroy($srcImg);
        return true;
    }
    
    /**
     * copy and Resize an image to the destination keeping image proportions
     *
     * @param Tk_Type_Path $src The source image path
     * @param Tk_Type_Path $dest The destination image path
     * @param integer $width The new image width
     * @param integer $height The new image height
     * @param Tk_Type_Color $bgColor Hex color. eg: '000000', 'FFFFFF'
     * @param boolean $padImg Pad the image out to size using the bgHexColor
     * @throws Tk_ExceptionRuntime
     */
    static function propScale(Tk_Type_Path $src, Tk_Type_Path $dest, $width, $height, Tk_Type_Color $bgHexColor = null, $padImg = false)
    {
        if (!$src->isFile()) {
            return false;
        }
        $width = intval($width);
        $height = intval($height);
        if ($bgHexColor == null) {
            $bgHexColor = new Tk_Type_Color('FFFFFF');
        }
        
        if ($dest->isDir()) {
            throw new Tk_ExceptionRuntime('GdImage: Destination file is a directory.');
        }
        if (!$dest->getDirname()->isWritable()) {
            $dir = $dest->getDirname();
            throw new Tk_ExceptionRuntime("GdImage: Destination directory not writable: `$dir`");
        }
        if ($width < 1 || $height < 1) {
            throw new Tk_ExceptionRuntime('GdImage: Dimensions have invalid values');
        }
        
        $srcImg = self::createImagefromFile($src);
        // resize
        $px = $py = 0;
        $sWidth = imagesx($srcImg);
        $sHeight = imagesy($srcImg);
        
        $xscale = $sWidth / $width;
        $yscale = $sHeight / $height;
        
        $destWidth = 0;
        $destHeight = 0;
        
        
        if ($padImg && ($sWidth < $width && $sHeight < $height)) {
            $destWidth = $sWidth;
            $destHeight = $sHeight;
            $px = ($width - $destWidth) / 2;
            $py = ($height - $destHeight) / 2;
        } else if ($yscale > $xscale) {
            $destWidth = round($sWidth * (1 / $yscale));
            $destHeight = round($sHeight * (1 / $yscale));
            $px = ($width - $destWidth) / 2;
        } else {
            $destWidth = round($sWidth * (1 / $xscale));
            $destHeight = round($sHeight * (1 / $xscale));
            $py = ($height - $destHeight) / 2;
        }
        
        if ($padImg) {
            $thumb = imagecreatetruecolor($width, $height);
            $bgColor = imagecolorallocate($thumb, $bgHexColor->getRed(), $bgHexColor->getGreen(), $bgHexColor->getBlue());
            //$bgColor = imagecolorallocatealpha($thumb, $bgHexColor->getRed(), $bgHexColor->getGreen(), $bgHexColor->getBlue(), 0);
            imagefill($thumb, 0, 0, $bgColor);
        } else {
            $thumb = imagecreatetruecolor($destWidth, $destHeight);
            $px = $py = 0;
        }
        
        // Resize
        if (!imagecopyresampled($thumb, $srcImg, $px, $py, 0, 0, $destWidth, $destHeight, $sWidth, $sHeight)) {
            return false;
        }
        
        self::saveImage($thumb, $dest);
        
        imagedestroy($thumb);
        imagedestroy($srcImg);
        return true;
    }
    
    /**
     * This function will only resize a file if it exists and exceeds
     *   the width and height specified. Otherwise the file will remain un-modified
     *
     * If $padImg is true this method will pad out small images and center the
     * image at its original size
     *
     * @param Tk_Type_Path $src The source image path
     * @param Tk_Type_Path $dest The destination image path
     * @param integer $width The new image width
     * @param integer $height The new image height
     * @param Tk_Type_Color $bgHexColor
     * @param boolean $padImg Pad the image out to size using the bgHexColor
     * @throws Tk_ExceptionRuntime
     */
    static function conditionalPropScale(Tk_Type_Path $src, Tk_Type_Path $dest, $width, $height, Tk_Type_Color $bgHexColor = null, $padImg = false)
    {
        if (!$src->isFile()) {
            return false;
        }
        $imageInfo = getimagesize($src->toString());
        if (!$imageInfo) {
            return false;
        }
        if ($imageInfo[0] >= $width || $imageInfo[1] >= $height) {
            return self::propScale($src, $dest, $width, $height, $bgHexColor, $padImg);
        }
        return false;
    }
    
    /**
     * copy and Resize an image to the destination
     *
     * @param Tk_Type_Path $src The source image path
     * @param Tk_Type_Path $dest The destination image path
     * @param integer $width The new image width
     * @param integer $height The new image height
     * @throws Tk_ExceptionRuntime
     */
    static function scale($src, $dest, $width, $height)
    {
        $width = intval($width);
        $height = intval($height);
        if (!$src->isFile()) {
            return false;
        }
        if ($dest->isDir()) {
            throw new Tk_ExceptionRuntime('Destination file is a directory.');
        }
        if (!$dest->getDirname()->isWritable()) {
            $dir = $dest->getDirname();
            throw new Tk_ExceptionRuntime("Destination directory not writable: `$dir`");
        }
        if ($width < 1 || $height < 1) {
            throw new Tk_ExceptionRuntime('Dimensions have invalid values');
        }
        
        $srcImg = self::createImagefromFile($src);
        
        // resize
        $sWidth = imagesx($srcImg);
        $sHeight = imagesy($srcImg);
        if ($sWidth == $width && $sHeight == $height) {
            copy($src->toString(), $dest->toString());
            return true;
        }
        
        $thumb = imagecreatetruecolor($width, $height);
        
        // Resize
        if (!imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $width, $height, $sWidth, $sHeight)) {
            return false;
        }
        
        self::saveImage($thumb, $dest);
        
        imagedestroy($thumb);
        imagedestroy($srcImg);
        return true;
    }
    
    /**
     * Is the supplied image extension valid for the GdImage Object
     *
     * @param string $file
     * @return boolean
     */
    static function isValidImage($file)
    {
        switch (Tk_Type_Path::getFileExtension($file)) {
            case 'jpeg' :
            case 'jpg' :
            case 'gif' :
            case 'png' :
                return true;
        }
        return false;
    }

}