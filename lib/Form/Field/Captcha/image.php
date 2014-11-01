<?php
include dirname(dirname(dirname(dirname(__FILE__)))) . '/Tk/Tk.php';

try {
    $sitePath = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
    $htroot = dirname(dirname(dirname(dirname(dirname($_SERVER['PHP_SELF'])))));
    if (strlen($htroot) == 1) {
        $htroot = '';  
    }

    Tk::init($sitePath, $sitePath . '/lib', 'Tk/_prepend.php', $htroot);
    
	$width  = 100;
	if (Tk_Request::exists('w')) {
	    $width = (int)Tk_Request::get('w');
	}
	$height =  40;
	if (Tk_Request::exists('h')) {
	    $height = (int)Tk_Request::get('h');
	}
	$length =   4;
	if (Tk_Request::exists('l')) {
	    $length = (int)Tk_Request::get('l');
	}
	if ($length < 4) {
	    $length = 4;
	}
	
    //$baseList = '23456789abcdfghjkmnpqrstvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
	$baseList = '23456789abcdfghjkmnpqrstvwxyz';
	
    $sid = Form_Field_Captcha::SID . '_' . Tk_Request::get('id');
	$code = "";
    $counter = 0;
	
	if (Tk_Request::exists('r')) {
		Tk_Session::delete($sid);
	}
	if (Tk_Session::exists($sid)) {
		$code = Tk_Session::get($sid);
	}
    
    if (!$code) {
        for($i=0; $i<$length; $i++) {
            $actChar = substr($baseList, rand(0, strlen($baseList)-1), 1);
            $code .= strtolower($actChar);
        }
    }
    Tk_Session::set($sid, $code);
    
    generateImage($code, $width, $height);
    
    exit();
}  catch (Exception $e) {
    vd("image.php: \n" . $e->__toString());
    exit();
}


function generateImage($text, $width=200, $height=50) {
    // constant values
    $backgroundSizeX = 2000;
    $backgroundSizeY = 350;
    $sizeX = $width;
    $sizeY = $height;
    $fontFile = dirname(__FILE__) . "/verdana.ttf";
    $textLength = strlen($text);

    // generate random security values
    $backgroundOffsetX = rand(0, $backgroundSizeX - $sizeX - 1);
    $backgroundOffsetY = rand(0, $backgroundSizeY - $sizeY - 1);
    $angle = rand(-5, 5);
    $fontColorR = rand(0, 127);
    $fontColorG = rand(0, 127);
    $fontColorB = rand(0, 127);

    $fontSize = rand(16, 22);
    $textX = rand(0, (int)($sizeX - 0.9 * $textLength * $fontSize)); // these coefficients are empiric
    $textY = rand((int)(1.25 * $fontSize), (int)($sizeY - 0.2 * $fontSize)); // don't try to learn how they were taken out

    $gdInfoArray = gd_info();
    if (! $gdInfoArray['PNG Support']) {
        return false;
    }

    // create image with background
    $src_im = imagecreatefrompng(dirname(__FILE__) . "/background.png");
    if (function_exists('imagecreatetruecolor')) {
        // this is more qualitative function, but it doesn't exist in old GD
        $dst_im = imagecreatetruecolor($sizeX, $sizeY);
        $resizeResult = imagecopyresampled($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);
    } else {
        // this is for old GD versions
        $dst_im = imagecreate( $sizeX, $sizeY );
        $resizeResult = imagecopyresized($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);
    }

    if (! $resizeResult) {
        return false;
    }

    // write text on image
    if (! function_exists('imagettftext')) {
        return false;
    }
    $color = imagecolorallocate($dst_im, $fontColorR, $fontColorG, $fontColorB);
    imagettftext($dst_im, $fontSize, -$angle, $textX, $textY, $color, $fontFile, $text);

    // output header
    header("Content-Type: image/png");

    // output image
    imagepng($dst_im);

    // free memory
    imagedestroy($src_im);
    imagedestroy($dst_im);

    return true;
}